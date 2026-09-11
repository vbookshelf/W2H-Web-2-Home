<?php
// ============================================================
// control-panel.php
//
// Remote Control Panel: lets the user directly toggle connected
// hardware (starting with one LED) without going through chat at
// all. See the "Remote Control Panel" note near the top of
// poller.py for the full design, and the set_led/get_state/
// report_state section in api.php for the backend side of this.
//
// Deliberately self-contained, same as about.php: its own copy of the
// palette and base styles rather than a shared include, so it has no
// dependency on index.php or about.php and can't be broken by future
// changes to either (other than the theme mode itself - see below).
// If you re-theme those pages later, update the :root/.dark-mode
// values below to match.
//
// Theme mode (light/dark) IS deliberately tied to index.php: this
// page has no theme toggle of its own and instead reads the shared
// 'theme' key from localStorage (set by index.php's toggle) to decide
// whether to add the .dark-mode class to <body>. Light mode is the
// default on both pages. See the theme-sync script near the bottom
// of this file.
//
// State model, in short:
//   - Clicking the button POSTs set_led (a desired change) and
//     immediately shows a "pending" look - it does NOT flip the
//     button to on/off right away, since that would be showing intent
//     as if it were fact.
//   - The button only shows on/off once get_state confirms it - i.e.
//     once the Mac has actually driven the Arduino and reported back.
//   - This page polls get_state every few seconds so it stays correct
//     even if the LED was changed via chat while this page is open.
// ============================================================
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Control Panel</title>
    <meta name="robots" content="noindex, nofollow">

    <link rel="shortcut icon" type="image/png" href="assets/ebot3.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        :root {
            --bg: #f5f7fa;
            --bg2: #ffffff;
            --bg3: #eef1f6;
            --border: rgba(0, 0, 0, 0.08);
            --border2: rgba(0, 0, 0, 0.15);
            --text: #1a1d24;
            --text2: #5c6275;
            --text3: #8a91a5;
            --accent: #2563eb;
            --accent-dim: rgba(37, 99, 235, 0.08);
            --accent-glow: rgba(37, 99, 235, 0.18);
            --overlay-bg: rgba(245, 247, 250, 0.97);
            --on-color: #16a34a;
            --on-dim: rgba(22, 163, 74, 0.10);
            --on-glow: rgba(22, 163, 74, 0.22);
            --error: #e5484d;
        }

        /* Dark palette is applied via the .dark-mode class on <body>
           (kept in sync with index.php's theme - see the script near
           the bottom of this file) rather than the OS-level
           prefers-color-scheme media query, so this page always
           matches whatever theme was last chosen on index.php instead
           of independently following the OS setting. Light mode (no
           class) is the default here, same as index.php. */
        body.dark-mode {
            --bg: #0d0f14; --bg2: #141720; --bg3: #1c2030;
            --border: rgba(255,255,255,0.08); --border2: rgba(255,255,255,0.14);
            --text: #e8eaf0; --text2: #8b90a4; --text3: #555b70;
            --accent: #5b8cff; --accent-dim: rgba(91,140,255,0.12); --accent-glow: rgba(91,140,255,0.25);
            --overlay-bg: rgba(13, 15, 20, 0.97);
            --on-color: #34d399; --on-dim: rgba(52, 211, 153, 0.12); --on-glow: rgba(52, 211, 153, 0.28);
        }

        * { box-sizing: border-box; }

        body {
            background-color: var(--bg);
            color: var(--text);
            font-family: Helvetica, Arial, sans-serif;
            font-size: 18px;
            margin: 0;
            padding: 50px 20px 80px;
        }

        .container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .hero {
            text-align: center;
            margin-bottom: 34px;
        }

        .hero-image {
            width: 100%;
            height: auto;
            max-width: 200px;
            margin: 0 auto 4px auto;
            display: block;
        }

        h1 {
            letter-spacing: -0.02em;
            font-size: clamp(2rem, 8vw, 2.6rem);
            margin: 0 0 4px 0;
        }

        .hero-subtitle {
            color: var(--text2);
            font-weight: normal;
            font-size: 17px;
            margin: 0;
        }

        h2 {
            font-size: 20px;
            margin: 36px 0 12px 0;
            letter-spacing: -0.01em;
        }

        p {
            line-height: 1.7;
            color: var(--text);
            margin: 0 0 14px 0;
        }

        a {
            color: var(--accent);
            text-decoration: none;
        }

        .card {
            background-color: var(--bg2);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 24px 20px;
        }

        /* ── Device row ─────────────────────────────────────────── */

        .device-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .device-row + .device-row {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .device-info {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .device-icon {
            width: 42px;
            height: 42px;
            border-radius: 10px;
            background-color: var(--bg3);
            color: var(--text2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .device-icon.is-on {
            background-color: var(--on-dim);
            color: var(--on-color);
        }

        .device-name {
            font-weight: bold;
            font-size: 17px;
            margin: 0 0 2px 0;
        }

        .device-status {
            font-size: 14px;
            color: var(--text2);
            margin: 0;
        }

        .device-status.is-on { color: var(--on-color); }
        .device-status.is-pending { color: var(--accent); }
        .device-status.is-error { color: var(--error); }

        /* ── Power button ───────────────────────────────────────── */
        /* Deliberately has no on/off color state of its own - unlike
           .device-icon.is-on and .device-status.is-on above, this
           button always looks the same regardless of confirmed LED
           state. Only the icon and status text communicate on/off;
           the button communicates only "press to send a command" and
           (via .is-pending) "a command is in flight". */

        .power-btn {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            border: 2px solid var(--border2);
            background-color: var(--bg3);
            color: var(--text2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
            flex-shrink: 0;
            padding: 0;
            transition: opacity 0.2s ease, border-color 0.15s ease, color 0.15s ease, transform 0.1s ease;
        }

        .power-btn:hover:not(:disabled) {
            border-color: var(--accent);
            color: var(--accent);
        }

        .power-btn:active:not(:disabled) {
            transform: scale(0.94);
        }

        .power-btn.is-pending {
            opacity: 0.55;
            cursor: wait;
        }

        .power-btn:disabled {
            cursor: wait;
        }

        .panel-error {
            color: var(--error);
            font-size: 14px;
            margin-top: 16px;
            text-align: center;
            min-height: 18px;
        }

        .back-link-wrap {
            text-align: center;
            margin-top: 44px;
        }

        .back-link {
            display: inline-block;
            background-color: var(--accent);
            color: #fff;
            text-decoration: none;
            padding: 12px 26px;
            border-radius: 8px;
            font-size: 16px;
        }

        .back-link:hover {
            opacity: 0.92;
        }

        .top-back-link {
            display: inline-block;
            color: var(--text2);
            text-decoration: none;
            font-size: 14px;
            margin-bottom: 26px;
        }

        .top-back-link:hover {
            color: var(--accent);
        }

        /* ── Token gate (copied from index.php's pattern) ──────────── */

        .token-gate-overlay {
            position: fixed;
            inset: 0;
            z-index: 700;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 14px;
            background-color: var(--overlay-bg);
            color: var(--text);
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease, background-color 0.3s ease;
            padding: 20px;
            box-sizing: border-box;
            text-align: center;
        }

        .token-gate-overlay.open {
            opacity: 1;
            visibility: visible;
        }

        .token-gate-overlay p {
            margin: 0;
            color: var(--text2);
            max-width: 320px;
        }

        .token-gate-overlay input[type="text"] {
            box-sizing: border-box;
            height: 46px;
            padding: 0 15px;
            border-radius: 5px;
            border: 1px solid var(--border2);
            background-color: var(--bg3);
            color: var(--text);
            font-size: 18px;
            width: 100%;
            max-width: 320px;
        }

        .token-gate-overlay button {
            height: 46px;
            padding: 0 24px;
            border-radius: 5px;
            border: 1px solid transparent;
            background-color: var(--accent);
            color: #fff;
            font-size: 16px;
            cursor: pointer;
        }

        .token-gate-error {
            color: var(--error);
            font-size: 14px;
            min-height: 18px;
        }

        footer {
            text-align: center;
            color: var(--text3);
            font-size: 13px;
            margin-top: 50px;
        }
    </style>
</head>
<body>

<!-- Access-token gate. Same pattern as index.php: blocks the panel
     behind it until a valid token is stored, and reuses the same
     localStorage key, so anyone already signed into the chat is
     already signed into this page too. -->
<div class="token-gate-overlay open" id="tokenGate" role="dialog" aria-modal="true" aria-label="Enter access token">
    <p style="font-size:17px; color:var(--text);">Enter your access token to continue</p>
    <input type="text" id="tokenInput" placeholder="Access token" autocomplete="off" aria-label="Access token">
    <button type="button" id="tokenSubmitBtn">Continue</button>
    <p class="token-gate-error" id="tokenError" style="display:none;">Invalid token, try again.</p>
</div>

<div class="container">

    <a href="index.php" class="top-back-link">Back to W2H</a>

    <div class="hero">
        <img src="assets/ebot2.png" alt="E-Bot" class="hero-image" loading="eager">
        <h1><b>Control Panel</b></h1>
        <p class="hero-subtitle">Remote hardware control</p>
    </div>

    <div class="card">
        <div class="device-row">
            <div class="device-info">
                <div class="device-icon" id="ledIcon">
                    <i class="fa fa-lightbulb-o" aria-hidden="true"></i>
                </div>
                <div>
                    <p class="device-name">LED</p>
                    <p class="device-status" id="ledStatusText">Checking...</p>
                </div>
            </div>
            <button type="button" class="power-btn" id="ledToggle" aria-pressed="false" aria-label="Toggle LED power" disabled>
                <i class="fa fa-power-off" aria-hidden="true"></i>
            </button>
        </div>
    </div>

    <p class="panel-error" id="panelError"></p>

    <div class="back-link-wrap">
        <a href="index.php" class="back-link">Back to W2H</a>
    </div>

</div>

<script>
    // ============================================================
    // Theme sync with index.php.
    // Same idea as index.php's own theme handling: light mode is the
    // default (no class on <body>), dark mode is only applied when
    // 'theme' === 'dark' in localStorage. This page has no toggle of
    // its own - it just mirrors whatever was last chosen on index.php,
    // via the same shared localStorage key, so the two pages can never
    // show different modes.
    // ============================================================
    const THEME_STORAGE_KEY = 'theme';

    function applyTheme() {
        const savedTheme = localStorage.getItem(THEME_STORAGE_KEY);
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
        } else {
            document.body.classList.remove('dark-mode');
        }
    }

    // Apply as soon as the DOM is parsed, same timing as index.php,
    // so there's no flash of the wrong theme while other resources
    // (e.g. the hero image) are still loading.
    document.addEventListener('DOMContentLoaded', applyTheme);

    // If index.php is open in another tab and its theme is changed
    // there, the 'storage' event fires here and keeps this page in
    // sync without needing a reload.
    window.addEventListener('storage', function(e) {
        if (e.key === THEME_STORAGE_KEY) {
            applyTheme();
        }
    });

    // ============================================================
    // Access-token gate + authFetch wrapper for api.php.
    // Copied from index.php's version - same storage key, so a token
    // entered on either page works on both. See that file for the
    // fuller explanatory comments.
    // ============================================================
    const API_BASE = "api.php";
    const TOKEN_STORAGE_KEY = 'w2h_access_token';

    const tokenGateEl = document.getElementById('tokenGate');
    const tokenInputEl = document.getElementById('tokenInput');
    const tokenSubmitBtn = document.getElementById('tokenSubmitBtn');
    const tokenErrorEl = document.getElementById('tokenError');

    async function authFetch(url, options = {}) {
        const token = localStorage.getItem(TOKEN_STORAGE_KEY) || '';
        const headers = Object.assign({}, options.headers, {
            'X-Access-Token': token
        });
        const res = await fetch(url, Object.assign({}, options, { headers }));

        if (res.status === 401) {
            // Diagnostic only - see the matching comment in index.php.
            // This page polls get_state every few seconds, so if
            // something is going to spuriously 401 and wipe the shared
            // token, it's most likely to happen here first - this log
            // makes that visible instead of only noticing it later on
            // index.php after the token is already gone.
            let body = '';
            try { body = await res.clone().text(); } catch (e) { /* ignore */ }
            console.warn(
                `[token-gate] Got 401 from ${url} - clearing saved token. ` +
                `Response: ${body}`
            );
            resetToTokenGate();
        }

        return res;
    }

    async function tryToken(token) {
        const res = await fetch(`${API_BASE}?action=status`, {
            headers: { 'X-Access-Token': token },
            cache: 'no-store'
        });
        return res.ok;
    }

    function openTokenGate() {
        tokenGateEl.classList.add('open');
    }

    function closeTokenGate() {
        tokenGateEl.classList.remove('open');
    }

    function resetToTokenGate() {
        localStorage.removeItem(TOKEN_STORAGE_KEY);
        openTokenGate();
        tokenInputEl.value = '';
        tokenErrorEl.style.display = 'none';
    }

    // Trusts a saved token immediately rather than re-validating it
    // against the server on every page load - see the matching comment
    // in index.php for why: a network hiccup during that extra
    // round-trip used to look exactly like a revoked token and would
    // wipe a perfectly good one, forcing re-entry just from navigating
    // here and back via a plain link. A genuinely revoked token is
    // instead caught by authFetch's 401 handler the next time a real
    // request (e.g. the get_state poll) actually goes out.
    function initTokenGate() {
        const savedToken = localStorage.getItem(TOKEN_STORAGE_KEY);

        if (savedToken) {
            closeTokenGate();
            startPanel();
            return;
        }

        console.warn('[token-gate] No saved token found at page load - showing gate.');
        openTokenGate();
    }

    tokenSubmitBtn.addEventListener('click', async () => {
        const token = tokenInputEl.value.trim();
        if (!token) return;

        const valid = await tryToken(token);
        if (!valid) {
            tokenErrorEl.style.display = 'block';
            return;
        }

        localStorage.setItem(TOKEN_STORAGE_KEY, token);
        tokenErrorEl.style.display = 'none';
        closeTokenGate();
        startPanel();
    });

    tokenInputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') tokenSubmitBtn.click();
    });

    // ============================================================
    // LED control.
    //
    // State model:
    //   confirmedState - the last value get_state actually returned
    //     ("on" / "off" / null-ish "unknown"). This is what the icon,
    //     label, and switch position are drawn from.
    //   pending - true from the moment the user clicks until get_state
    //     actually reports pendingTarget (the value we asked for) or a
    //     failure ("unknown") - NOT just "the next get_state response",
    //     since that response could still be the pre-command value if
    //     the Mac hasn't driven the hardware yet. See refreshState().
    //   pendingTarget - "on"/"off": the value a pending command is
    //     waiting to see confirmed. null whenever pending is false.
    //     Shown as a distinct visual state so a click never looks like
    //     it already succeeded before it actually has.
    //
    // GET_STATE_POLL_MS controls how often this page re-checks state
    // on its own, so a change made via chat (LLM tool call) while this
    // page is sitting open still shows up here without a refresh. It
    // also determines how quickly a pending command's confirmation is
    // noticed, since that's checked on the same poll.
    // ============================================================
    const GET_STATE_POLL_MS = 3000;

    const ledToggleEl = document.getElementById('ledToggle');
    const ledIconEl = document.getElementById('ledIcon');
    const ledStatusTextEl = document.getElementById('ledStatusText');
    const panelErrorEl = document.getElementById('panelError');

    let confirmedLedState = null; // "on" | "off" | null (unknown)
    let pending = false;
    let pendingTarget = null; // "on" | "off" - value a pending command is waiting to see confirmed
    let pollHandle = null;

    function renderLed() {
        const isOn = confirmedLedState === 'on';

        // Deliberately no ledToggleEl.classList.toggle('is-on', ...) here -
        // the power button itself always looks the same; only the bulb
        // icon and status text below change color with the LED state.
        ledToggleEl.classList.toggle('is-pending', pending);
        ledToggleEl.setAttribute('aria-pressed', String(isOn));
        ledToggleEl.disabled = pending;

        ledIconEl.classList.toggle('is-on', isOn && !pending);

        ledStatusTextEl.classList.remove('is-on', 'is-pending', 'is-error');

        if (pending) {
            ledStatusTextEl.textContent = 'Sending command...';
            ledStatusTextEl.classList.add('is-pending');
        } else if (confirmedLedState === 'on') {
            ledStatusTextEl.textContent = 'On';
            ledStatusTextEl.classList.add('is-on');
        } else if (confirmedLedState === 'off') {
            ledStatusTextEl.textContent = 'Off';
        } else if (confirmedLedState === 'unknown') {
            ledStatusTextEl.textContent = 'Unknown - last command may have failed';
            ledStatusTextEl.classList.add('is-error');
        } else {
            ledStatusTextEl.textContent = 'Checking...';
        }
    }

    async function refreshState() {
        try {
            const res = await authFetch(`${API_BASE}?action=get_state`, { cache: 'no-store' });
            if (!res.ok) return;
            const data = await res.json();
            const reportedState = data.led ?? 'unknown';

            if (pending) {
                // Don't clear "pending" just because a get_state response
                // came back - it may still be reporting the pre-command
                // value if the Mac hasn't driven the hardware yet. Only
                // treat it as confirmation once it actually reports the
                // value we asked for, or reports "unknown" (the Mac tried
                // and failed - also a confirmation, just not the one we
                // wanted, so there's no reason to keep waiting for a
                // value that will never arrive).
                if (reportedState === pendingTarget || reportedState === 'unknown') {
                    confirmedLedState = reportedState;
                    pending = false;
                    pendingTarget = null;
                }
                // else: still the old value - keep "Sending command..."
                // showing and let the next poll tick check again.
            } else {
                confirmedLedState = reportedState;
            }

            panelErrorEl.textContent = '';
            renderLed();
        } catch (e) {
            panelErrorEl.textContent = 'Could not reach the server. Retrying...';
        }
    }

    async function toggleLed() {
        if (pending) return;

        const nextValue = confirmedLedState === 'on' ? 'off' : 'on';
        pending = true;
        pendingTarget = nextValue;
        renderLed();

        try {
            const res = await authFetch(`${API_BASE}?action=set_led`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `value=${encodeURIComponent(nextValue)}`
            });
            if (!res.ok) {
                throw new Error('set_led request failed');
            }
        } catch (e) {
            pending = false;
            pendingTarget = null;
            panelErrorEl.textContent = 'Could not send the command. Please try again.';
            renderLed();
            return;
        }

        // Command is now queued server-side; wait for the Mac's poll
        // loop to actually drive the hardware and report back, rather
        // than guessing when that happens. refreshState() on each
        // regular poll tick will keep checking until get_state reports
        // pendingTarget (or a failure), and only then clear "pending" -
        // see the setInterval below.
    }

    ledToggleEl.addEventListener('click', toggleLed);

    function startPanel() {
        renderLed();
        refreshState();
        if (!pollHandle) {
            pollHandle = setInterval(refreshState, GET_STATE_POLL_MS);
        }
    }

    initTokenGate();
</script>

</body>
</html>
