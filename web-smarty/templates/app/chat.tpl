{extends file="app/layout.tpl"}

{block name="content"}
<section class="page-hero app-hero">
  <div class="page-hero-inner">
    <p class="eyebrow">{$page.eyebrow|escape}</p>
    <h1>{$page.heading|escape}</h1>
    <p class="page-lead">{$page.lead|escape}</p>
    {if $page.user.profile_key}
      <p class="page-lead app-profile-badge">Profil: {$page.user.player_display_name|escape} ({$page.user.training_name|escape})</p>
    {/if}
  </div>
</section>

<div id="cailama-app"
     class="app-shell"
     data-api-base="{$page.api_base|escape}"
     data-csrf="{$page.csrf_token|escape}"
     data-debug="{if $page.debug}1{else}0{/if}"
     data-profile="{$page.user.training_name|default:''|escape}"
     data-mobile="0">

  <div id="app-activity-bar" class="app-activity-bar" aria-live="polite" aria-atomic="true">
    <span class="app-activity-pulse" hidden></span>
    <span id="app-activity-label">Bereit</span>
    <label class="app-debug-toggle" title="Debug-Infos einblenden">
      <input type="checkbox" id="app-debug-toggle"{if $page.debug} checked{/if}>
      Debug
    </label>
  </div>

  <div class="app-grid">
    <aside class="app-sidebar" id="app-sidebar">
      <h2>Sessions</h2>
      <button type="button" class="button light ui-button" id="app-new-session">Neue Session</button>
      <ul id="app-session-list" class="app-session-list" data-role="listview" data-inset="true"></ul>
      <h2>Brett</h2>
      <div id="app-board-svg" class="app-board"></div>
      <p id="app-board-fen" class="app-meta"></p>
    </aside>

    <div class="app-main">
      <div id="app-messages" class="app-messages" aria-live="polite"></div>
      <form id="app-input-form" class="app-input-form">
        <input type="hidden" name="csrf_token" value="{$page.csrf_token|escape}">
        <textarea id="app-input" name="message" rows="3" placeholder="Nachricht oder /help …"></textarea>
        <div class="app-actions">
          <button type="submit" class="button ui-button" id="app-send">Senden</button>
          <button type="button" class="button light ui-button" id="app-analyse-open">PGN analysieren</button>
        </div>
      </form>
      <div id="app-errors" class="app-errors" role="alert"></div>
    </div>
  </div>

  <dialog id="app-analyse-dialog" class="app-dialog">
    <form method="dialog" id="app-analyse-form">
      <h2>PGN analysieren</h2>
      <textarea id="app-analyse-pgn" rows="8" placeholder="PGN einfügen"></textarea>
      <label>
        Profil
        <select id="app-analyse-profile">
          <option value="quick">Schnelltest</option>
          <option value="full">Vollständig</option>
          <option value="ptg">PTG</option>
          <option value="stockfish_only">Nur Stockfish</option>
        </select>
      </label>
      <div class="app-actions">
        <button type="submit" class="button ui-button">Start</button>
        <button type="button" class="button light ui-button" id="app-analyse-cancel">Abbrechen</button>
      </div>
    </form>
  </dialog>
</div>

<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.min.css">
<link rel="stylesheet" href="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.css">
<link rel="stylesheet" href="app/assets/app.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js" integrity="sha256-DQN+eu7N5cWHj8FQgtlNc6pkgiMDsE+bWKwcwegE0QY=" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/mobile/1.4.5/jquery.mobile-1.4.5.min.js"></script>
<script src="app/assets/app.js" defer></script>
{/block}
