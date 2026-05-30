{extends file="app/layout.tpl"}

{block name="content"}
<div id="cailama-app"
     class="app-shell"
     data-api-base="{$page.api_base|escape}"
     data-csrf="{$page.csrf_token|escape}"
     data-debug="{if $page.debug}1{else}0{/if}"
     data-profile="{$page.user.training_name|default:''|escape}"
     data-mobile="0"
     role="application"
     aria-label="CaiLama Chat Anwendung">
  <header class="app-header">
    <div class="app-header-inner">
      <a href="index.php" class="app-back-link" title="Zurück zur Startseite">
        <span aria-hidden="true">←</span> Zurück
      </a>
      <div class="app-header-title">
        <span class="app-brand">CaiLama</span>
        {if $page.user.profile_key}
          <span class="app-profile-badge">{$page.user.player_display_name|escape}</span>
        {/if}
        <div class="app-model-picker" aria-label="Modellauswahl">
          <select id="app-model-role" class="app-model-role" title="Modellrolle">
            <option value="large">Large</option>
            <option value="small">Small</option>
            <option value="task">Task</option>
            <option value="coach">Coach</option>
            <option value="analyst">Analyst</option>
            <option value="critic">Critic</option>
            <option value="vision">Vision</option>
            <option value="scribe">Scribe</option>
            <option value="researcher">Researcher</option>
            <option value="router">Router</option>
            <option value="translator">Translator</option>
          </select>
          <select id="app-model-select" class="app-model-select" title="Modell">
            <option value="">Auto</option>
          </select>
        </div>
      </div>
      <div class="app-header-status">
        <span class="app-status-indicator" id="app-status-dot"></span>
        <span id="app-status-text">Bereit</span>
        <label class="app-debug-toggle" title="Debug-Infos einblenden">
          <input type="checkbox" id="app-debug-toggle"{if $page.debug} checked{/if}>
          Debug
        </label>
      </div>
    </div>
  </header>

  <div class="app-layout">
    <aside class="app-nav" id="app-nav">
      <nav class="app-nav-list">
        <button type="button" class="app-nav-item is-active" data-mode="chat">
          <span class="app-nav-icon">💬</span>
          <span>Chat</span>
        </button>
        <button type="button" class="app-nav-item" data-mode="board">
          <span class="app-nav-icon">♟️</span>
          <span>Brett</span>
        </button>
        <button type="button" class="app-nav-item" data-mode="analysis">
          <span class="app-nav-icon">🔍</span>
          <span>Analyse</span>
        </button>
        <button type="button" class="app-nav-item" data-mode="training">
          <span class="app-nav-icon">📚</span>
          <span>Training</span>
        </button>
      </nav>
      <div class="app-nav-footer">
        <button type="button" class="app-nav-item" id="app-new-session">
          <span class="app-nav-icon">+</span>
          <span>Neu</span>
        </button>
      </div>
    </aside>

    <main class="app-chat" id="app-chat">
      <div id="app-messages" class="app-messages" aria-live="polite" aria-atomic="true">
        <div class="app-empty-state">
          <h1>CaiLama Chat</h1>
          <p>Bereit fuer Chat, Slash-Commands, Brettarbeit und Analyse.</p>
        </div>
      </div>
    </main>

    <aside class="app-flex" id="app-flex">
      <div id="app-board-container" class="app-board-container">
        <div id="app-board-svg" class="app-board"></div>
        <p id="app-board-fen" class="app-board-fen"></p>
        <div class="app-board-controls" aria-label="Brettaktionen">
          <button type="button" class="app-board-btn" id="app-board-undo" title="Zug zuruecknehmen">↶</button>
          <button type="button" class="app-board-btn" id="app-board-reset" title="Startstellung">⌂</button>
          <button type="button" class="app-board-btn" id="app-board-flip" title="Brett drehen">⇅</button>
          <select id="app-engine-profile" class="app-engine-profile" title="Engine-Profil">
            <option value="stockfish18">Stockfish 18</option>
          </select>
          <button type="button" class="app-board-btn" id="app-engine-move" title="Enginezug spielen">Engine</button>
        </div>
        <p id="app-board-status" class="app-board-status">Figur und Zielfeld anklicken.</p>
      </div>
      <div id="app-flex-content" class="app-flex-content">
        <h3>Brett</h3>
        <ol id="app-board-moves" class="app-board-moves"></ol>
      </div>
    </aside>

    <footer class="app-footer" id="app-footer">
      <form id="app-input-form" class="app-input-form">
        <input type="hidden" name="csrf_token" value="{$page.csrf_token|escape}">
        <div class="app-input-wrapper">
          <textarea id="app-input" name="message" rows="2" placeholder="Nachricht oder /help …" aria-label="Nachricht eingeben"></textarea>
          <div class="app-input-actions">
            <button type="button" class="app-action-btn" id="app-analyse-open" title="PGN analysieren">
              <span>📄</span>
              <span>PGN</span>
            </button>
            <button type="submit" class="app-send-btn" id="app-send">
              <span>Senden</span>
              <span aria-hidden="true">➤</span>
            </button>
          </div>
        </div>
        <div id="app-errors" class="app-errors" role="alert"></div>
      </form>
    </footer>
  </div>

  <dialog id="app-analyse-dialog" class="app-dialog" aria-labelledby="app-analyse-title">
    <form method="dialog" id="app-analyse-form">
      <h2 id="app-analyse-title">PGN analysieren</h2>
      <textarea id="app-analyse-pgn" rows="8" placeholder="PGN hier einfügen…" aria-label="PGN Inhalt"></textarea>
      <label>
        Analyse-Profil
        <select id="app-analyse-profile" aria-label="Analyse-Profil auswählen">
          <option value="quick">Schnelltest</option>
          <option value="full">Vollständig</option>
          <option value="ptg">PTG</option>
          <option value="stockfish_only">Nur Stockfish</option>
        </select>
      </label>
      <div class="app-dialog-actions">
        <button type="submit" class="app-btn-primary">Start</button>
        <button type="button" class="app-btn-secondary" id="app-analyse-cancel">Abbrechen</button>
      </div>
    </form>
  </dialog>
</div>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="app/assets/app.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js" integrity="sha256-DQN+eu7N5cFQgtlNc6pkgiMDsE+bWKwcwegE0QY=" crossorigin="anonymous"></script>
<script src="app/assets/app.js" defer></script>
{/block}
