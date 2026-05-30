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
  const $boardStatus = $("#app-board-status");
  const $statusDot = $("#app-status-dot");
  const $statusText = $("#app-status-text");
  const $debugToggle = $("#app-debug-toggle");
  const $modelRole = $("#app-model-role");
  const $modelSelect = $("#app-model-select");
  const $engineProfile = $("#app-engine-profile");
  const $engineMove = $("#app-engine-move");
  const $navItems = $(".app-nav-item[data-mode]");
  const $flexPanel = $("#app-flex");
  const $flexContent = $("#app-flex-content");
  const analyseDialog = document.getElementById("app-analyse-dialog");

  let currentSessionId = null;
  let currentBoardId = null;
  let busyCount = 0;
  let currentMode = "chat";
  let selectedSquare = null;
  let availableModels = [];
  let availableEngineProfiles = [];
  let activeAnalysisJobId = null;
  let activeAnalysisTimer = null;
  let $activeAnalysisMessage = null;
  let analysisBusy = false;

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
        const message =
          data?.error?.message ||
          data?.detail?.message ||
          data?.detail?.error ||
          `HTTP ${response.status}`;
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

  function setBoardStatus(message) {
    $boardStatus.text(message || "Figur und Zielfeld anklicken.");
  }

  function selectedModelPayload() {
    const model = String($modelSelect.val() || "").trim();
    const role = String($modelRole.val() || "").trim();
    if (!model || !role) return {};
    return { model_role: role, model };
  }

  function selectedEnginePayload() {
    const profile = String($engineProfile.val() || "stockfish18").trim();
    return { engine_profile: profile || "stockfish18" };
  }

  function applyMessageContent($div, text, kind, meta) {
    $div.text(text || "");
    if (meta && debugMode && String(kind || "").indexOf("status") !== -1) {
      $div.append($("<small>").css({ display: "block", opacity: 0.7, marginTop: "0.35rem" }).text(JSON.stringify(meta)));
    }
  }

  function appendMessage(text, kind, meta) {
    $messages.find(".app-empty-state").remove();
    const $div = $("<div>").addClass(`app-message ${kind || "assistant"}`);
    applyMessageContent($div, text, kind, meta);
    $messages.append($div);
    $messages.scrollTop($messages[0].scrollHeight);
    return $div;
  }

  function replaceMessage($message, text, kind, meta) {
    if (!$message || !$message.length) {
      return appendMessage(text, kind, meta);
    }
    $message.attr("class", `app-message ${kind || "assistant"}`);
    applyMessageContent($message, text, kind, meta);
    $messages.scrollTop($messages[0].scrollHeight);
    return $message;
  }

  function latestAnalysisMessage(record) {
    const events = record?.events || record?.result?.events || [];
    if (events.length) {
      const event = events[events.length - 1] || {};
      return event.message || event.stage || "Analyse laeuft …";
    }
    if (record?.status === "queued") return "Analysejob wartet …";
    if (record?.status === "done") return "Analyse abgeschlossen.";
    if (record?.status === "failed") return record.error || "Analyse fehlgeschlagen.";
    return "Analyse laeuft …";
  }

  function renderAnalysisProgress(record) {
    const progress = Math.max(0, Math.min(100, Number(record?.progress || 0)));
    const running = ["queued", "running"].includes(record?.status);
    const summary = record?.result?.summary || "";
    const moments = record?.result?.critical_moments || [];
    const events = (record?.events || record?.result?.events || []).slice(-5);
    const $card = $("<div>").addClass("app-analysis-card");
    $card.append(
      $("<div>").addClass("app-analysis-head").append(
        $("<strong>").text(record?.status === "done" ? "PGN-Analyse" : "PGN-Analyse laeuft"),
        $("<span>").text(`${progress}%`)
      ),
      $("<div>").addClass("app-analysis-shimmer").text(latestAnalysisMessage(record)),
      $("<div>").addClass("app-analysis-bar").append(
        $("<span>").css("width", `${progress}%`)
      )
    );
    if (events.length) {
      const $log = $("<ol>").addClass("app-analysis-log");
      events.forEach((event) => {
        $log.append($("<li>").text(event.message || event.stage || ""));
      });
      $card.append($log);
    }
    if (summary) {
      $card.append($("<p>").addClass("app-analysis-summary").text(summary));
    }
    if (moments.length) {
      const text = moments.slice(0, 3).map((item) => `${item.move_number}.${item.san}: ${item.reason}`).join(" · ");
      $card.append($("<p>").addClass("app-analysis-summary").text(text));
    }
    if (record?.artifact_ids?.length) {
      const $links = $("<div>").addClass("app-analysis-links");
      record.artifact_ids.slice(0, 4).forEach((id, index) => {
        $links.append(
          $("<a>")
            .attr("href", apiUrl(`/artifacts/${id}`))
            .attr("target", "_blank")
            .attr("rel", "noopener")
            .text(["source.pgn", "summary.md", "annotated.pgn", "analysis.json"][index] || "Artefakt")
        );
      });
      $card.append($links);
    }
    if (!$activeAnalysisMessage || !$activeAnalysisMessage.length) {
      $activeAnalysisMessage = appendMessage("", "activity");
    }
    $activeAnalysisMessage
      .attr("class", `app-message activity${running ? " is-pending" : ""}`)
      .empty()
      .append($card);
    $messages.scrollTop($messages[0].scrollHeight);
  }

  function renderAnalysisPanel(record) {
    if (currentMode !== "analysis") return;
    const progress = Math.max(0, Math.min(100, Number(record?.progress || 0)));
    const events = (record?.events || record?.result?.events || []).slice(-10);
    $flexContent.empty().append(
      $("<h3>").text("Analyse"),
      $("<div>").addClass("app-analysis-side-status").text(latestAnalysisMessage(record)),
      $("<div>").addClass("app-analysis-bar").append($("<span>").css("width", `${progress}%`))
    );
    if (record?.result?.final_fen) {
      $flexContent.append($("<p>").addClass("app-meta").text(record.result.final_fen));
    }
    const $log = $("<ol>").addClass("app-analysis-log");
    events.forEach((event) => $log.append($("<li>").text(event.message || event.stage || "")));
    $flexContent.append($log);
  }

  function finishAnalysisBusy() {
    if (analysisBusy) {
      analysisBusy = false;
      setBusy(false);
    }
  }

  function pollAnalysisJob(jobId) {
    if (activeAnalysisTimer) {
      window.clearTimeout(activeAnalysisTimer);
      activeAnalysisTimer = null;
    }
    return api("GET", `/analysis/jobs/${jobId}`)
      .then((record) => {
        renderAnalysisProgress(record);
        renderAnalysisPanel(record);
        if (["queued", "running"].includes(record.status)) {
          activeAnalysisTimer = window.setTimeout(() => pollAnalysisJob(jobId), 1400);
        } else {
          finishAnalysisBusy();
          if (record.status === "failed") {
            showError(record.error || "Analyse fehlgeschlagen.");
          }
        }
      })
      .catch((error) => {
        finishAnalysisBusy();
        showError(error.message || String(error));
      });
  }

  function statusDisplayText(event, text) {
    const role = event.role || event.model_role || "";
    const model = event.model || "";
    const routing = event.routing_source || "";
    if (text.indexOf("Modell:") === 0 && (role || model)) {
      const parts = [`Modell: ${role || "auto"} / ${model || "auto"}`];
      if (routing) parts.push(routing);
      return parts.join(" · ");
    }
    return text || "";
  }

  function pendingLabelFor(text) {
    if ((text || "").trim().startsWith("/")) {
      return "Befehl wird ausgeführt …";
    }
    return "Router prüft Eingabe …";
  }

  function appendActivityMessage(label, meta) {
    return appendMessage(label || "Verarbeite …", "status activity is-pending", meta)
      .attr("role", "status")
      .attr("aria-live", "polite");
  }

  function renderEvents(events, options) {
    const opts = options || {};
    let pending = opts.pendingMessage || null;
    let pendingFinalized = false;
    let skippedEcho = false;

    function renderFinal(text, kind, event) {
      if (pending && pending.length && !pendingFinalized) {
        replaceMessage(pending, text, kind, event);
        pendingFinalized = true;
        return;
      }
      appendMessage(text, kind, event);
    }

    (events || []).forEach((event) => {
      const type = event.type || "";
      const text = event.text || "";
      switch (type) {
        case "user_message":
          if (
            opts.skipUserText &&
            !skippedEcho &&
            text.trim() === String(opts.skipUserText).trim()
          ) {
            skippedEcho = true;
            break;
          }
          appendMessage(text, "user");
          break;
        case "assistant_message":
        case "assistant_delta":
          renderFinal(text, "assistant", event);
          break;
        case "slash_result":
          renderFinal(text, "slash", event);
          break;
        case "status":
          if (pending && pending.length && !pendingFinalized) {
            replaceMessage(pending, statusDisplayText(event, text), "status activity is-pending", event);
          } else {
            appendMessage(statusDisplayText(event, text), "status", event);
          }
          if (text.indexOf("Modell:") === 0) {
            $statusText.text(text.replace(/^Modell:\s*/, "").slice(0, 80));
          }
          break;
        case "tool_start":
          $statusText.text(`Tool: ${event.tool || "…"}`);
          if (pending && pending.length && !pendingFinalized) {
            replaceMessage(pending, `Tool: ${event.tool || "…"}`, "status activity is-pending", event);
          } else {
            appendMessage(`Tool: ${event.tool || "?"}`, "tool");
          }
          break;
        case "tool_result":
          if (debugMode) {
            appendMessage(text, "tool");
          }
          break;
        case "error":
          renderFinal(text || "Fehler", "error", event);
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
    if (pending && pending.length && !pendingFinalized) {
      pending.removeClass("is-pending");
    }
  }

  function capabilityLabel(model) {
    const caps = model.capabilities || {};
    const tags = caps.route_capabilities || [];
    if (Array.isArray(tags) && tags.length > 0) {
      return tags.slice(0, 3).join("/");
    }
    const roles = caps.roles || [];
    if (Array.isArray(roles) && roles.length > 0) {
      return roles.slice(0, 2).join("/");
    }
    return "";
  }

  function renderModelOptions(models) {
    availableModels = models || [];
    const previous = String($modelSelect.val() || "");
    $modelSelect.empty().append($("<option>").attr("value", "").text("Auto"));
    availableModels.forEach((model) => {
      const name = String(model.name || "");
      if (!name) return;
      const label = capabilityLabel(model);
      $modelSelect.append(
        $("<option>")
          .attr("value", name)
          .text(label ? `${name} (${label})` : name)
      );
    });
    if (previous && availableModels.some((model) => model.name === previous)) {
      $modelSelect.val(previous);
    }
    $modelSelect.prop("disabled", availableModels.length === 0);
  }

  function refreshModels() {
    return api("GET", "/models")
      .then((data) => {
        renderModelOptions(data.models || []);
      })
      .catch(() => {
        renderModelOptions([]);
      });
  }

  function renderEngineProfiles(profiles, defaultProfile) {
    availableEngineProfiles = profiles || [];
    const previous = String($engineProfile.val() || "");
    $engineProfile.empty();
    availableEngineProfiles.forEach((profile) => {
      const id = String(profile.id || "");
      if (!id) return;
      const label = profile.available
        ? profile.name || id
        : `${profile.name || id} (fehlt)`;
      $engineProfile.append(
        $("<option>")
          .attr("value", id)
          .prop("disabled", !profile.available)
          .text(label)
      );
    });
    const fallback = defaultProfile || "stockfish18";
    if (previous && availableEngineProfiles.some((profile) => profile.id === previous && profile.available)) {
      $engineProfile.val(previous);
    } else {
      $engineProfile.val(fallback);
    }
    $engineProfile.prop("disabled", availableEngineProfiles.every((profile) => !profile.available));
    $engineMove.prop("disabled", $engineProfile.prop("disabled"));
  }

  function refreshEngineProfiles() {
    return api("GET", "/engine-profiles")
      .then((data) => {
        renderEngineProfiles(data.profiles || [], data.default_profile || "stockfish18");
      })
      .catch(() => {
        renderEngineProfiles([{ id: "stockfish18", name: "Stockfish 18", available: false }], "stockfish18");
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
      renderBoardState(board);
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
          bindBoardInteractions();
        });
      }
    );
  }

  function refreshBoardState() {
    if (!currentBoardId) return Promise.resolve();
    return api("GET", `/boards/${currentBoardId}`).then((board) => {
      renderBoardState(board);
      return board;
    });
  }

  function renderBoardState(board) {
    if (!board) return;
    if (board.fen) {
      $boardFen.text(board.fen);
    }
    const $boardMoves = $("#app-board-moves");
    $boardMoves.empty();
    (board.move_sans || []).forEach((san) => {
      $boardMoves.append($("<li>").text(san));
    });
  }

  function squareFromElement(element) {
    const classes = String($(element).attr("class") || "").split(/\s+/);
    return classes.find((className) => /^[a-h][1-8]$/.test(className)) || "";
  }

  function clearSelectedSquare() {
    selectedSquare = null;
    $boardSvg.find(".square").removeClass("is-selected");
  }

  function selectSquare(square) {
    selectedSquare = square;
    $boardSvg.find(".square").removeClass("is-selected");
    $boardSvg.find(`.square.${square}`).addClass("is-selected");
    setBoardStatus(`${square} gewaehlt. Zielfeld anklicken.`);
  }

  function moveBoardPiece(from, to) {
    if (!currentBoardId || !from || !to) return Promise.resolve();
    setBusy(true, "Brettzug …");
    const payload = { from, to };
    return api("POST", `/boards/${currentBoardId}/move`, payload)
      .then((result) => {
        if (!result.legal) {
          setBoardStatus(result.error || "Ungueltiger Zug.");
          showError(result.error || "Ungueltiger Zug.");
          return null;
        }
        setBoardStatus(result.san ? `Zug: ${result.san}` : "Zug ausgefuehrt.");
        return refreshBoardState().then(() => refreshBoardSvg());
      })
      .finally(() => {
        clearSelectedSquare();
        setBusy(false);
      });
  }

  function handleSquareInput(square) {
    showError("");
    if (!square) return;
    if (!selectedSquare) {
      selectSquare(square);
      return;
    }
    if (selectedSquare === square) {
      clearSelectedSquare();
      setBoardStatus("");
      return;
    }
    moveBoardPiece(selectedSquare, square).catch((error) => {
      clearSelectedSquare();
      setBoardStatus("Zug fehlgeschlagen.");
      showError(error.message || String(error));
    });
  }

  function bindBoardInteractions() {
    $boardSvg.find(".square")
      .attr("tabindex", "0")
      .attr("role", "button")
      .off(".boardInput")
      .on("click.boardInput", function () {
        handleSquareInput(squareFromElement(this));
      })
      .on("keydown.boardInput", function (event) {
        if (event.key === "Enter" || event.key === " ") {
          event.preventDefault();
          handleSquareInput(squareFromElement(this));
        }
      });
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
    let $pending = null;
    return ensureSession
      .then(() => {
        appendMessage(trimmed, "user");
        $pending = appendActivityMessage(pendingLabelFor(trimmed));
        $input.val("");
        setBusy(true, trimmed.startsWith("/") ? "Befehl …" : "Verarbeite …");
        const path = trimmed.startsWith("/") ? "commands" : "messages";
        const payload = trimmed.startsWith("/")
          ? {
              command: trimmed,
              board_state: currentBoardId ? { board_id: currentBoardId } : {},
              ...selectedModelPayload(),
            }
          : { message: trimmed, ...selectedModelPayload() };
        return api("POST", `/sessions/${currentSessionId}/${path}`, payload);
      })
      .then((data) => {
        renderEvents(data.events || [], { pendingMessage: $pending, skipUserText: trimmed });
        return refreshBoardSvg();
      })
      .catch((error) => {
        replaceMessage($pending, error.message || String(error), "error");
        throw error;
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
          $("<h3>").text("Brett"),
          $("<ol>").attr("id", "app-board-moves").addClass("app-board-moves")
        );
        refreshBoardState().catch(() => {});
        break;
      case "analysis":
        if (activeAnalysisJobId) {
          api("GET", `/analysis/jobs/${activeAnalysisJobId}`)
            .then((record) => renderAnalysisPanel(record))
            .catch(() => {});
        } else {
          $flexContent.append(
            $("<h3>").text("Analyse"),
            $("<p>").addClass("app-meta").text("Noch kein PGN-Analysejob.")
          );
        }
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

  $("#app-board-undo").on("click", () => {
    if (!currentBoardId) return;
    api("POST", `/boards/${currentBoardId}/undo`, {})
      .then((result) => {
        setBoardStatus(result.legal ? "Zug zurueckgenommen." : result.error);
        return refreshBoardState().then(() => refreshBoardSvg());
      })
      .catch((error) => showError(error.message || String(error)));
  });

  $("#app-board-reset").on("click", () => {
    if (!currentBoardId) return;
    api("POST", `/boards/${currentBoardId}/reset`, {})
      .then((board) => {
        renderBoardState(board);
        setBoardStatus("Startstellung geladen.");
        return refreshBoardSvg();
      })
      .catch((error) => showError(error.message || String(error)));
  });

  $("#app-board-flip").on("click", () => {
    if (!currentBoardId) return;
    api("POST", `/boards/${currentBoardId}/flip`, {})
      .then((board) => {
        renderBoardState(board);
        setBoardStatus("Brett gedreht.");
        return refreshBoardSvg();
      })
      .catch((error) => showError(error.message || String(error)));
  });

  $engineMove.on("click", () => {
    if (!currentBoardId) return;
    showError("");
    setBusy(true, "Enginezug …");
    api("POST", `/boards/${currentBoardId}/engine-move`, selectedEnginePayload())
      .then((payload) => {
        const move = payload.engine_move || {};
        const label = move.san ? `Enginezug (${move.profile}): ${move.san}` : "Kein Enginezug.";
        setBoardStatus(label);
        appendMessage(label, "status", move);
        if (payload.board) {
          renderBoardState(payload.board);
        }
        return refreshBoardSvg();
      })
      .catch((error) => {
        setBoardStatus("Enginezug fehlgeschlagen.");
        showError(error.message || String(error));
      })
      .finally(() => setBusy(false));
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
        activeAnalysisJobId = job.job_id;
        $activeAnalysisMessage = appendMessage("Analysejob gestartet …", "activity is-pending");
        analysisBusy = true;
        setBusy(true, "PGN-Analyse …");
        if (analyseDialog) analyseDialog.close();
        switchMode("analysis");
        return pollAnalysisJob(job.job_id);
      })
      .catch((error) => {
        finishAnalysisBusy();
        showError(error.message || String(error));
      });
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
  
  refreshModels();
  refreshEngineProfiles();
  refreshSessions().catch(() => {});
})(jQuery);
