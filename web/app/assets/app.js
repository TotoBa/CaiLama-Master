/* global jQuery */
(function ($) {
  "use strict";

  const $root = $("#cailama-app");
  if (!$root.length) return;

  const apiBase = $root.data("apiBase") || "app-api.php?path=";
  const profileName = String($root.data("profile") || "");
  let debugMode =
    $root.data("debug") === 1 ||
    $root.data("debug") === "1" ||
    localStorage.getItem("cailama_app_debug") === "1";

  // New selectors for updated DOM
  const $messages = $("#app-messages");
  const $errors = $("#app-errors");
  const $form = $("#app-input-form");
  const $input = $("#app-input");
  const $boardSvg = $("#app-board-svg");
  const $boardFen = $("#app-board-fen");
  const $statusDot = $("#app-status-dot");
  const $statusText = $("#app-status-text");
  const $debugToggle = $("#app-debug-toggle");
  const $navItems = $(".app-nav-item[data-mode]");
  const $flexPanel = $("#app-flex");
  const $flexContent = $("#app-flex-content");
  const analyseDialog = document.getElementById("app-analyse-dialog");

  let currentSessionId = null;
  let currentBoardId = null;
  let busyCount = 0;
  let currentMode = "chat";

  const slashHints = [
    "/help",
    "/models",
    "/board",
    "/move",
    "/undo",
    "/flip",
    "/pgn",
    "/tools",
    "/task",
    "/context",
    "/history",
  ];

  function isMobileClient() {
    return (
      window.matchMedia("(max-width: 768px)").matches ||
      window.matchMedia("(hover: none) and (pointer: coarse)").matches
    );
  }

  function applyDebugMode() {
    $root.toggleClass("is-debug", debugMode);
    $debugToggle.prop("checked", debugMode);
    localStorage.setItem("cailama_app_debug", debugMode ? "1" : "0");
  }

  function setBusy(active, label) {
    if (active) {
      busyCount += 1;
    } else {
      busyCount = Math.max(0, busyCount - 1);
    }
    const on = busyCount > 0;
    $statusDot.toggleClass("is-busy", on);
    if (label) {
      $statusText.text(label);
    } else if (!on) {
      $statusText.text("Bereit");
    }
  }

  function apiUrl(path) {
    const normalized = path.startsWith("/") ? path : `/${path}`;
    return `${apiBase}${encodeURIComponent(normalized)}`;
  }

  function api(method, path, body) {
    const options = {
      method,
      headers: { Accept: "application/json" },
      credentials: "same-origin",
    };
    if (body !== undefined) {
      options.headers["Content-Type"] = "application/json";
      options.body = JSON.stringify(body);
    }
    return fetch(apiUrl(path), options).then(async (response) => {
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message = data?.error?.message || `HTTP ${response.status}`;
        throw new Error(message);
      }
      return data;
    });
  }

  function showError(message) {
    $errors.text(message || "");
    if (message) {
      setTimeout(() => $errors.text(""), 5000);
    }
  }

  function appendMessage(text, kind, meta) {
    $messages.find(".app-empty-state").remove();
    const $div = $("<div>").addClass(`app-message ${kind || "assistant"}`);
    if (kind === "status" && meta && !debugMode) {
      $div.addClass("debug-only");
    }
    $div.text(text || "");
    if (meta && debugMode && kind === "status") {
      $div.append($("<small>").css({ display: "block", opacity: 0.7, marginTop: "0.35rem" }).text(JSON.stringify(meta)));
    }
    $messages.append($div);
    $messages.scrollTop($messages[0].scrollHeight);
  }

  function renderEvents(events) {
    (events || []).forEach((event) => {
      const type = event.type || "";
      const text = event.text || "";
      switch (type) {
        case "user_message":
          appendMessage(text, "user");
          break;
        case "assistant_message":
        case "assistant_delta":
          appendMessage(text, "assistant");
          break;
        case "slash_result":
          appendMessage(text, "slash");
          break;
        case "status":
          appendMessage(text, "status", event);
          if (text.indexOf("Modell:") === 0) {
            setBusy(true, text.replace(/^Modell:\s*/, "").slice(0, 80));
          }
          break;
        case "tool_start":
          setBusy(true, `Tool: ${event.tool || "…"}`);
          if (debugMode) {
            appendMessage(`Tool start: ${event.tool || "?"}`, "tool");
          }
          break;
        case "tool_result":
          if (debugMode) {
            appendMessage(text, "tool");
          }
          break;
        case "error":
          appendMessage(text || "Fehler", "error");
          break;
        case "board_update":
          if (event.fen) {
            $boardFen.text(event.fen);
          }
          break;
        default:
          if (text && debugMode) {
            appendMessage(text, "status", event);
          }
      }
    });
  }

  function refreshSessions() {
    return api("GET", "/sessions").then((data) => {
      // Sessions now shown in flex panel
      const $list = $("#app-session-list");
      const $container = $list.length ? $list : $("<ul>").attr("id", "app-session-list").addClass("app-session-list");
      
      $container.empty();
      (data.sessions || []).forEach((session) => {
        const $btn = $("<button>")
          .attr("type", "button")
          .addClass("app-session-btn")
          .toggleClass("is-active", session.id === currentSessionId)
          .text(session.title || session.id)
          .on("click", () => selectSession(session.id));
        $container.append($("<li>").append($btn));
      });
      
      if (!$list.length && $container.children().length > 0) {
        $flexContent.prepend($("<h3>").text("Sessions")).append($container);
      }
    });
  }

  function createBoard() {
    return api("POST", "/boards", {}).then((board) => {
      currentBoardId = board.id;
      $boardFen.text(board.fen || "");
      return refreshBoardSvg().then(() => board);
    });
  }

  function refreshBoardSvg() {
    if (!currentBoardId) return Promise.resolve();
    return fetch(apiUrl(`/boards/${currentBoardId}/svg`), { credentials: "same-origin" }).then(
      (response) => {
        if (!response.ok) return;
        return response.text().then((html) => {
          $boardSvg.html(html);
        });
      }
    );
  }

  function selectSession(sessionId) {
    currentSessionId = sessionId;
    $messages.empty();
    appendMessage(`Session ${sessionId} geöffnet.`, "status");
    return refreshSessions();
  }

  function createSession() {
    const title = profileName ? `Chat (${profileName})` : "Chat";
    return api("POST", "/sessions", { title, type: "chat" }).then((session) => {
      return refreshSessions().then(() => selectSession(session.id)).then(() => {
        if (!currentBoardId) {
          return createBoard();
        }
      });
    });
  }

  function sendText(text) {
    showError("");
    const trimmed = (text || "").trim();
    if (!trimmed) return Promise.resolve();

    const ensureSession = currentSessionId ? Promise.resolve() : createSession();
    return ensureSession
      .then(() => {
        $input.val("");
        setBusy(true, trimmed.startsWith("/") ? "Befehl …" : "Verarbeite …");
        const path = trimmed.startsWith("/") ? "commands" : "messages";
        const payload = trimmed.startsWith("/")
          ? { command: trimmed, board_state: currentBoardId ? { board_id: currentBoardId } : {} }
          : { message: trimmed };
        return api("POST", `/sessions/${currentSessionId}/${path}`, payload);
      })
      .then((data) => {
        renderEvents(data.events || []);
        return refreshBoardSvg();
      })
      .finally(() => setBusy(false));
  }

  // Navigation handlers
  function switchMode(mode) {
    currentMode = mode;
    $navItems.removeClass("is-active");
    $navItems.filter(`[data-mode="${mode}"]`).addClass("is-active");
    
    // Show/hide flex panel based on mode
    if (["board", "analysis", "training"].includes(mode)) {
      $flexPanel.addClass("is-visible");
      if (mode === "board" && !currentBoardId) {
        createBoard();
      }
    } else {
      $flexPanel.removeClass("is-visible");
    }
    
    // Update flex content
    updateFlexContent(mode);
  }

  function updateFlexContent(mode) {
    $flexContent.empty();
    
    switch (mode) {
      case "board":
        $flexContent.append(
          $("<h3>").text("Schachbrett"),
          $("<p>").addClass("app-meta").text("Brett wird rechts angezeigt.")
        );
        break;
      case "analysis":
        $flexContent.append(
          $("<h3>").text("Analyse"),
          $("<p>").addClass("app-meta").text("Engine-Analyse wird geladen…"),
          $("<div>").addClass("app-analysis-placeholder")
        );
        break;
      case "training":
        $flexContent.append(
          $("<h3>").text("Training"),
          $("<p>").addClass("app-meta").text("Trainingsaufgaben werden vorbereitet…")
        );
        break;
    }
  }

  // Event handlers
  $form.on("submit", (event) => {
    event.preventDefault();
    sendText($input.val()).catch((error) => {
      setBusy(false);
      showError(error.message || String(error));
    });
  });

  $input.on("keydown", (event) => {
    if (event.key === "Enter" && !event.shiftKey && !event.isComposing) {
      event.preventDefault();
      $form.trigger("submit");
    }
  });

  $("#app-new-session").on("click", () => {
    createSession().catch((error) => showError(error.message || String(error)));
  });

  $navItems.on("click", function () {
    const mode = $(this).data("mode");
    switchMode(mode);
  });

  $("#app-analyse-open").on("click", () => {
    if (analyseDialog && analyseDialog.showModal) analyseDialog.showModal();
  });

  $("#app-analyse-cancel").on("click", () => {
    if (analyseDialog) analyseDialog.close();
  });

  $("#app-analyse-form").on("submit", (event) => {
    event.preventDefault();
    showError("");
    const pgnText = ($("#app-analyse-pgn").val() || "").trim();
    if (!pgnText) return;
    const start = currentSessionId ? $.when(currentSessionId) : createSession();
    start
      .then(() =>
        api("POST", "/analysis/jobs", {
          profile: $("#app-analyse-profile").val(),
          pgn_text: pgnText,
          session_id: currentSessionId,
        })
      )
      .then((job) => {
        appendMessage(`Analysejob gestartet: ${job.job_id} (${job.status})`, "assistant");
        if (analyseDialog) analyseDialog.close();
      })
      .catch((error) => showError(error.message || String(error)));
  });

  $debugToggle.on("change", function () {
    debugMode = $(this).is(":checked");
    applyDebugMode();
  });

  // Auto-resize textarea
  $input.on("input", function () {
    this.style.height = "auto";
    this.style.height = Math.min(this.scrollHeight, 120) + "px";
  });

  // Initialize
  if (isMobileClient()) {
    $root.addClass("is-mobile").attr("data-mobile", "1");
    if ($.mobile) {
      $.mobile.ajaxEnabled = false;
      $.mobile.linkBindingEnabled = false;
      $.mobile.hashListeningEnabled = false;
    }
  }

  applyDebugMode();

  if ($.fn.autocomplete) {
    $input.autocomplete({
      minLength: 0,
      source(request, response) {
        const term = (request.term || "").trim();
        if (!term.startsWith("/")) {
          response([]);
          return;
        }
        response(
          slashHints.filter((hint) => hint.indexOf(term.toLowerCase()) === 0)
        );
      },
      focus() {
        return false;
      },
      select(event, ui) {
        if (ui.item && ui.item.value) {
          $input.val(`${ui.item.value} `);
        }
        return false;
      },
    });
    $input.on("focus", function () {
      if ($(this).val().startsWith("/")) {
        $(this).autocomplete("search", $(this).val());
      }
    });
  }

  // Initialize with chat mode
  switchMode("chat");
  
  // Initial board creation
  createBoard().catch(() => {});
  
  refreshSessions().catch(() => {});
})(jQuery);
