<!doctype html>
<html lang="pl">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#090d0b">
    <title>MintCore — panel serwera</title>
    <style>
      @font-face {
        font-family: "Minecraft Five";
        src: url("assets/MinecraftFiveBold.otf") format("opentype");
        font-display: swap;
      }

      @font-face {
        font-family: "Minecraft Five Bold";
        src: url("assets/MinecraftFiveBold.otf") format("opentype");
        font-display: swap;
      }

      @font-face {
        font-family: "Minecraft";
        src: url("assets/Minecraft.ttf") format("truetype");
        font-display: swap;
      }

      :root {
        color-scheme: dark;
        --bg: #090d0b;
        --surface: #101713;
        --surface-raised: #151e18;
        --line: #26352b;
        --muted: #829287;
        --text: #e7f2e9;
        --green: #88f0a3;
        --green-dark: #143522;
        --red: #ff7777;
        --mono: "Minecraft Five", "Minecraft", "Courier New", monospace;
      }

      * { box-sizing: border-box; }

      body {
        min-width: 340px;
        min-height: 100vh;
        margin: 0;
        background:
          radial-gradient(ellipse at 82% -10%, rgba(43, 105, 61, .22), transparent 38rem),
          linear-gradient(135deg, #0b100d 0%, var(--bg) 60%);
        color: var(--text);
        font-family: var(--mono);
        font-size: 11px;
      }

      button, input, select { font: inherit; }
      button {
        color: inherit;
        transition: transform .16s ease, filter .18s ease, color .2s ease, border-color .2s ease, box-shadow .18s ease;
      }
      button:hover:not(:disabled) { transform: scale(1.025); }
      button:active:not(:disabled) { transform: scale(.975); }

      .shell { width: min(1500px, 100%); margin: 0 auto; padding: 22px clamp(14px, 3vw, 42px) 32px; }
      .topbar { display: flex; align-items: center; justify-content: space-between; gap: 20px; margin-bottom: 30px; }
      .brand { display: flex; align-items: center; gap: 13px; }
      .brand-mark {
        display: grid;
        width: 42px;
        height: 42px;
        place-items: center;
        border: 1px solid #477654;
        background: linear-gradient(145deg, #23452d, #122318);
        color: var(--green);
        font-size: 17px;
        box-shadow: inset 0 0 0 3px rgba(8, 14, 10, .45), 0 8px 25px rgba(46, 135, 67, .13);
      }
      .brand-name { margin: 0; font-size: 18px; letter-spacing: 1px; }
      .brand-caption { margin: 5px 0 0; color: var(--muted); font-size: 9px; letter-spacing: .8px; }
      .top-status { display: flex; align-items: center; gap: 15px; color: var(--muted); }
      .online-pill, .connection-pill { display: inline-flex; align-items: center; gap: 8px; padding: 9px 12px; border: 1px solid var(--line); background: rgba(15, 23, 18, .8); }
      .online-dot, .connection-dot { width: 7px; height: 7px; border-radius: 50%; background: var(--green); box-shadow: 0 0 12px rgba(136, 240, 163, .65); }
      .connection-dot.offline { background: var(--red); box-shadow: 0 0 12px rgba(255, 119, 119, .55); }

      .hero { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 20px; }
      .eyebrow { margin: 0 0 8px; color: var(--green); font-size: 9px; letter-spacing: 2px; }
      h1 { margin: 0; font-size: clamp(20px, 3vw, 30px); line-height: 1.4; }
      .hero-copy { max-width: 620px; margin: 8px 0 0; color: var(--muted); line-height: 1.8; }
      .workspace { overflow: hidden; border: 1px solid var(--line); background: rgba(14, 20, 16, .95); box-shadow: 0 24px 80px rgba(0, 0, 0, .28); animation: workspace-enter .35s ease-out both; }
      .toolbar { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 15px; border-bottom: 1px solid var(--line); background: #111813; }
      .tabs { position: relative; display: flex; align-items: center; gap: 6px; isolation: isolate; }
      .tabs::before {
        position: absolute;
        z-index: 0;
        top: 0;
        bottom: 0;
        left: 0;
        width: var(--tab-active-width, 0px);
        transform: translateX(var(--tab-active-x, 0px));
        border: 1px solid #31503a;
        background: #17241a;
        content: "";
        pointer-events: none;
        transition: transform .28s cubic-bezier(.2,.8,.2,1), width .28s cubic-bezier(.2,.8,.2,1);
      }
      .tab { position: relative; z-index: 1; padding: 10px 14px; border: 1px solid transparent; background: transparent; color: var(--muted); cursor: pointer; }
      .tab:hover { color: var(--text); }
      .tab.active { border-color: transparent; background: transparent; color: var(--green); }
      .toolbar-actions { display: flex; align-items: center; gap: 10px; }
      .select-wrap { display: flex; align-items: center; gap: 9px; color: var(--muted); }
      select, input {
        min-height: 38px;
        border: 1px solid var(--line);
        border-radius: 0;
        background: #0b100d;
        color: var(--text);
        outline: none;
      }
      select { max-width: 190px; padding: 0 30px 0 10px; }
      select:focus, input:focus { border-color: #67b47a; }
      .icon-button { min-width: 38px; min-height: 38px; border: 1px solid var(--line); background: #101711; color: var(--muted); cursor: pointer; }
      .icon-button:hover { border-color: #4a7553; color: var(--green); }

      .console-layout { display: grid; grid-template-columns: minmax(0, 1fr) minmax(300px, 390px); min-height: 570px; }
      .terminal-column { display: flex; min-width: 0; min-height: 570px; flex-direction: column; border-right: 1px solid var(--line); }
      .terminal-head, .gui-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 45px; padding: 0 15px; border-bottom: 1px solid #202b23; color: var(--muted); }
      .terminal-head strong, .gui-head strong { color: #cbd9ce; font-weight: normal; }
      .terminal-lights { display: flex; gap: 5px; }
      .terminal-lights i { width: 7px; height: 7px; background: #304337; }
      .terminal-lights i:first-child { background: #b96559; }
      .terminal-lights i:nth-child(2) { background: #caa55d; }
      .terminal-lights i:nth-child(3) { background: #70ae78; }
      .log-list { display: flex; flex: 1; min-height: 440px; flex-direction: column; gap: 5px; overflow: auto; padding: 14px 15px; background-image: linear-gradient(rgba(255,255,255,.012) 1px, transparent 1px); background-size: 100% 25px; }
      .log-entry { display: grid; grid-template-columns: 77px 66px minmax(0, 1fr); align-items: baseline; gap: 8px; min-height: 21px; line-height: 1.65; overflow-wrap: anywhere; }
      .log-time { color: #68786d; font-size: 9px; white-space: nowrap; }
      .log-level { color: #74857a; font-size: 9px; }
      .log-level.WARNING { color: #f0c46d; }
      .log-level.SEVERE { color: #ff7777; }
      .log-level.CHAT { color: #76c9a0; }
      .log-level.COMMAND { color: #a68afb; }
      .log-message { color: #d1ddd3; white-space: pre-wrap; }
      .chat-name { color: var(--green); }
      .empty-log { margin: auto; color: #718177; line-height: 2; text-align: center; }
      .command-form { display: flex; gap: 9px; padding: 13px 15px; border-top: 1px solid var(--line); background: #101712; }
      .prompt { align-self: center; color: var(--green); font-size: 14px; }
      .command-input { flex: 1; min-width: 0; padding: 0 11px; }
      .send-button, .primary-button { min-height: 38px; padding: 0 15px; border: 1px solid #438554; background: linear-gradient(180deg, #244c30, #1a3824); color: #d8ffe1; cursor: pointer; }
      .send-button:hover, .primary-button:hover { filter: brightness(1.16); }
      .send-button:disabled { opacity: .5; cursor: wait; }
      .run-context { padding: 0 15px 11px; background: #101712; color: #75867a; font-size: 9px; }
      .run-context span { color: #b5c8b9; }

      .gui-column { min-width: 0; background: #0d130f; }
      .gui-body { display: flex; min-height: 515px; flex-direction: column; align-items: center; justify-content: flex-start; overflow: auto; padding: 24px 12px; }
      .inventory-frame { width: min(100%, 352px); padding: 7px; border: 2px solid #f4f4f4; background: #c6c6c6; box-shadow: inset 0 0 0 2px #555, inset 0 0 0 3px #eee, 0 10px 25px rgba(0,0,0,.3); image-rendering: pixelated; }
      .inventory-title { min-height: 24px; padding: 4px 3px 6px; color: #404040; font-size: 10px; text-shadow: 1px 1px #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
      .slot-grid { display: grid; grid-template-columns: repeat(9, minmax(0, 1fr)); gap: 2px; }
      .slot {
        position: relative;
        display: grid;
        aspect-ratio: 1;
        min-width: 0;
        place-items: center;
        border: 2px solid;
        border-color: #373737 #fff #fff #373737;
        background: #8b8b8b;
        color: #f3f3f3;
        box-shadow: inset 1px 1px #555, inset -1px -1px #bdbdbd;
        cursor: default;
      }
      .slot.empty { background: #8b8b8b; }
      .slot:focus-visible { outline: 2px solid #fff; outline-offset: -3px; z-index: 1; }
      .slot-icon { width: 78%; height: 78%; image-rendering: pixelated; filter: drop-shadow(1px 1px 0 rgba(0,0,0,.3)); }
      .slot-count { position: absolute; right: 1px; bottom: 0; color: #fff; font-family: var(--mono); font-size: 9px; line-height: 1; text-shadow: 1px 1px 0 #333, -1px -1px 0 #333; pointer-events: none; }
      .gui-placeholder { color: #617067; font-size: 10px; line-height: 2; text-align: center; }
      .gui-placeholder-mark { display: grid; width: 54px; height: 54px; margin: 0 auto 15px; place-items: center; border: 1px solid #34463a; color: #819788; font-size: 22px; }
      .gui-meta { width: min(100%, 352px); margin-top: 10px; color: #85958a; font-size: 9px; line-height: 1.8; overflow-wrap: anywhere; text-align: center; }
      .mc-tooltip { position: fixed; z-index: 20; max-width: min(260px, calc(100vw - 20px)); padding: 6px 8px; border: 2px solid #2a1b3d; background: rgba(16, 8, 24, .98); box-shadow: 0 2px 0 #100817, 0 8px 20px rgba(0,0,0,.5); color: #fff; font-family: "Minecraft", "Minecraft Five", monospace; font-size: 10px; line-height: 1.55; white-space: pre-wrap; overflow-wrap: anywhere; pointer-events: none; }
      .mc-tooltip[hidden] { display: none; }
      .mc-tooltip-name { color: #fff; }
      .mc-tooltip-lore { color: #aaa; }
      .mc-tooltip .mc-font-smallcaps { font-family: "Minecraft Five", "Minecraft", monospace; }
      .mc-tooltip .mc-font-bold { font-family: "Minecraft Five Bold", "Minecraft Five", "Minecraft", monospace; }

      .checks-panel { display: none; min-height: 570px; }
      .checks-panel.active { display: block; }
      .checks-head { display: flex; align-items: center; justify-content: space-between; padding: 22px; border-bottom: 1px solid var(--line); }
      .checks-title { margin: 0; font-size: 16px; }
      .checks-subtitle { margin: 8px 0 0; color: var(--muted); font-size: 9px; }
      .checks-count { padding: 8px 11px; border: 1px solid #344638; background: #17231a; color: var(--green); }
      .check-list { display: flex; flex-direction: column; gap: 7px; padding: 16px; }
      .check-entry { display: grid; grid-template-columns: 130px 82px minmax(0, 1fr); gap: 12px; padding: 13px; border: 1px solid #202c23; background: #111813; line-height: 1.7; overflow-wrap: anywhere; }
      .check-time { color: #748378; font-size: 9px; }
      .check-level { color: var(--green); }
      .check-message { color: #c7d4ca; }
      .checks-empty { padding: 66px 20px; color: var(--muted); line-height: 2; text-align: center; }
      .footer { display: flex; justify-content: space-between; gap: 15px; margin-top: 14px; color: #6f7f74; font-size: 9px; line-height: 1.8; }
      .footer strong { color: #a4b7a9; font-weight: normal; }

      .login-overlay { position: fixed; z-index: 5; inset: 0; display: grid; place-items: center; padding: 18px; background: rgba(5, 8, 6, .88); backdrop-filter: blur(7px); }
      .login-overlay[hidden] { display: none; }
      .login-card { width: min(420px, 100%); padding: 27px; border: 1px solid #35513c; background: #101713; box-shadow: 0 24px 90px rgba(0,0,0,.5); }
      .login-card h2 { margin: 0 0 10px; font-size: 18px; }
      .login-card p { color: var(--muted); font-size: 10px; line-height: 1.9; }
      .login-card label { display: block; margin: 20px 0 7px; color: #b4c5b8; }
      .login-input { width: 100%; padding: 0 11px; }
      .login-card .primary-button { width: 100%; margin-top: 12px; }
      .login-card a { color: var(--green); }
      .login-error { min-height: 16px; color: var(--red) !important; }
      .notice { position: fixed; z-index: 4; right: 18px; bottom: 18px; max-width: min(400px, calc(100vw - 36px)); padding: 12px 15px; border: 1px solid #3b5f43; background: #15241a; color: #d9ffe0; line-height: 1.8; box-shadow: 0 12px 35px rgba(0,0,0,.35); }
      .notice.error { border-color: #6a3b3b; background: #2a1717; color: #ffd0d0; }

      @keyframes workspace-enter {
        from { opacity: 0; transform: translateY(5px); }
        to { opacity: 1; transform: translateY(0); }
      }

      @media (max-width: 900px) {
        .console-layout { grid-template-columns: minmax(0, 1fr) 310px; }
        .toolbar { align-items: flex-start; flex-direction: column; }
        .toolbar-actions { width: 100%; justify-content: space-between; }
      }
      @media (max-width: 700px) {
        .topbar { align-items: flex-start; }
        .top-status { flex-direction: column; align-items: flex-end; gap: 6px; }
        .hero { align-items: flex-start; flex-direction: column; }
        .console-layout { grid-template-columns: minmax(0, 1fr); }
        .terminal-column { border-right: 0; border-bottom: 1px solid var(--line); }
        .gui-body { min-height: 350px; }
        .log-entry { grid-template-columns: 61px 55px minmax(0, 1fr); gap: 5px; }
        .check-entry { grid-template-columns: 1fr; gap: 2px; }
        .footer { flex-direction: column; }
      }
      @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
          animation-duration: .01ms !important;
          animation-iteration-count: 1 !important;
          scroll-behavior: auto !important;
          transition-duration: .01ms !important;
        }
      }
    </style>
  </head>
  <body>
    <div class="login-overlay" id="login-overlay">
      <form class="login-card" id="login-form">
        <div class="brand-mark" style="margin-bottom:18px">M</div>
        <h2>Panel MintCore</h2>
        <p id="login-instructions">Wpisz token z <strong>plugins/MintCore/config.yml</strong>, aby połączyć się z konsolą serwera.</p>
        <label for="token-input" id="login-label">Token dostępu</label>
        <input class="login-input" id="token-input" type="password" autocomplete="current-password" required>
        <p class="login-error" id="login-error" role="status" aria-live="polite"></p>
        <p id="local-file-help" hidden>Ta strona jest otwarta jako plik. Otworz panel z serwera MintCore: <a href="http://127.0.0.1:8765/">http://127.0.0.1:8765/</a></p>
        <button class="primary-button" type="submit">POŁĄCZ Z SERWEREM</button>
      </form>
    </div>

    <main class="shell">
      <header class="topbar">
        <div class="brand">
          <div class="brand-mark">M</div>
          <div>
            <p class="brand-name">MintCore</p>
            <p class="brand-caption">PANEL ZARZĄDZANIA SERWEREM</p>
          </div>
        </div>
        <div class="top-status">
          <div class="online-pill"><span class="online-dot"></span><span><b id="online-count">0</b> graczy online</span></div>
          <div class="connection-pill"><span class="connection-dot offline" id="connection-dot"></span><span id="connection-label">Rozłączono</span></div>
        </div>
      </header>

      <section class="hero">
        <div>
          <p class="eyebrow">MINTCORE / LIVE CONTROL</p>
          <h1>Centrum serwera</h1>
          <p class="hero-copy">Logi na żywo, komendy i szybki podgląd menu Minecraft. Wybierz gracza, aby wykonywać polecenia w jego kontekście i zobaczyć otwarte GUI.</p>
        </div>
      </section>

      <section class="workspace">
        <div class="toolbar">
          <nav class="tabs" aria-label="Zakładki panelu">
            <button class="tab active" id="tab-console" type="button">KONSOLA</button>
            <button class="tab" id="tab-checks" type="button">CZEKI <span id="checks-tab-count"></span></button>
          </nav>
          <div class="toolbar-actions">
            <label class="select-wrap" for="player-select">Wykonaj jako
              <select id="player-select"><option value="">Konsola serwera</option></select>
            </label>
            <button class="icon-button" id="clear-button" type="button" title="Wyczyść widok logów">⌫</button>
          </div>
        </div>

        <section class="console-layout" id="console-panel">
          <div class="terminal-column">
            <div class="terminal-head">
              <span><span class="terminal-lights"><i></i><i></i><i></i></span> &nbsp; <strong>LOGI SERWERA</strong></span>
              <span id="log-count">0 wpisów</span>
            </div>
            <div class="log-list" id="log-list" aria-live="polite">
              <div class="empty-log">Oczekiwanie na połączenie z serwerem...</div>
            </div>
            <form class="command-form" id="command-form">
              <span class="prompt">›</span>
              <input class="command-input" id="command-input" type="text" placeholder="Wpisz komendę serwera..." autocomplete="off" maxlength="1024">
              <button class="send-button" id="send-button" type="submit">WYŚLIJ</button>
            </form>
            <div class="run-context">Komendy gracza zachowują jego uprawnienia. <span id="context-label">Wybrano: konsola</span></div>
          </div>
          <aside class="gui-column">
            <div class="gui-head"><strong>PODGLĄD GUI</strong><span id="gui-player-label">OCZEKUJE</span></div>
            <div class="gui-body" id="gui-body">
              <div class="gui-placeholder"><div class="gui-placeholder-mark">▦</div>Wybierz gracza i otwórz menu<br>komendą, aby zobaczyć jego układ.</div>
            </div>
          </aside>
        </section>

        <section class="checks-panel" id="checks-panel">
          <div class="checks-head">
            <div><h2 class="checks-title">Dziennik czeków</h2><p class="checks-subtitle">Wpisy systemu czeków MintCore aktualizowane na żywo.</p></div>
            <span class="checks-count" id="checks-count">0 ZDARZEŃ</span>
          </div>
          <div class="check-list" id="check-list"><div class="checks-empty">Oczekiwanie na zdarzenia czeków...</div></div>
        </section>
      </section>

      <footer class="footer">
        <span><strong>MintCore</strong> · Konsola i dziennik zdarzeń</span>
        <span id="footer-security">Panel domyślnie dostępny tylko lokalnie · Chroń swój token</span>
      </footer>
    </main>
    <div class="notice" id="notice" hidden></div>

    <script src="/relay-config.js"></script>
    <script>
      const $ = (selector) => document.querySelector(selector);
      const RELAY_MODE = window.MINTCORE_RELAY === true;
      const loginOverlay = $("#login-overlay");
      const tokenInput = $("#token-input");
      const loginError = $("#login-error");
      const logList = $("#log-list");
      const checkList = $("#check-list");
      const playerSelect = $("#player-select");
      const localFileHelp = $("#local-file-help");
      const loginInstructions = $("#login-instructions");
      const loginLabel = $("#login-label");
      const loginSubmitButton = $("#login-form button[type=submit]");
      let token = RELAY_MODE ? "" : (sessionStorage.getItem("mintcore-token") || "");
      let relayAuthenticated = false;
      let loginStage = "primary";
      let activationPending = false;
      let polling = false;
      let clearedBefore = 0;

      function normalizeFontText(value) {
        const replacements = {
          "ą":"a", "ć":"c", "ę":"e", "ł":"l", "ń":"n", "ś":"s", "ź":"z", "ż":"z",
          "Ą":"A", "Ć":"C", "Ę":"E", "Ł":"L", "Ń":"N", "Ś":"S", "Ź":"Z", "Ż":"Z"
        };
        return String(value ?? "").replace(/[ąćęłńśźżĄĆĘŁŃŚŹŻ]/g, (character) => replacements[character]);
      }

      function normalizeVisibleText() {
        const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT);
        let node;
        while ((node = walker.nextNode())) {
          if (!node.parentElement.closest("script, style")) {
            node.nodeValue = normalizeFontText(node.nodeValue);
          }
        }
        document.querySelectorAll("[placeholder], [title], [aria-label]").forEach((element) => {
          for (const attribute of ["placeholder", "title", "aria-label"]) {
            if (element.hasAttribute(attribute)) {
              element.setAttribute(attribute, normalizeFontText(element.getAttribute(attribute)));
            }
          }
        });
      }

      function parseComponent(value) {
        if (typeof value === "string") return JSON.parse(value);
        if (value && typeof value === "object") return value;
        throw new TypeError("Nieprawidłowy komponent tekstu.");
      }

      const minecraftColors = {
        "0":"#000000", "1":"#0000aa", "2":"#00aa00", "3":"#00aaaa",
        "4":"#aa0000", "5":"#aa00aa", "6":"#ffaa00", "7":"#aaaaaa",
        "8":"#555555", "9":"#5555ff", "a":"#55ff55", "b":"#55ffff",
        "c":"#ff5555", "d":"#ff55ff", "e":"#ffff55", "f":"#ffffff",
        "black":"#000000", "dark_blue":"#0000aa", "dark_green":"#00aa00",
        "dark_aqua":"#00aaaa", "dark_red":"#aa0000", "dark_purple":"#aa00aa",
        "gold":"#ffaa00", "gray":"#aaaaaa", "grey":"#aaaaaa",
        "dark_gray":"#555555", "dark_grey":"#555555", "blue":"#5555ff",
        "green":"#55ff55", "aqua":"#55ffff", "red":"#ff5555",
        "light_purple":"#ff55ff", "yellow":"#ffff55", "white":"#ffffff"
      };

      function resolveMinecraftColor(color) {
        if (typeof color !== "string") return "";
        if (/^#[\da-f]{6}$/i.test(color)) return color;
        return minecraftColors[color.toLowerCase()] || "";
      }

      function appendLegacyText(container, text, inherited = {}) {
        text = normalizeFontText(text);
        const pattern = /(?:§|&)(#[0-9a-f]{6}|[0-9a-fk-or])/ig;
        let currentColor = resolveMinecraftColor(inherited.color);
        let bold = Boolean(inherited.bold);
        let italic = Boolean(inherited.italic);
        let underlined = Boolean(inherited.underlined);
        let strikethrough = Boolean(inherited.strikethrough);
        const isGuiTooltip = Boolean(inherited.tooltip || container.closest(".mc-tooltip"));
        let cursor = 0;
        let match;
        const appendStyledText = (value) => {
          if (!value) return;
          const span = document.createElement("span");
          span.textContent = value;
          if (currentColor) span.style.color = currentColor;
          if (bold) {
            span.style.fontWeight = "bold";
            if (isGuiTooltip) span.classList.add("mc-font-bold");
          }
          if (italic) span.style.fontStyle = "italic";
          const decorations = [];
          if (underlined) decorations.push("underline");
          if (strikethrough) decorations.push("line-through");
          if (decorations.length) span.style.textDecorationLine = decorations.join(" ");
          if (inherited.smallCaps && isGuiTooltip) span.classList.add("mc-font-smallcaps");
          container.append(span);
        };
        while ((match = pattern.exec(text)) !== null) {
          appendStyledText(text.slice(cursor, match.index));
          const code = match[1].toLowerCase();
          if (code[0] === "#") currentColor = code;
          else if (minecraftColors[code]) {
            currentColor = minecraftColors[code];
            if (code === "b" && isGuiTooltip) bold = true;
          }
          else if (code === "l") bold = true;
          else if (code === "o") italic = true;
          else if (code === "n") underlined = true;
          else if (code === "m") strikethrough = true;
          else if (code === "r") { currentColor = ""; bold = false; italic = false; underlined = false; strikethrough = false; }
          cursor = pattern.lastIndex;
        }
        appendStyledText(text.slice(cursor));
      }

      function appendComponent(container, component, inherited = {}) {
        if (component == null) return;
        if (typeof component === "string") {
          appendLegacyText(container, component);
          return;
        }
        if (Array.isArray(component)) {
          component.forEach((part) => appendComponent(container, part, inherited));
          return;
        }
        const span = document.createElement("span");
        const isGuiTooltip = Boolean(inherited.tooltip || container.closest(".mc-tooltip"));
        const style = {
          color: component.color !== undefined ? component.color : inherited.color,
          bold: component.bold !== undefined ? component.bold : inherited.bold,
          italic: component.italic !== undefined ? component.italic : inherited.italic,
          underlined: component.underlined !== undefined ? component.underlined : inherited.underlined,
          strikethrough: component.strikethrough !== undefined ? component.strikethrough : inherited.strikethrough,
          tooltip: isGuiTooltip,
          smallCaps: component.font !== undefined
            ? /small[-_ ]?caps|minecraft:alt/i.test(component.font)
            : inherited.smallCaps
        };
        if (component.text != null) appendLegacyText(span, component.text, style);
        if (component.translate != null && component.text == null) appendLegacyText(span, component.translate, style);
        if (component.with) component.with.forEach((part) => appendComponent(span, part, style));
        if (component.extra) component.extra.forEach((part) => appendComponent(span, part, style));
        if (span.childNodes.length || span.textContent) container.append(span);
      }

      const itemTooltip = document.createElement("div");
      itemTooltip.className = "mc-tooltip";
      itemTooltip.hidden = true;
      itemTooltip.setAttribute("role", "tooltip");
      document.body.append(itemTooltip);

      function materialIcon(material) {
        const type = String(material || "STONE").toUpperCase();
        const palette = type.includes("DIAMOND") ? ["#42d9d0", "#a4fff3", "#159b9a"]
          : type.includes("EMERALD") ? ["#28c86f", "#9dff9f", "#087844"]
          : type.includes("GOLD") ? ["#f1c232", "#fff18a", "#a66b08"]
          : type.includes("IRON") ? ["#d4d7d9", "#ffffff", "#7d858b"]
          : type.includes("NETHERITE") ? ["#65545f", "#b397a5", "#342d38"]
          : type.includes("COPPER") ? ["#d87942", "#ffc080", "#874128"]
          : type.includes("REDSTONE") ? ["#d52f34", "#ff8c70", "#791d2a"]
          : type.includes("LAPIS") ? ["#4169d8", "#9bbcff", "#23398a"]
          : type.includes("AMETHYST") ? ["#ae71df", "#edbcff", "#613998"]
          : type.includes("WOOD") || type.includes("LOG") || type.includes("PLANK")
            ? ["#a8733e", "#e0b36e", "#684127"]
            : type.includes("LEAVES") || type.includes("SAPLING")
              ? ["#58a83e", "#a4df62", "#28622f"]
              : ["#92999c", "#d9e0df", "#4d555b"];
        const [base, light, dark] = palette;
        let shape;

        if (type.includes("SWORD")) {
          shape = `<path fill="${light}" d="M11 1h4v4l-7 7-3-3z"/><path fill="${base}" d="M10 3h3v2l-5 5-1-1z"/><path fill="#77512f" d="M5 9l2 2-3 3-2-2z"/><path fill="${dark}" d="M1 13l2-2 2 2-2 2zM4 10l2-2 2 2-2 2z"/>`;
        } else if (type.includes("PICKAXE")) {
          shape = `<path fill="#76502e" d="M7 5h3v9H7zM5 12h3v3H5z"/><path fill="${base}" d="M1 2h3V1h8v2h3v3h-3V5H6v2H3V5H1z"/><path fill="${light}" d="M3 2h8v1H4v2H3z"/>`;
        } else if (type.includes("AXE")) {
          shape = `<path fill="#76502e" d="M7 4h3v10H7zM5 12h3v3H5z"/><path fill="${base}" d="M2 1h8v2h4v5h-3v2H6V7H3z"/><path fill="${light}" d="M3 2h6v2h3v2H5v2H3z"/>`;
        } else if (type.includes("SHOVEL") || type.includes("HOE")) {
          shape = `<path fill="#76502e" d="M7 5h3v8H7zM5 11h3v4H5z"/><path fill="${base}" d="M4 1h8v3H4z"/><path fill="${light}" d="M5 2h6v1H5z"/>`;
        } else if (type.includes("HELMET")) {
          shape = `<path fill="${dark}" d="M2 7a6 6 0 0112 0v5H2z"/><path fill="${base}" d="M3 7a5 5 0 0110 0v3H3z"/><path fill="${light}" d="M5 4h5v1H5z"/><path fill="${dark}" d="M1 11h14v3H1z"/>`;
        } else if (type.includes("CHESTPLATE")) {
          shape = `<path fill="${dark}" d="M4 2h3v2h2V2h3l3 3-2 3v7H3V8L1 5z"/><path fill="${base}" d="M5 3h2v3h2V3h2l2 2-2 3v5H5V8L3 5z"/><path fill="${light}" d="M5 4h1v3H5z"/>`;
        } else if (type.includes("BOOTS")) {
          shape = `<path fill="${dark}" d="M3 2h4v8h2v3l3 1v2H2v-3h1zM10 2h4v8h1v3l1 1v2H9v-3h1z"/><path fill="${base}" d="M4 3h2v7l3 2v2H4zM11 3h2v7l2 2v2h-4z"/>`;
        } else if (type.includes("BOW")) {
          shape = `<path fill="none" stroke="${base}" stroke-width="2" d="M4 2Q14 8 4 14"/><path fill="none" stroke="${light}" stroke-width="1" d="M4 2L8 8 4 14"/><path fill="#76502e" d="M3 1h3v2H4v2H2V3zM3 13h3v2H3z"/>`;
        } else if (type.includes("ARROW")) {
          shape = `<path fill="#76502e" d="M7 4h2v8H7z"/><path fill="${light}" d="M5 2h6v4h-2v3H7V6H5z"/><path fill="${base}" d="M5 11h2v3H5zM9 11h2v3H9z"/>`;
        } else if (type.includes("POTION") || type.includes("BOTTLE")) {
          shape = `<path fill="${dark}" d="M6 1h4v4l2 2v7l-2 2H5l-2-2V7l3-2z"/><path fill="#d9eaff" d="M7 2h2v4l2 2v5H5V8l2-2z"/><path fill="${base}" d="M5 9h6v4H5z"/><path fill="${light}" d="M6 8h2v1H6z"/>`;
        } else if (type.includes("BOOK")) {
          shape = `<path fill="${dark}" d="M2 2h5q2 0 2 2 0-2 2-2h3v12h-4q-1 0-1 1-1-1-2-1H2z"/><path fill="${base}" d="M3 3h4q2 0 2 2v8q-1-1-2-1H3zM10 4h3v8h-3q-1 0-1 1V5z"/><path fill="${light}" d="M4 4h2v1H4zM11 5h1v4h-1z"/>`;
        } else if (type.includes("PAPER") || type.includes("MAP")) {
          shape = `<path fill="#d9d2a7" d="M3 1h8l3 3v11H3z"/><path fill="#fff3c8" d="M4 2h6v3h3v9H4z"/><path fill="${base}" d="M6 7h6v1H6zM6 9h5v1H6zM6 11h6v1H6z"/>`;
        } else if (type.includes("CHEST") || type.includes("BARREL") || type.includes("SHULKER")) {
          shape = `<path fill="${dark}" d="M1 4h14v10H1z"/><path fill="${base}" d="M2 5h12v8H2z"/><path fill="${light}" d="M2 5h12v2H2z"/><path fill="${dark}" d="M7 8h2v3H7zM1 12h14v2H1z"/>`;
        } else if (type.includes("HEAD") || type.includes("SKULL")) {
          shape = `<path fill="${dark}" d="M3 3h10v8H3zM5 11h6v4H5z"/><path fill="${base}" d="M4 4h8v6H4z"/><path fill="${light}" d="M5 5h2v2H5zM9 5h2v2H9z"/><path fill="${dark}" d="M7 8h2v2H7z"/>`;
        } else if (type.includes("APPLE") || type.includes("CARROT") || type.includes("POTATO") || type.includes("BREAD") || type.includes("COOKIE") || /MEAT|BEEF|PORK|CHICKEN|MUTTON|RABBIT|COD|SALMON/.test(type)) {
          shape = type.includes("CARROT")
            ? `<path fill="${base}" d="M3 5l8 2-3 8-3-2z"/><path fill="#5caa45" d="M5 2h3v4H6zM8 3h3v3H8z"/><path fill="${light}" d="M5 7h3v2H5z"/>`
            : `<path fill="${dark}" d="M6 3h4v2h3v3h2v4h-2v2H5v-2H3V8h2V5h2z"/><path fill="${base}" d="M6 4h4v2h3v3h2v3H5v-2H4V8h2z"/><path fill="${light}" d="M7 5h2v2H7z"/>`;
        } else if (/DIAMOND|EMERALD|AMETHYST|QUARTZ|CRYSTAL|PRISMARINE_SHARD/.test(type)) {
          shape = `<path fill="${dark}" d="M4 2h8l3 4-7 9-7-9z"/><path fill="${base}" d="M4 3h8l2 3-6 7-6-7z"/><path fill="${light}" d="M5 3h3L6 6H3zM9 3h2l2 3h-3z"/>`;
        } else if (/INGOT|NUGGET|RAW_/.test(type)) {
          shape = `<path fill="${dark}" d="M3 4h9l3 3-2 6H3L1 9z"/><path fill="${base}" d="M4 5h7l2 2-2 4H4L3 8z"/><path fill="${light}" d="M4 5h6l-2 2H3z"/>`;
        } else {
          shape = `<path fill="${dark}" d="M2 4l6-3 6 3v8l-6 3-6-3z"/><path fill="${light}" d="M2 4l6 3 6-3-6-3z"/><path fill="${base}" d="M2 5l5 3v6l-5-3z"/><path fill="${dark}" d="M9 8l5-3v6l-5 3z"/><path fill="${light}" d="M4 5l3 2v2L4 7z"/>`;
        }

        return `data:image/svg+xml;charset=utf-8,${encodeURIComponent(`<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><g shape-rendering="crispEdges">${shape}</g></svg>`)}`;
      }

      function prettyMaterial(material) {
        return normalizeFontText(String(material || "ITEM").toLowerCase().replaceAll("_", " "));
      }

      function positionItemTooltip(event, slot) {
        const rect = slot.getBoundingClientRect();
        const pointerX = event && Number.isFinite(event.clientX) ? event.clientX : rect.right;
        const pointerY = event && Number.isFinite(event.clientY) ? event.clientY : rect.top;
        const margin = 10;
        const gap = 14;
        const width = itemTooltip.offsetWidth;
        const height = itemTooltip.offsetHeight;
        const left = pointerX + gap + width + margin <= window.innerWidth
          ? pointerX + gap
          : Math.max(margin, pointerX - width - gap);
        const top = pointerY + gap + height + margin <= window.innerHeight
          ? pointerY + gap
          : Math.max(margin, pointerY - height - gap);
        itemTooltip.style.left = `${left}px`;
        itemTooltip.style.top = `${top}px`;
      }

      function showItemTooltip(slot, item, event) {
        itemTooltip.replaceChildren();
        const name = document.createElement("div");
        name.className = "mc-tooltip-name";
        itemTooltip.append(name);
        if (item.nameComponent) {
          try { appendComponent(name, parseComponent(item.nameComponent)); }
          catch { name.textContent = normalizeFontText(item.name || prettyMaterial(item.material)); }
        } else {
          name.textContent = normalizeFontText(item.name || prettyMaterial(item.material));
        }
        for (const line of item.lore || []) {
          const lore = document.createElement("div");
          lore.className = "mc-tooltip-lore";
          itemTooltip.append(lore);
          if (line && line.component) {
            try { appendComponent(lore, parseComponent(line.component)); }
            catch { appendLegacyText(lore, line.text || ""); }
          } else {
            appendLegacyText(lore, line && line.text ? line.text : "");
          }
        }
        itemTooltip.hidden = false;
        positionItemTooltip(event, slot);
      }

      function hideItemTooltip() {
        itemTooltip.hidden = true;
      }

      function showNotice(message, isError = false) {
        const notice = $("#notice");
        notice.textContent = normalizeFontText(message);
        notice.classList.toggle("error", isError);
        notice.hidden = false;
        window.setTimeout(() => { notice.hidden = true; }, 3500);
      }

      async function parseJsonResponse(response) {
        const contentType = response.headers.get("content-type") || "";
        if (!contentType.toLowerCase().includes("application/json")) {
          const error = new Error("Otrzymano odpowiedź inną niż JSON. Sprawdź adres relayu i konfigurację hostingu.");
          error.status = response.status;
          throw error;
        }
        const payload = await response.json();
        if (!response.ok) {
          const error = new Error(payload.error || `Błąd serwera (${response.status})`);
          error.status = response.status;
          throw error;
        }
        return payload;
      }

      async function api(path, options = {}) {
        let url = path;
        const headers = { ...(options.headers || {}) };
        if (RELAY_MODE) {
          const parsed = new URL(path, window.location.href);
          const action = parsed.pathname.endsWith("/api/state") ? "state" : "command";
          const query = new URLSearchParams(parsed.search);
          if (query.has("player")) {
            query.set("watch", query.get("player"));
            query.delete("player");
          }
          query.set("action", action);
          url = `bridge.php?${query.toString()}`;
        } else {
          headers.Authorization = `Bearer ${token}`;
        }
        const response = await fetch(url, {
          ...options,
          credentials: "same-origin",
          headers
        });
        return parseJsonResponse(response);
      }

      async function postRelayAction(action, fields = {}) {
        const response = await fetch(new URL(`/bridge.php?action=${encodeURIComponent(action)}`, window.location.origin), {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
          body: new URLSearchParams(fields)
        });
        return parseJsonResponse(response);
      }

      function setLoginStage(stage) {
        loginStage = stage;
        const awaitingActivation = stage === "awaiting-activation";
        const primary = stage === "primary";
        tokenInput.hidden = awaitingActivation;
        tokenInput.disabled = awaitingActivation;
        tokenInput.required = !awaitingActivation;
        loginLabel.hidden = awaitingActivation;
        loginSubmitButton.hidden = RELAY_MODE && !primary;
        loginInstructions.textContent = primary
          ? (RELAY_MODE ? "Wpisz hasło panelu, aby kontynuować." : "Wpisz token z plugins/MintCore/config.yml, aby połączyć się z konsolą serwera.")
          : awaitingActivation ? "Weryfikacja zakończona." : "Wpisz dodatkowe hasło dostępu.";
        loginLabel.textContent = primary ? (RELAY_MODE ? "Hasło panelu" : "Token dostępu") : "Dodatkowe hasło";
        tokenInput.value = "";
        loginError.textContent = "";
        if (!awaitingActivation) tokenInput.focus();
      }

      function renderEvents(events) {
        const visible = events.filter((event) => Date.parse(event.time) >= clearedBefore);
        const atBottom = logList.scrollHeight - logList.scrollTop - logList.clientHeight < 60;
        logList.replaceChildren();
        if (!visible.length) {
          const empty = document.createElement("div");
          empty.className = "empty-log";
          empty.textContent = "Brak wpisów. Nowe logi, czat i komendy pojawią się tutaj.";
          logList.append(empty);
        }
        for (const event of visible) {
          const row = document.createElement("div");
          row.className = "log-entry";
          const time = document.createElement("span");
          time.className = "log-time";
          time.textContent = new Date(event.time).toLocaleTimeString("pl-PL", { hour12: false });
          const level = document.createElement("span");
          level.className = `log-level ${event.level}`;
          level.textContent = event.level;
          const message = document.createElement("span");
          message.className = "log-message";
          if (event.component) {
            if (event.player) {
              const name = document.createElement("span");
              name.className = "chat-name";
              name.textContent = `${event.player}: `;
              message.append(name);
            }
            try { appendComponent(message, parseComponent(event.component)); }
            catch { appendLegacyText(message, event.message); }
          } else {
            appendLegacyText(message, event.message);
          }
          row.append(time, level, message);
          logList.append(row);
        }
        $("#log-count").textContent = `${visible.length} wpisów`;
        if (atBottom) logList.scrollTop = logList.scrollHeight;
      }

      function renderChecks(events) {
        const checks = events.filter((event) => event.message.includes("[MintCore | Czek]"));
        $("#checks-count").textContent = `${checks.length} ZDARZEŃ`;
        $("#checks-tab-count").textContent = checks.length ? `(${checks.length})` : "";
        checkList.replaceChildren();
        if (!checks.length) {
          const empty = document.createElement("div");
          empty.className = "checks-empty";
          empty.textContent = "Brak wpisów czeków. Zdarzenia pojawią się tutaj na żywo.";
          checkList.append(empty);
          return;
        }
        for (const event of [...checks].reverse()) {
          const row = document.createElement("div");
          row.className = "check-entry";
          const time = document.createElement("span");
          time.className = "check-time";
          time.textContent = new Date(event.time).toLocaleString("pl-PL", { hour12: false });
          const level = document.createElement("span");
          level.className = "check-level";
          level.textContent = event.level;
          const message = document.createElement("span");
          message.className = "check-message";
          appendLegacyText(message, event.message);
          row.append(time, level, message);
          checkList.append(row);
        }
      }

      function renderPlayers(players) {
        const selected = playerSelect.value;
        const options = [new Option("Konsola serwera", "")];
        for (const player of players) {
          options.push(new Option(`${normalizeFontText(player.name)} · ${normalizeFontText(player.world)}`, player.id));
        }
        playerSelect.replaceChildren(...options);
        if (players.some((player) => player.id === selected)) playerSelect.value = selected;
        $("#context-label").textContent = playerSelect.value
          ? `Wybrano: ${playerSelect.options[playerSelect.selectedIndex].text.split(" · ")[0]}`
          : "Wybrano: konsola";
      }

      function renderGui(gui) {
        const body = $("#gui-body");
        const selected = playerSelect.value;
        const selectedName = selected && playerSelect.selectedIndex >= 0
          ? playerSelect.options[playerSelect.selectedIndex].text.split(" · ")[0]
          : "";
        $("#gui-player-label").textContent = selectedName ? selectedName.toUpperCase() : "OCZEKUJE";
        body.replaceChildren();
        if (!gui) {
          const empty = document.createElement("div");
          empty.className = "gui-placeholder";
          const mark = document.createElement("div");
          mark.className = "gui-placeholder-mark";
          mark.textContent = "▦";
          const text = document.createElement("div");
          text.textContent = selectedName
            ? "Gracz nie ma otwartego menu. Wpisz komendę otwierającą GUI."
            : "Wybierz gracza i otwórz menu komendą, aby zobaczyć jego układ.";
          empty.append(mark, text);
          body.append(empty);
          return;
        }

        const frame = document.createElement("div");
        frame.className = "inventory-frame";
        const title = document.createElement("div");
        title.className = "inventory-title";
        title.textContent = normalizeFontText(gui.title || "MENU");
        const grid = document.createElement("div");
        grid.className = "slot-grid";
        const slotCount = Math.max(0, Math.min(54, Number(gui.size) || 0));
        grid.style.gridTemplateRows = `repeat(${Math.ceil(slotCount / 9)}, 1fr)`;
        grid.setAttribute("aria-label", `${title.textContent}, ${slotCount} slotow`);
        for (let index = 0; index < slotCount; index++) {
          const item = gui.slots[index] || null;
          const slot = document.createElement("div");
          slot.className = `slot${item ? "" : " empty"}`;
          if (item) {
            slot.tabIndex = 0;
            slot.setAttribute("role", "img");
            slot.setAttribute("aria-label", normalizeFontText(item.name || prettyMaterial(item.material)));
            const icon = document.createElement("img");
            icon.className = "slot-icon";
            icon.src = materialIcon(item.material);
            icon.alt = "";
            icon.draggable = false;
            const count = document.createElement("span");
            count.className = "slot-count";
            count.textContent = Number(item.amount) > 1 ? String(item.amount) : "";
            slot.append(icon, count);
            slot.addEventListener("pointerenter", (event) => showItemTooltip(slot, item, event));
            slot.addEventListener("pointermove", (event) => positionItemTooltip(event, slot));
            slot.addEventListener("pointerleave", hideItemTooltip);
            slot.addEventListener("focus", () => showItemTooltip(slot, item));
            slot.addEventListener("blur", hideItemTooltip);
          }
          grid.append(slot);
        }
        frame.append(title, grid);
        body.append(frame);
        const meta = document.createElement("div");
        meta.className = "gui-meta";
        meta.textContent = `${Math.ceil(slotCount / 9)} RZĘDÓW · ${slotCount} SLOTÓW · PODGLĄD GRACZA ${gui.player}`;
        body.append(meta);
      }

      async function refresh() {
        if (polling || (!token && !RELAY_MODE) || (RELAY_MODE && !relayAuthenticated)) return;
        polling = true;
        try {
          const query = playerSelect.value ? `?player=${encodeURIComponent(playerSelect.value)}` : "";
          const state = await api(`/api/state${query}`);
          $("#connection-dot").classList.remove("offline");
          $("#connection-label").textContent = "Połączono";
          $("#online-count").textContent = state.online;
          renderPlayers(state.players);
          renderEvents(state.events);
          renderChecks(state.events);
          renderGui(state.gui);
          normalizeVisibleText();
          if (RELAY_MODE) loginOverlay.hidden = true;
        } catch (error) {
          $("#connection-dot").classList.add("offline");
          $("#connection-label").textContent = "Brak połączenia";
          if ((!RELAY_MODE && error.message.includes("token")) || (RELAY_MODE && error.status === 401)) {
            sessionStorage.removeItem("mintcore-token");
            token = "";
            relayAuthenticated = false;
            loginOverlay.hidden = false;
            if (RELAY_MODE) setLoginStage("primary");
            loginError.textContent = normalizeFontText(error.message);
          }
        } finally {
          polling = false;
        }
      }

      $("#login-form").addEventListener("submit", async (event) => {
        event.preventDefault();
        const originalButtonText = loginSubmitButton.textContent;
        loginSubmitButton.disabled = true;
        loginSubmitButton.textContent = "LOGOWANIE...";
        loginError.textContent = "";
        try {
          if (RELAY_MODE && loginStage === "primary") {
            const password = tokenInput.value;
            await postRelayAction("login", { password });
            setLoginStage("second-factor");
            return;
          }
          if (RELAY_MODE) {
            return;
          } else {
            token = tokenInput.value.trim();
            await api("/api/state");
            sessionStorage.setItem("mintcore-token", token);
          }
          loginError.textContent = "";
          loginOverlay.hidden = true;
          await refresh();
        } catch (error) {
          if (!RELAY_MODE) {
            token = "";
          }
          relayAuthenticated = false;
          loginOverlay.hidden = false;
          loginError.textContent = normalizeFontText(error.message);
          if (RELAY_MODE && loginStage === "second-factor") {
            tokenInput.value = "";
            tokenInput.focus();
          }
        } finally {
          loginSubmitButton.disabled = false;
          loginSubmitButton.textContent = originalButtonText;
        }
      });

      document.addEventListener("keydown", async (event) => {
        const secondFactorStep = loginStage === "second-factor";
        const activationStep = loginStage === "awaiting-activation";
        if (!RELAY_MODE || (!secondFactorStep && !activationStep) || event.key.toLowerCase() !== "m" || event.repeat || event.isComposing || activationPending) {
          return;
        }
        event.preventDefault();
        activationPending = true;
        try {
          if (secondFactorStep) {
            await postRelayAction("second-factor", { password: tokenInput.value });
            setLoginStage("awaiting-activation");
          }
          await postRelayAction("activate");
          await api("/api/state");
          relayAuthenticated = true;
          loginStage = "primary";
          loginOverlay.hidden = true;
          await refresh();
        } catch (error) {
          loginError.textContent = normalizeFontText(error.message);
          if (loginStage === "second-factor") {
            tokenInput.value = "";
            tokenInput.focus();
          }
        } finally {
          activationPending = false;
        }
      });

      $("#command-form").addEventListener("submit", async (event) => {
        event.preventDefault();
        const input = $("#command-input");
        const command = input.value.trim();
        if (!command) return;
        $("#send-button").disabled = true;
        try {
          const result = await api("/api/command", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded;charset=UTF-8" },
            body: new URLSearchParams({ command, player: playerSelect.value })
          });
          showNotice(result.message, !result.ok);
          input.value = "";
          await refresh();
        } catch (error) {
          showNotice(error.message, true);
        } finally {
          $("#send-button").disabled = false;
          input.focus();
        }
      });

      $("#tab-console").addEventListener("click", () => {
        $("#tab-console").classList.add("active");
        $("#tab-checks").classList.remove("active");
        $("#console-panel").style.display = "grid";
        $("#checks-panel").classList.remove("active");
        updateActiveTabIndicator();
      });
      $("#tab-checks").addEventListener("click", () => {
        $("#tab-checks").classList.add("active");
        $("#tab-console").classList.remove("active");
        $("#console-panel").style.display = "none";
        $("#checks-panel").classList.add("active");
        updateActiveTabIndicator();
      });
      function updateActiveTabIndicator() {
        const activeTab = $(".tab.active");
        const tabs = $(".tabs");
        if (!activeTab || !tabs) return;
        tabs.style.setProperty("--tab-active-x", `${activeTab.offsetLeft}px`);
        tabs.style.setProperty("--tab-active-width", `${activeTab.offsetWidth}px`);
      }
      window.addEventListener("resize", updateActiveTabIndicator);
      updateActiveTabIndicator();
      $("#clear-button").addEventListener("click", () => {
        clearedBefore = Date.now();
        logList.replaceChildren();
        const empty = document.createElement("div");
        empty.className = "empty-log";
        empty.textContent = "Widok wyczyszczony. Nowe wpisy pojawią się tutaj.";
        logList.append(empty);
        $("#log-count").textContent = "0 wpisów";
      });
      playerSelect.addEventListener("change", refresh);

      normalizeVisibleText();
      if (RELAY_MODE) {
        setLoginStage("primary");
        $("#footer-security").textContent = "Panel zabezpieczony HTTPS · Chroń swoje hasło.";
        tokenInput.autocomplete = "current-password";
        normalizeVisibleText();
      }
      if (window.location.protocol === "file:") {
        tokenInput.disabled = true;
        $("#login-form button[type=submit]").disabled = true;
        localFileHelp.hidden = false;
      }
      if (token) {
        if (window.location.protocol !== "file:") {
          tokenInput.value = token;
          $("#login-form").requestSubmit();
        }
      } else if (RELAY_MODE && window.location.protocol !== "file:") {
        api("/api/state").then(() => {
          relayAuthenticated = true;
          loginOverlay.hidden = true;
          refresh();
        }).catch((error) => {
          if (error.status !== 401) {
            loginError.textContent = normalizeFontText(error.message);
          }
        });
      }
      window.setInterval(refresh, 1200);
    </script>
  </body>
</html>
