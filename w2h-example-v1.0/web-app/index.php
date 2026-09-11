<?php
session_start();

// ============================================================
// This file used to include php/name_config.php separately.
// Those values are now set directly here as part of the
// codebase consolidation into just index.php and main.php.
// ============================================================
$bot_name = 'Polly'; 	// Give the bot a name
$user_name = 'Guest';	// Set the user's name
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Tell search engines not to index the site -->
    <meta name="robots" content="noindex, nofollow">
  
    <meta charset="utf-8">
    <title>W2H - Home Server Web Interface</title>
    <meta name="description" content="W2H is a self hosted web based chat and voice gateway to your home server.">
	
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Image -->
    <link rel="shortcut icon" type="image/png" href="assets/ebot3.png">
	
    <!-- Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <!-- Markdown rendering for the bot's replies (see applyBotResponse
         below): marked.js converts markdown -> HTML, DOMPurify sanitizes
         it before it's ever set via innerHTML. Loaded blocking, in
         <head>, so both are guaranteed to exist by the time any script
         further down the page runs. -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/marked/12.0.2/marked.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dompurify/3.1.5/purify.min.js"></script>

    <style>
        /* ============================================================
           Default Theme Palette: Light Mode
           ============================================================ */
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
            --pill-bg: rgba(255, 255, 255, 0.92);
            --overlay-bg: rgba(245, 247, 250, 0.97);
        }

        /* ============================================================
           Dark Theme Palette Override: Near-black Navy (adopted from
           the Noise for Sleep app: two lighter "surface" layers for
           cards/panels rather than one flat background color
           everywhere, soft off-white text with two dimmer secondary
           shades, and a blue accent).
           ============================================================ */
        body.dark-mode {
        	--bg: #0d0f14; --bg2: #141720; --bg3: #1c2030;
        	--border: rgba(255,255,255,0.08); --border2: rgba(255,255,255,0.14);
        	--text: #e8eaf0; --text2: #8b90a4; --text3: #555b70;
        	--accent: #5b8cff; --accent-dim: rgba(91,140,255,0.12); --accent-glow: rgba(91,140,255,0.25);
        	--pill-bg: rgba(20, 23, 32, 0.92);
        	--overlay-bg: rgba(13, 15, 20, 0.97);
        }

        /* ============================================================
           Custom replacements for the w3.css utility classes that
           were previously used (w3.css has been dropped entirely).
           ============================================================ */
        .w3-small { font-size: 12px; }
        .w3-padding-left { padding-left: 8px; }
        .w3-padding-right { padding-right: 8px; }
        .w3-padding-bottom { padding-bottom: 8px; }
        .w3-padding { padding: 8px; }
        .w3-text-white { color: var(--text); }
        .w3-text-blue { color: var(--accent); }
        .w3-text-teal { color: var(--accent); }
        .w3-center { text-align: center; }
        .w3-round { border-radius: 4px; }
        .w3-animate-opacity { animation: w3-fade-in 0.5s; }
        @keyframes w3-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* ============================================================
           App styles (formerly css/e-bot.css)
           ============================================================ */
        body {
        	background-color: var(--bg);
        	font-family: Helvetica, Arial, sans-serif;
        	font-size: 18px;
        	color: var(--text);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        main {
        	margin-bottom: 200px;
        	color: var(--text);
        	padding: 10px;
        }

        h4 {
        	font-size: 20px;
        }

        #main-image h2 {
        	color: var(--text);
        	letter-spacing: -0.02em;
        	font-size: clamp(2.4rem, 9vw, 3.2rem);
        	margin: 0 0 2px 0;
        }

        .hero-subtitle {
        	color: var(--text2);
        	font-weight: normal;
        	font-size: 18px;
        	margin: 0;
        }

        #about-link {
        	display: inline-block;
        	color: var(--text3);
        	font-size: 13px;
        	margin-top: 6px;
        	text-decoration: none;
        }

        #about-link:hover,
        #about-link:focus-visible {
        	color: var(--accent);
        	outline: none;
        }

        a {
        	text-decoration: none;
        }

        .responsive {
        	 width: 100%; /*Makes media scalable as the viewport size changes*/
        	 height: auto;
        	 max-width: 200px; 
        } 

        .hero-image {
        	width: 100%;
        	height: auto;
        	max-width: 280px;
        	margin: 0 auto 4px auto;
        	display: block;
        }

        .container {
        	width: 100%;
        	max-width: 600px;
        	margin: 0 auto;
        	padding: 0 20px;
        }

        .sticky-bar {
        	position: fixed;
        	bottom: 0;
        	left: 0;
        	width: 100%;
            box-sizing: border-box; /* Fix content-box overflow clipping on mobile */
        	background-color: var(--bg);
        	color: var(--text);
        	padding: 10px 0; /* Align top/bottom, horizontal spacing is handled by input-group */
        	text-align: center;
        	border-top: 1px solid transparent;
            transition: background-color 0.3s ease, border-top-color 0.2s ease;
        }

        /* Thin top border on the sticky area while the Settings panel
           is open, so the bar visually separates itself from the chat
           above it. Disappears again once the panel is closed. */
        .sticky-bar.settings-open {
        	border-top-color: var(--border2);
        }

        .input-group {
            display: flex;
            align-items: center;
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            padding: 0 20px; /* Matches default container margins on desktop */
            box-sizing: border-box;
        }

        .sticky-bar input[type="text"] {
        	box-sizing: border-box;
        	height: 46px;
        	padding: 0 15px;
        	border-radius: 5px;
        	border: 1px solid var(--border2);
        	background-color: var(--bg3);
        	color: var(--text);
        	flex: 1;
        	min-width: 0;
        	font-size: 18px;
        }

        .sticky-bar input[type="submit"] {
        	box-sizing: border-box;
        	height: 46px;
        	background-color: var(--accent);
        	color: #fff;
        	border: 1px solid transparent;
        	padding: 0 20px;
        	border-radius: 5px;
        	cursor: pointer;
        	font-size: 16px;
        	margin-left: 10px;
        	flex-shrink: 0;
        }

        .message-container {
			/*font-family: Menlo, Courier New, monospace;*/
			font-size: 16px;
        	margin-bottom: 10px;
        	padding: 5px 20px;
        	background-color: var(--bg2);
        	color: var(--text);
        	border: 1px solid var(--border);
        	border-radius: 5px;
        	line-height: 2.0; /*1.8*/
        	letter-spacing: 0.02em;
            transition: background-color 0.3s ease, border-color 0.3s ease;
        }

        /* Small, muted line under a bot reply showing how much of the
           model's context window this turn used (see applyBotResponse's
           contextCaption). Deliberately outside .bot-markdown so it's
           never picked up by the "click to speak" handler and never
           read aloud by TTS. */
        .context-caption {
            font-size: 11px;
            line-height: 1.4;
            color: var(--text3);
            margin-top: 2px;
        }

        /* --- Image attach button, pending-image preview, and
               sent-image thumbnails inside chat bubbles --- */

        #image-picker-label {
        	display: flex;
        	align-items: center;
        	justify-content: center;
        	width: 46px;
        	height: 46px;
        	flex-shrink: 0;
        	margin-right: 10px;
        	border-radius: 5px;
        	border: 1px solid var(--border2);
        	background-color: var(--bg3);
        	color: var(--text);
        	cursor: pointer;
        }

        /* --- Suggested prompt chips, shown above the input until the
               first message is sent --- */

        .suggested-prompts {
        	display: flex;
        	flex-direction: column;
        	gap: 4px;
        	width: 100%;
        	max-width: 600px;
        	margin: 0 auto 10px auto;
        	padding: 0 20px;
        	box-sizing: border-box;
        }

        .suggested-prompt-btn {
        	box-sizing: border-box;
        	background: none;
        	border: none;
        	padding: 2px 0;
        	color: var(--text2);
        	font-size: 13px;
        	text-align: left;
        	white-space: nowrap;
        	overflow: hidden;
        	text-overflow: ellipsis;
        	cursor: pointer;
        }

        .suggested-prompt-btn:hover {
        	color: var(--accent);
        }

        .image-preview-row {
        	display: none;
        	align-items: center;
        	gap: 10px;
        	width: 100%;
        	max-width: 600px;
        	margin: 0 auto 8px auto;
        	padding: 0 20px;
        	box-sizing: border-box;
        }

        .image-preview-row img {
        	height: 56px;
        	width: 56px;
        	object-fit: cover;
        	border-radius: 6px;
        	border: 1px solid var(--border2);
        }

        .image-preview-row button {
        	width: 24px;
        	height: 24px;
        	border-radius: 50%;
        	border: 1px solid var(--border2);
        	background-color: var(--bg3);
        	color: var(--text);
        	line-height: 1;
        	font-size: 14px;
        	cursor: pointer;
        }

        .chat-image-thumb {
        	display: block;
        	max-width: 220px;
        	max-height: 220px;
        	border-radius: 6px;
        	margin: 8px 0;
        	object-fit: cover;
        }

        /* --- Drag-and-drop indicator (no full-page overlay) --- */

        #drag-drop-indicator {
        	position: fixed;
        	inset: 0;
        	z-index: 9999;
        	pointer-events: none;
        	border: 3px dashed var(--accent);
        	box-shadow: inset 0 0 40px var(--accent-glow);
        	opacity: 0;
        	transition: opacity 0.15s ease;
        }

        body.page-drag-active #drag-drop-indicator {
        	opacity: 1;
        }

        body.page-drag-active #image-picker-label {
        	border-color: var(--accent);
        	background-color: var(--accent-dim);
        	box-shadow: 0 0 0 3px var(--accent-glow);
        }

        .set-color1 {
        	color: var(--accent);
        }

        .set-color2 {
        	color: var(--text2);
        }

        #chat-buttons {
          display: flex;
          justify-content: center;
          align-items: center;
          margin-top: 10px;
        }

        #chat-buttons button {
          margin-right: 20px;
          padding: 0px 20px;
          border-radius: 5px;
          cursor: pointer;
          font-size: 15px;
          background-color: var(--bg3);
          color: var(--text);
          border: none;
        }

        #chat-buttons input[type="file"] {
          display: none;
        }

        #chat-buttons label {
          display: inline-block;
          padding: 0px 20px;
          border-radius: 5px;
          cursor: pointer;
          font-size: 15px;
          background-color: var(--bg3);
          color: var(--text);
          border: none;
        }
        	
        #chat-buttons input[type="file"] + label {
        	margin-right: 10px;
        }

        #chat-buttons input[type="file"] + label:before {
          	content: "Load a saved chat";
        }

        .sticky-image {
        	position: fixed;
        	top: 0;
        	left: 0;
        }
        	
        .beta-text {
        	font-size: 15px;
        }


        .lighter-black {
        	color: var(--text2); 
        }

        #language-dropdown {
        	margin-top: 10px;
        	font-size: 15px;
        }

        .space-letters {
        	letter-spacing: .03em;
        }
        	
        .wrapper {
          display: flex;
          justify-content: center;
          width: 100%;
          max-width: 600px;
          margin: 3px auto 0 auto;
          padding: 0 20px;
          box-sizing: border-box;
        }

        .form-elements {
          display: flex;
          flex-direction: row;
          justify-content: center;
          align-items: center;
          gap: 10px; /* Space between the radio buttons and dropdown */
          width: 100%;
        }

        .radio-group {
          display: flex;
          flex-direction: row;
          justify-content: flex-start;
          align-items: center;
          gap: 10px; /* Space between the radio buttons */
        }

        .radio-option {
          display: flex;
          align-items: center;
          gap: 8px;
          padding: 8px;
          /*border: 1px solid #ccc;
          border-radius: 4px;*/
          cursor: pointer;
        }

        .radio-option input[type="radio"] {
          margin-right: 0;
          accent-color: var(--accent);
        }

        .dropdown-option {
          display: flex;
          align-items: center;
          flex: 1 1 auto;
          min-width: 0; /* lets the select actually shrink instead of forcing the row wide - this was the root cause of the dropdown ballooning to full width alone on mobile */
        }

        .styled-dropdown {
          width: 100%;
          padding: 8px 30px 8px 10px;
          border: 1px solid var(--border2);
          border-radius: 4px;
          cursor: pointer;
          background-color: var(--bg3);
          color: var(--text);
          appearance: none;
          -webkit-appearance: none;
          -moz-appearance: none;
          background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 8'%3E%3Cpath fill='%238b90a4' d='M1 1l5 5 5-5'/%3E%3C/svg%3E");
          background-repeat: no-repeat;
          background-position: right 10px center;
          background-size: 10px 7px;
        }

        .styled-dropdown:hover,
        .styled-dropdown:focus,
        .styled-dropdown:focus-visible {
          border-color: var(--accent);
          outline: none;
        }

        /* Settings panel rows (Voice, Speed): label on the left, a
           *single* controls group on the right that holds the
           dropdown/slider plus its companion button/value-label. Using
           one wrapper for "everything after the label" means that if
           the row runs out of space, the whole controls group wraps
           together onto its own line - rather than the label, the
           dropdown, and the button each independently deciding where
           to wrap and landing on three separate lines. */
        .settings-row {
          display: flex;
          flex-wrap: wrap;
          align-items: center;
          gap: 10px;
        }

        .settings-row-label {
          display: flex;
          align-items: center;
          flex: 0 0 auto;
          min-width: 60px;
          cursor: pointer;
        }

        .settings-row-controls {
          display: flex;
          align-items: center;
          gap: 8px;
          flex: 1 1 auto;
          min-width: 0;
        }

        .title-color {
        	color: var(--accent);
        }

        .tag-color {
        	background-color: var(--bg3);
        }

        .hide {
            display: none;
        }

        /* ============================================================
           Voice picker row (Settings panel): dropdown of available
           TTS voices for English, plus a small preview button so the
           user can hear a voice before committing to it.
           ============================================================ */
        #preview-voice-btn {
        	display: flex;
        	align-items: center;
        	justify-content: center;
        	width: 34px;
        	height: 34px;
        	flex-shrink: 0;
        	border-radius: 4px;
        	border: 1px solid var(--border2);
        	background-color: var(--bg3);
        	color: var(--text);
        	cursor: pointer;
        }

        #preview-voice-btn:hover {
        	border-color: var(--accent);
        	color: var(--accent);
        }

        #preview-voice-btn:disabled {
        	opacity: 0.4;
        	cursor: not-allowed;
        }

        /* ============================================================
           Speed picker row (Settings panel): a range slider so users
           can slow speech down for practice or speed it back up.
           ============================================================ */
        #speed-select {
        	flex: 1;
        	min-width: 100px;
        	accent-color: var(--accent);
        	cursor: pointer;
        }

        #speed-value-label {
        	min-width: 34px;
        	text-align: right;
        	font-size: 13px;
        	color: var(--text);
        	font-variant-numeric: tabular-nums;
        }

        /* This <select> stays in the DOM and keeps working exactly as
           before (same id, same form field name) for the Translation
           toggle - it's just not shown, since there's no visible
           language-picker UI anymore. */
        .visually-hidden-select {
        	position: absolute;
        	width: 1px;
        	height: 1px;
        	overflow: hidden;
        	clip: rect(0 0 0 0);
        	white-space: nowrap;
        }

        /* #line1 used to have its own display:block rule here too, but
           since it also carries the .radio-group class (display:flex),
           the ID selector's higher specificity was silently winning and
           stacking the toggles vertically instead of in a row. Only
           #line2 (the visually-hidden language dropdown's wrapper)
           actually needs to stay block. */
        #line2 {
        	display: block;
        }

        #line1 {
        	flex-wrap: wrap;
        }

        .display-block {
        	display: block;
        }

        /* Initially hide the panel and set up the transition */
        #panel {
          max-height: 0;
          overflow: hidden;
          transition: max-height 0.5s ease-out;
          width: 100%;
        }

        /* Class to show the panel */
        .panel-open {
          max-height: 1000px; /* Set a high value for max-height */
        }

        /* Card styling lives on this inner wrapper rather than on #panel
           itself: #panel is the thing whose max-height animates for the
           open/close transition, and padding placed directly on an
           element with max-height:0 still renders (padding isn't part
           of what max-height constrains), leaving a visible sliver even
           while "closed". Keeping padding/background one level in avoids
           that. */
        #panel-inner {
          display: flex;
          flex-direction: column;
          gap: 10px;
          margin-top: 10px;
          padding: 14px 16px;
          background-color: var(--bg2);
          border: 1px solid var(--border);
          border-radius: 10px;
        }

        #accordion {
          margin-right: 20px;
          padding: 8px 14px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 18px;
          background-color: transparent;
          color: var(--text);
          border: none;
          transition: background-color 0.15s ease, color 0.15s ease;
        }

        #accordion:hover,
        #accordion:focus,
        #accordion:focus-visible {
          background-color: var(--bg3);
          outline: none;
          box-shadow: none;
        }

        /* Settings button stays visibly "active" while its panel is
           open, so the gear affordance actually signals open/closed
           state instead of looking the same either way (no blue
           tint - just a neutral background, matching the button's
           hover state). */
        #accordion.settings-active {
          background-color: var(--bg3);
        }

        #start-voicechat-btn {
          margin-right: 10px;
          padding: 8px 14px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 18px;
          background-color: transparent;
          color: var(--text);
          border: none;
          transition: background-color 0.15s ease;
        }

        #start-voicechat-btn:hover,
        #start-voicechat-btn:focus,
        #start-voicechat-btn:focus-visible {
          background-color: var(--bg3);
          outline: none;
          box-shadow: none;
        }

        /* Upload-to-workspace button: silent action (no chat message,
           no agent notification), so its only feedback is a brief
           spinner/check/error swap on the icon itself - see the
           uploadWorkspaceFile script below. */
        #upload-file-btn {
          margin-right: 10px;
          padding: 8px 14px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 18px;
          background-color: transparent;
          color: var(--text);
          border: none;
          transition: background-color 0.15s ease;
        }

        #upload-file-btn:hover,
        #upload-file-btn:focus,
        #upload-file-btn:focus-visible {
          background-color: var(--bg3);
          outline: none;
          box-shadow: none;
        }

        #upload-file-btn.upload-success i {
          color: #2e7d32;
        }

        #upload-file-btn.upload-error i {
          color: #c62828;
        }

        #practice-plan-btn {
          margin-right: 10px;
          padding: 8px 14px;
          border-radius: 8px;
          cursor: pointer;
          font-size: 18px;
          background-color: transparent;
          color: var(--text);
          border: none;
          transition: background-color 0.15s ease;
        }

        #practice-plan-btn:hover,
        #practice-plan-btn:focus,
        #practice-plan-btn:focus-visible {
          background-color: var(--bg3);
          outline: none;
          box-shadow: none;
        }

        /* ============================================================
           7-Day Practice Plan overlay.
           Same full-screen dialog pattern as .language-overlay: fixed,
           translucent, blurred backdrop, fades in/out via .open,
           fixed circular close button top-right.
           ============================================================ */
        .plan-overlay {
        	position: fixed;
        	top: 0;
        	left: 0;
        	width: 100%;
        	height: 100%;
        	background-color: var(--overlay-bg);
        	backdrop-filter: blur(5px);
        	-webkit-backdrop-filter: blur(5px);
        	z-index: 600;
        	display: flex;
        	flex-direction: column;
        	align-items: center;
        	opacity: 0;
        	visibility: hidden;
        	transition: opacity 0.3s ease, visibility 0.3s ease, background-color 0.3s ease;
        	padding: 76px 20px 40px;
        	overflow-y: auto;
        	box-sizing: border-box;
        }

        .plan-overlay.open {
        	opacity: 1;
        	visibility: visible;
        }

        .close-plan-overlay {
        	position: fixed;
        	top: 16px;
        	right: 16px;
        	width: 48px;
        	height: 48px;
        	border-radius: 50%;
        	border: 1px solid var(--border2);
        	background: var(--overlay-bg);
        	color: var(--text);
        	font-size: 22px;
        	cursor: pointer;
        	z-index: 601;
        	transition: background-color 0.3s ease, color 0.3s ease;
        }

        .close-plan-overlay:hover,
        .close-plan-overlay:focus-visible {
        	border-color: var(--accent);
        	color: var(--accent);
        	outline: none;
        }

        .plan-overlay-subtitle {
        	color: var(--text2);
        	font-size: 15px;
        	margin: 0 0 18px 0;
        	text-align: center;
        	max-width: 380px;
        }

        #plan-list {
        	list-style: none;
        	margin: 0;
        	padding: 0;
        	width: 100%;
        	max-width: 480px;
        	display: flex;
        	flex-direction: column;
        	gap: 10px;
        }

        .plan-item {
        	display: flex;
        	align-items: center;
        	gap: 14px;
        	padding: 12px 14px;
        	background-color: var(--bg2);
        	border: 1px solid var(--border);
        	border-radius: 10px;
        	transition: background-color 0.25s ease, border-color 0.25s ease;
        }

        .plan-prompt {
        	flex: 1;
        	display: flex;
        	flex-direction: column;
        	align-items: flex-start;
        	gap: 3px;
        	background: none;
        	border: none;
        	text-align: left;
        	font-family: Helvetica, Arial, sans-serif;
        	cursor: pointer;
        	padding: 4px 0;
        }

        .plan-prompt:hover .command-line {
        	color: var(--accent);
        }

        /* The slash command itself - e.g. "/reset" - on its own line. */
        .command-line {
        	font-size: 16px;
        	line-height: 1.4;
        	color: var(--text);
        	font-weight: 600;
        	font-family: ui-monospace, Menlo, Consolas, monospace;
        }

        /* What the command does, shown underneath in smaller, lighter
           text so the command name itself reads as the primary label. */
        .command-description {
        	font-size: 13px;
        	line-height: 1.4;
        	color: var(--text3);
        	font-weight: normal;
        	font-family: Helvetica, Arial, sans-serif;
        }

        /* Numbered circle badge to the left of each command, same
           visual language as the practice-plan's numbered checkboxes. */
        .command-number {
        	flex-shrink: 0;
        	width: 28px;
        	height: 28px;
        	border-radius: 50%;
        	border: 1.5px solid var(--border2);
        	background-color: var(--bg3);
        	color: var(--text2);
        	font-size: 13px;
        	font-weight: 600;
        	display: flex;
        	align-items: center;
        	justify-content: center;
        }

        .instruction-text {
        	font-size: 17px;
        }

        #audioIndicator {
          bottom: 20px;
          right: 20px;
        }

        .bar {
          width: 4px;
          height: 15px;
          background-color: var(--accent);
          display: inline-block;
          margin-right: 2px;
          animation: soundWave 1s infinite alternate;
        }

        @keyframes soundWave {
          0% { height: 5px; }
          50% { height: 20px; }
          100% { height: 5px; }
        }

        #audioIndicator1 {
          bottom: 20px;
          right: 20px;
        }

        .bar1 {
          width: 4px;
          height: 15px;
          background-color: var(--text2);
          display: inline-block;
          margin-right: 2px;
          
        }

        .clickable {
          cursor: pointer;
        }

        /* ============================================================
           Rendered markdown inside a bot reply (see applyBotResponse).
           This wraps arbitrary block content (headings, lists, code,
           etc.) from marked.js, so it's a <div> rather than a <p> -
           these rules just rein in default browser spacing/sizing so
           it reads like a chat bubble rather than a rendered document.
           ============================================================ */
	   .bot-markdown {
    	font-size: inherit;
    	line-height: 1.45;
    	margin-top: 1.1em;
    	margin-bottom: 1.1em;
        }

        .bot-markdown > *:first-child {
        	margin-top: 0;
        }

        .bot-markdown > *:last-child {
        	margin-bottom: 0;
        }

        .bot-markdown p,
        .bot-markdown ul,
        .bot-markdown ol,
        .bot-markdown pre,
        .bot-markdown blockquote {
        	margin: 0 0 8px 0;
        }

        .bot-markdown ul,
        .bot-markdown ol {
        	padding-left: 22px;
        }

        .bot-markdown li {
        	margin-bottom: 2px;
        }

        .bot-markdown h1,
        .bot-markdown h2,
        .bot-markdown h3,
        .bot-markdown h4 {
        	margin: 10px 0 6px 0;
        	line-height: 1.3;
        }

        .bot-markdown h1 { font-size: 1.25em; }
        .bot-markdown h2 { font-size: 1.15em; }
        .bot-markdown h3,
        .bot-markdown h4 { font-size: 1.05em; }

        .bot-markdown code {
        	font-family: ui-monospace, Menlo, Consolas, monospace;
        	font-size: 0.9em;
        	background-color: var(--bg3);
        	border-radius: 4px;
        	padding: 1px 5px;
        }

        .bot-markdown pre {
        	background-color: var(--bg3);
        	border-radius: 8px;
        	padding: 10px 12px;
        	overflow-x: auto;
        }

        .bot-markdown pre code {
        	background-color: transparent;
        	padding: 0;
        }

        .bot-markdown blockquote {
        	border-left: 3px solid var(--border2);
        	padding-left: 10px;
        	color: var(--text2);
        }

        .bot-markdown a {
        	color: var(--accent);
        }

        .bot-markdown table {
        	border-collapse: collapse;
        	margin-bottom: 8px;
        	max-width: 100%;
        	display: block;
        	overflow-x: auto;
        }

        .bot-markdown th,
        .bot-markdown td {
        	border: 1px solid var(--border2);
        	padding: 4px 8px;
        	text-align: left;
        }

        /* An image attached to a bot reply via the send_image tool -
           see attachReplyImage(). Appended inside .bot-markdown once
           it's downloaded, so it sits under the reply text. Capped at
           a preview height so a tall/dense image (a full-page
           screenshot, a portrait photo) doesn't dominate the chat feed
           and force a lot of scrolling - object-fit: contain (not
           cover) means the whole image always fits inside that box
           rather than getting cropped, since these are often the
           actual content of the reply (a chart, a diagnostic photo)
           rather than a decorative thumbnail. Tap to view full-size -
           see openImageLightbox(). */
        .bot-reply-image {
        	display: block;
        	max-width: 100%;
        	max-height: 320px;
        	width: auto;
        	object-fit: contain;
        	border-radius: 10px;
        	margin-top: 8px;
        	cursor: zoom-in;
        }

        /* ============================================================
           Full-size image viewer - opened by tapping a .bot-reply-image
           thumbnail. Same fixed full-screen overlay pattern as the
           other overlays above, but with a fixed dark backdrop rather
           than var(--overlay-bg): a photo viewer conventionally stays
           dark regardless of light/dark mode, so images read clearly
           against it either way.
           ============================================================ */
        .image-lightbox {
        	position: fixed;
        	inset: 0;
        	z-index: 800;
        	display: flex;
        	align-items: center;
        	justify-content: center;
        	background-color: rgba(0, 0, 0, 0.88);
        	opacity: 0;
        	visibility: hidden;
        	transition: opacity 0.25s ease, visibility 0.25s ease;
        	padding: 20px;
        	box-sizing: border-box;
        }

        .image-lightbox.open {
        	opacity: 1;
        	visibility: visible;
        }

        .image-lightbox img {
        	max-width: 100%;
        	max-height: 100%;
        	border-radius: 8px;
        	object-fit: contain;
        	cursor: zoom-out;
        }

        .close-image-lightbox {
        	position: fixed;
        	top: 16px;
        	right: 16px;
        	width: 48px;
        	height: 48px;
        	border-radius: 50%;
        	border: 1px solid rgba(255, 255, 255, 0.3);
        	background: rgba(0, 0, 0, 0.4);
        	color: #fff;
        	font-size: 22px;
        	line-height: 1;
        	cursor: pointer;
        	z-index: 801;
        	transition: background-color 0.2s ease;
        }

        .close-image-lightbox:hover,
        .close-image-lightbox:focus-visible {
        	background: rgba(0, 0, 0, 0.6);
        	outline: none;
        }

        /* ============================================================
           Per-message speaker icon: pulses while its audio is playing,
           doubling as both the "audio is playing" cue and the
           "click here to mute" affordance (replaces the old separate
           mute button in the sticky bar).

           Opacity-only on purpose: this icon has display:block (see
           .display-block below) so it drops onto its own line under
           the chat text, which makes its box as wide as the paragraph
           while the glyph itself sits left-aligned inside that box.
           transform: scale() scales around the box's center, not the
           glyph, so it visibly drifts sideways as it grows/shrinks.
           Opacity doesn't touch layout/geometry at all, so it can't
           cause that drift regardless of box width.
           ============================================================ */
        .speaker-icon.speaking {
        	animation: speaker-pulse 1s ease-in-out infinite;
        }

        @keyframes speaker-pulse {
        	0%, 100% { opacity: 1; }
        	50% { opacity: 0.35; }
        }


        /* When the page loads
        show audioIndicator1 and
        hide #audioIndicator
        */

        #audioIndicator1 {
            display: inline-block;
            vertical-align: middle;
        }

        #audioIndicator {
        	display: none;
        	vertical-align: middle;
        }

        /* 
        --------------
        MEDIA QUERIES
        --------------
        */

        /*Cellphone Screens in portrait*/	
        @media only screen and (max-width: 480px) and (orientation: portrait){

        	.container {
        	        padding: 0;
        	}
        		
        	.hide-on-phone {
        		display: none;
        	}

            .input-group {
                padding: 0 12px; /* Add a dedicated structural layout margin from phone borders */
            }

            .wrapper {
                padding: 0 12px; /* Match .input-group's phone padding above */
            }

            /* The toggle row (currently just Auto Speak) is stacked
               into a single column for a taller, easier tap target
               on phones. */
            #line1 {
                flex-direction: column;
                align-items: flex-start;
                gap: 2px;
            }

            /* Larger chat bubble text on mobile for readability. */
            .message-container {
                font-size: 18px;
            }
        } /*Close media query*/

        /* ============================================================
           Access-token gate. Same fixed full-screen overlay pattern as
           .language-overlay / .plan-overlay above - shown by default
           (via the "open" class in the markup) so there's no flash of
           the chat UI before the token check has run.
           ============================================================ */
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
        	color: #e5484d;
        	font-size: 14px;
        	min-height: 18px;
        }

        #changeTokenLink {
        	display: inline-block;
        	color: var(--text3);
        	font-size: 12px;
        	margin-top: 4px;
        }

        #changeTokenLink:hover,
        #changeTokenLink:focus-visible {
        	color: var(--accent);
        	outline: none;
        }
    </style>
	
</head>

<body>
<!-- Access-token gate. Talks to api.php - blocks the chat UI behind
     it until a valid token is stored. "open" by default so this shows
     immediately, before the token-check JS has had a chance to run. -->
<div class="token-gate-overlay open" id="tokenGate" role="dialog" aria-modal="true" aria-label="Enter access token">
    <p style="font-size:17px; color:var(--text);">Enter your access token to continue</p>
    <input type="text" id="tokenInput" placeholder="Access token" autocomplete="off" aria-label="Access token">
    <button type="button" id="tokenSubmitBtn">Continue</button>
    <p class="token-gate-error" id="tokenError" style="display:none;">Invalid token, try again.</p>
</div>

<div id="drag-drop-indicator" aria-hidden="true"></div>

    <div class="plan-overlay" id="plan-overlay" role="dialog" aria-modal="true" aria-label="Commands">
        <button class="close-plan-overlay" id="close-plan-overlay" aria-label="Close commands">&#10005;</button>
        <p class="plan-overlay-subtitle">Click a command to send it to W2H.</p>
        <ul id="plan-list">
            <!-- Populated by JS -->
        </ul>
    </div>

    <!-- Full-size viewer for images the model attaches to its replies
         (see attachReplyImage()/openImageLightbox()). Empty <img> filled
         in by JS right before opening. -->
    <div class="image-lightbox" id="image-lightbox" role="dialog" aria-modal="true" aria-label="Image viewer">
        <button class="close-image-lightbox" id="close-image-lightbox" aria-label="Close image viewer">&#10005;</button>
        <img id="image-lightbox-img" src="" alt="">
    </div>

    <div class="container w3-animate-opacity">
        <div id="main-image" class="w3-center w3-round w3-padding">

			<img src="assets/ebot2.png" alt="W2H" class="hero-image" loading="eager" fetchpriority="high">

			<h2 class="space-letters"><b>W2H</b></h2>

            <h4 class="space-letters hero-subtitle">Home Server Web Interface</h4>
			
			
            <a href="control-panel.php" id="about-link">Control Panel</a>
		   

        </div>
        <main id="chat" class="texts">
            <!-- Add more message containers here -->
            <!-- The div for the spinner gets added and deleted here. -->
        </main>
        <div class="sticky-bar">
			
            <div id="image-preview-row" class="image-preview-row">
                <img id="image-preview-thumb" src="" alt="Selected image preview">
                <button type="button" id="remove-image-btn" aria-label="Remove image">&#10005;</button>
            </div>

            <div id="suggested-prompts" class="suggested-prompts">
                <button type="button" class="suggested-prompt-btn" onclick="submit_text_to_php('Hello')">Hello</button>
                <button type="button" class="suggested-prompt-btn" onclick="submit_text_to_php('What files are available?')">What files are available?</button>
            </div>

            <!-- action is informational only - form.onsubmit below always
                 preventDefault()s and drives the request itself via
                 api.php's send_message/status/get_outgoing endpoints. -->
            <form id="myForm" action="api.php?action=send_message" method="post">
                <div class="input-group">
                    <label id="image-picker-label" for="image-input" aria-label="Attach an image" title="Attach an image">
                        <i class="fa fa-camera" style="font-size:20px;"></i>
                    </label>
                    <input id="image-input" type="file" accept="image/*" style="display:none;">
                    <input id="user-input" type="text" name="my_message" placeholder="Type or talk..." autofocus>
                    <input type="hidden" name="robotblock">
                    <input id="submit-btn" type="submit" value="Send">
                </div>
                <div class="w3-padding space-letters">
                    <input id="workspace-file-input" type="file" style="display:none;">
                    <button type="button" class="" id="upload-file-btn" aria-label="Upload a file to the workspace" title="Upload a file to the workspace"><i class="fa fa-upload" style="font-size:20px;"></i></button>
                    <button type="button" class="" id="start-voicechat-btn" onclick="toggle_voicechat(lang_code)" aria-label="Start Voicechat" title="Start Voicechat"><i class="fa fa-microphone" style="font-size:25px;"></i></button>
                    <button type="button" class="" id="practice-plan-btn" aria-label="Commands" title="Commands"><i class="fa fa-list-ul" style="font-size:22px;"></i></button>
                    <button type="button" class="" id="accordion" aria-label="Settings" aria-expanded="false"><i class="fa fa-gear" style="font-size:25px;"></i></button>
                    <div id="audioIndicator">
                        <div class="bar"></div>
                        <div class="bar"></div>
                        <div class="bar"></div>
                    </div>
                    <div id="audioIndicator1">
                        <div class="bar1"></div>
                        <div class="bar1"></div>
                        <div class="bar1"></div>
                    </div>
                </div>
                <div class="wrapper">
                    <div class="form-elements">
                        <div id="panel">
                          <div id="panel-inner">
                            <div id="line1" class="radio-group">
                                <label class="radio-option">
                                    <input id="speakid" class="w3-padding" type="radio" name="speak1" value="speak" onclick="toggleRadio(this)">
                                    Auto Speak
                                </label>
                            </div>
                            <div id="line3" class="radio-group" style="margin-top: 5px; margin-bottom: 5px;">
                                <label class="radio-option">
                                    <input id="theme-toggle" type="checkbox" onclick="toggleTheme(this)">
                                    Dark Mode
                                </label>
								<!--
                                <a href="#" id="changeTokenLink">Change Token</a>
							    -->
                            </div>
                            <div id="voice-picker-row" class="settings-row">
                                <label class="settings-row-label" for="voice-select">
                                    <span>Voice</span>
                                </label>
                                <div class="settings-row-controls">
                                    <div id="voice-select-wrap" class="dropdown-option">
                                        <select class="styled-dropdown" id="voice-select" aria-label="Choose the bot's voice" onchange="onVoiceSelected(this)">
                                            <option value="">Default</option>
                                        </select>
                                    </div>
                                    <button type="button" id="preview-voice-btn" aria-label="Preview this voice" title="Preview this voice" onclick="previewSelectedVoice()">
                                        <i class="fa fa-play" style="font-size:12px;"></i>
                                    </button>
                                </div>
                            </div>
                            <div id="speed-picker-row" class="settings-row">
                                <label class="settings-row-label" for="speed-select">
                                    <span>Speed</span>
                                </label>
                                <div class="settings-row-controls">
                                    <!-- value/min/max here are an internal 0-100 "position"
                                         scale, NOT the speaking rate itself. See
                                         speedPositionToRate()/speedRateToPosition() in the
                                         script below: a plain linear 0.5x-2x mapping puts
                                         1.0x (the default/"normal" speed) at 33% along the
                                         track instead of visual center, since 1.0 isn't the
                                         midpoint of 0.5-2. This piecewise mapping keeps the
                                         full 0.5x-2x range while guaranteeing 1.0x always
                                         renders at exactly 50%. -->
                                    <input type="range" id="speed-select" min="0" max="100" step="0.1" value="50" aria-label="Speaking speed" aria-valuetext="1.0x" oninput="onSpeedChanged(this)">
                                    <span id="speed-value-label" aria-hidden="true">1.0x</span>
                                </div>
                            </div>
                            <div id="line2">
                                
                                <!-- This dropdown now lives visually in the language
                                     pill + overlay at the top of the page. It's kept
                                     here (visually hidden) since it's still the actual
                                     form field that gets submitted, and the rest of the
                                     app's JS (updateSelectedOption, updateSelectedLanguage)
                                     already targets it by id. -->
                                <div id="dropdown1" class="dropdown-option w3-padding-left w3-padding-bottom visually-hidden-select">
                                    <select class="styled-dropdown" id="language-select" name='user_language' onchange="updateSelectedOption(this)">
                                        <option value="Afrikaans">Afrikaans</option>
                                        <option value="Albanian">Albanian</option>
                                        <option value="Arabic">Arabic</option>
                                        <option value="Armenian">Armenian</option>
                                        <option value="Assamese">Assamese</option>
                                        <option value="Azerbaijani">Azerbaijani</option>
                                        <option value="Bashkir">Bashkir</option>
                                        <option value="Basque">Basque</option>
                                        <option value="Belarusian">Belarusian</option>
                                        <option value="Bengali">Bengali</option>
                                        <option value="Bosnian">Bosnian</option>
                                        <option value="Bulgarian">Bulgarian</option>
                                        <option value="Burmese">Burmese</option>
                                        <option value="Catalan">Catalan</option>
                                        <option value="Chinese">Chinese</option>
                                        <option value="Croatian">Croatian</option>
                                        <option value="Czech">Czech</option>
                                        <option value="Danish">Danish</option>
                                        <option value="Dutch">Dutch</option>
                                        <option value="English">English</option>
                                        <option value="Estonian">Estonian</option>
                                        <option value="Faroese">Faroese</option>
                                        <option value="Finnish">Finnish</option>
                                        <option value="French">French</option>
                                        <option value="Galician">Galician</option>
                                        <option value="Georgian">Georgian</option>
                                        <option value="German">German</option>
                                        <option value="Greek">Greek</option>
                                        <option value="Gujarati">Gujarati</option>
                                        <option value="Haitian Creole">Haitian Creole</option>
                                        <option value="Hebrew">Hebrew</option>
                                        <option value="Hindi">Hindi</option>
                                        <option value="Hungarian">Hungarian</option>
                                        <option value="Icelandic">Icelandic</option>
                                        <option value="Indonesian">Indonesian</option>
                                        <option value="Irish">Irish</option>
                                        <option value="Italian">Italian</option>
                                        <option value="Japanese">Japanese</option>
                                        <option value="Javanese">Javanese</option>
                                        <option value="Kannada">Kannada</option>
                                        <option value="Kazakh">Kazakh</option>
                                        <option value="Khmer">Khmer</option>
                                        <option value="Korean">Korean</option>
                                        <option value="Lao">Lao</option>
                                        <option value="Latvian">Latvian</option>
                                        <option value="Lithuanian">Lithuanian</option>
                                        <option value="Luxembourgish">Luxembourgish</option>
                                        <option value="Macedonian">Macedonian</option>
                                        <option value="Malay">Malay</option>
                                        <option value="Malayalam">Malayalam</option>
                                        <option value="Maltese">Maltese</option>
                                        <option value="Marathi">Marathi</option>
                                        <option value="Nepali">Nepali</option>
                                        <option value="Norwegian Bokmål">Norwegian Bokmål</option>
                                        <option value="Norwegian Nynorsk">Norwegian Nynorsk</option>
                                        <option value="Occitan">Occitan</option>
                                        <option value="Oriya">Oriya</option>
                                        <option value="Persian">Persian</option>
                                        <option value="Polish">Polish</option>
                                        <option value="Portuguese">Portuguese</option>
                                        <option value="Punjabi">Punjabi</option>
                                        <option value="Romanian">Romanian</option>
                                        <option value="Russian">Russian</option>
                                        <option value="Sardinian">Sardinian</option>
                                        <option value="Serbian">Serbian</option>
                                        <option value="Sindhi">Sindhi</option>
                                        <option value="Sinhala">Sinhala</option>
                                        <option value="Slovak">Slovak</option>
                                        <option value="Slovenian">Slovenian</option>
                                        <option value="Spanish">Spanish</option>
                                        <option value="Sundanese">Sundanese</option>
                                        <option value="Swahili">Swahili</option>
                                        <option value="Swedish">Swedish</option>
                                        <option value="Tagalog">Tagalog</option>
                                        <option value="Tajik">Tajik</option>
                                        <option value="Tamil">Tamil</option>
                                        <option value="Tatar">Tatar</option>
                                        <option value="Telugu">Telugu</option>
                                        <option value="Thai" selected>Thai</option>
                                        <option value="Turkish">Turkish</option>
                                        <option value="Ukrainian">Ukrainian</option>
                                        <option value="Urdu">Urdu</option>
                                        <option value="Uzbek">Uzbek</option>
                                        <option value="Vietnamese">Vietnamese</option>
                                        <option value="Welsh">Welsh</option>
                                        <option value="Yiddish">Yiddish</option>
                                    </select>
                                </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- The page gets scrolled up to this id. -->
    <div id="e-bot"></div>
    <!-- Onload a click is simulated on this to scroll the page to id="bottom-bar" -->
    <a href="#e-bot" id="scroll-page-up"></a>
    <a href="#test100" id="scroll-to-last-message"></a>
    <a href="#chatbot" id="scroll-to-bot-message"></a>

    <!--
    Dev-only panel showing per-request token counts and cost, as
    reported by OpenRouter. Hidden by default - flip DEV_MODE to true
    in the script below (or just delete this block) for production.
    -->
    <div id="dev-usage-panel" style="display:none; position:fixed; bottom:0; left:0; z-index:9999; max-width:340px; max-height:40vh; overflow-y:auto; background:rgba(0,0,0,0.85); color:#0f0; font-family:monospace; font-size:12px; padding:8px; border-top-right-radius:6px;"></div>

</body>
</html>


<script>
/* ============================================================
   Utility functions
   (formerly js/utils.js)
   ============================================================ */

// When the form is submitted this function removes
// the 'selected' attribute. This ensures we don't end
// up with two dropdown options that have an attribute called 'selected'.
// The 'selected' attribute gets added later when the ajax response
// displays the output on the page. This ensures that the language
// the user has selected stays selected.
function clearSelectedOptions() {
    var selectElement = document.getElementById('language-select');
    var options = selectElement.getElementsByTagName('option');
    
    for (var i = 0; i < options.length; i++) {
        if (options[i].hasAttribute('selected')) {
            options[i].removeAttribute('selected');
        }
    }
}


//* Replaced this with a better solution *
// This sets the language when the dropdown option is selected.
// This gets called after the ajax response is received when the page gets updated.
function updateSelectedLanguage(user_language) {
    var selectElement = document.getElementById("language-select");
    // translation_language = selectElement.value;
    // console.log("Selected language: " + translation_language);

    // Get the <option> element you want to add the 'selected' attribute to by its value
    var optionToSelect = selectElement.querySelector('option[value="' + user_language + '"]'); 

    // Add the 'selected' attribute to the option
    optionToSelect.setAttribute("selected", "selected");

    // Keep the language pill's visible label in sync too.
    if (typeof window.updateLanguagePillLabel === 'function') {
        window.updateLanguagePillLabel();
    }
}


// This function creates the three dot spinner.
// Calling this function starts the spinner.
function spinner() {
    // Select the element where the spinner will be displayed
    const spinnerElement = document.getElementById("spinner");
    
    // Define an array of dots
    const dots = ["", ".", "..", "..."];
    
    // Initialize the dot counter
    let dotIndex = 0;// Set the color and size of the spinner
	
    spinnerElement.style.color = "var(--text)";
    spinnerElement.style.fontSize = "25px";
	
	
    
    // Start the spinner animation
    setInterval(() => {
        // Update the text content of the spinner element with the current dot
        // This adds the >... symbol
        // spinnerElement.textContent = `>${dots[dotIndex]}`;
        
        // This does not have the >... symbol
        spinnerElement.textContent = `${dots[dotIndex]}`;
    
        // Increment the dot counter
        dotIndex = (dotIndex + 1) % dots.length;
    }, 500);
}


// We create the div containing the spinner.
// We append the div to the chat.
// This displays the spinner.
function create_spinner_div() {
    // Create a new div element
    const spinnerElement = document.createElement("div");
    
    // Set the id attribute of the div element to "spinner"
    spinnerElement.setAttribute("id", "spinner");
    
    var chat = document.getElementById("chat");
    
    // Append the div to the chat
    chat.appendChild(spinnerElement);
    
    // Start the spinner
    spinner();
}


// This function deletes the div containing the spinner.
// This causes the spinner to disappear.
function delete_spinner_div() {
    // Get the div element you want to delete
    const elementToDelete = document.getElementById("spinner");
    
    // Get the parent node of the div element
    const parentElement = elementToDelete.parentNode;
    
    // Remove the div element from its parent node
    parentElement.removeChild(elementToDelete);
}


// This functions takes a list of text (paragraphs).
// If the paragraph does not have p tags then it adds them.
function wrapInPTags(paragraphs) {
    let result = '';

    for (let i = 0; i < paragraphs.length; i++) {
        const paragraph = paragraphs[i];

        if (paragraph.includes('<p>')) {
            result += paragraph;
        } else {
            result += '<p>' + paragraph + '</p>';
        }
    }

    return result;
}


// This function formats the text into paragraphs.
function formatResponse(response) {
    // Split the response into lines
    const lines = response.split("\n");

    // Combine the lines into paragraphs
    const paragraphs = [];
    let currentParagraph = "";

    for (const line of lines) {
        if (line.trim()) {  // Check if the line is non-empty
            currentParagraph += line.trim() + " ";
        } else if (currentParagraph) {  // Check if the current paragraph is non-empty
            paragraphs.push(currentParagraph.trim());
            currentParagraph = "";
        }
    }

    // Append the last paragraph
    if (currentParagraph) {
        paragraphs.push(currentParagraph.trim());
    }

    // Some text thats returned has \n character but no <p> tags.
    // Other text has <p> tags that we can use when displaying the text on the page.
    // Here we check each list item (paragraph). If it doesn't have <p> tags then add them.
    // This is also important when we save and then reload the chat history.
    // If you change this make sure that the saving and reloading also works well.
    formattedResponse = wrapInPTags(paragraphs);
    
    // Add HTML tags to separate paragraphs
    // const formattedResponse = paragraphs.map(p => `<p>${p}</p>`).join("");
    
    return formattedResponse;
}


// Function to create a new message container
function createMessageContainer(message) {
    var messageContainer = document.createElement("div");
    messageContainer.classList.add("message-container");
    messageContainer.classList.add("w3-animate-opacity");
    
    // Add an id attribute. This will help to scroll to
    // the bot message. This gets detelted after the page
    // is scrolled to the bot message.
    messageContainer.setAttribute("id", "chatbot");

    var messageText = document.createElement("span"); // p

    // This if statement sets the coour of the name that gets displayed
    if (message.sender == bot_name) {
        messageText.innerHTML = "<span class='set-color1'><b>&#x2022 " + message.sender + "</b></span>" + message.text;
    } else {
        messageText.innerHTML = "<span class='set-color2'><b>&#x2022 " + message.sender + "</b></span>" + message.text;
    }

    messageContainer.appendChild(messageText);

    return messageContainer;
}


// Function to add a new message to the chat
function addMessageToChat(message) {
    var chat = document.getElementById("chat");
    var messageContainer = createMessageContainer(message);
    
    chat.appendChild(messageContainer);
    
    // Persist so this bubble survives navigating away (e.g. to
    // control-panel.php) and back. sessionStorage (not localStorage) is
    // used deliberately: it's tied to this browser tab and clears
    // itself when the tab/window closes, matching the "wiped per-tab"
    // chat-history behaviour already assumed elsewhere in this file -
    // it just wasn't actually being persisted anywhere until now.
    saveMessageToHistory(message);
    
    // Scroll the page up by cicking on a div at the bottom of the page.
    simulateClick('scroll-page-up');
}

// sessionStorage key the chat transcript is stored under. Session-scoped
// on purpose - see the comment in addMessageToChat() above.
const CHAT_HISTORY_KEY = 'w2h_chat_history';

// Appends one message to the sessionStorage-backed transcript. Wrapped
// in try/catch since sessionStorage can throw (private browsing, quota
// exceeded, etc.) - losing persistence for this message shouldn't break
// the chat itself, so failures are logged and swallowed.
function saveMessageToHistory(message) {
    try {
        var history = JSON.parse(sessionStorage.getItem(CHAT_HISTORY_KEY) || '[]');
        history.push(message);
        sessionStorage.setItem(CHAT_HISTORY_KEY, JSON.stringify(history));
    } catch (err) {
        console.log('Could not save chat message to sessionStorage:', err);
    }
}

// Replays the sessionStorage-backed transcript back into #chat. Called
// once on page load (see DOMContentLoaded listener below) - this is
// what makes bubbles reappear after navigating to control-panel.php
// and back, instead of the chat pane loading empty.
//
// Known limitation: images the bot attached via send_image are shown
// via a short-lived blob: object URL created at receive-time (see
// attachReplyImage) and are deleted from the server right after being
// fetched, so they can't be restored here - only the reply's text
// comes back. Images the user attached to their own outgoing message
// use a data: URL embedded directly in message.text, so those DO
// survive and restore correctly.
function restoreChatHistory() {
    var history;
    try {
        history = JSON.parse(sessionStorage.getItem(CHAT_HISTORY_KEY) || '[]');
    } catch (err) {
        console.log('Could not restore chat history from sessionStorage:', err);
        return;
    }

    if (!history.length) {
        return;
    }

    var chat = document.getElementById('chat');
    history.forEach(function (message) {
        var messageContainer = createMessageContainer(message);
        // createMessageContainer() always stamps id="chatbot" (normally
        // removed again right after each live message finishes
        // rendering/speaking - see the two removeAttribute("id") call
        // sites). Restored messages skip that follow-up step, so strip
        // the id here to avoid several duplicate-id containers sitting
        // in the restored history at once.
        messageContainer.removeAttribute('id');
        chat.appendChild(messageContainer);
    });

    // Matches the same "a real conversation is underway" treatment a
    // freshly-sent first message gets further down this file.
    var suggestedPrompts = document.getElementById('suggested-prompts');
    if (suggestedPrompts) {
        suggestedPrompts.style.display = 'none';
    }

    simulateClick('scroll-page-up');
}

document.addEventListener('DOMContentLoaded', restoreChatHistory);

// Function to remove html tags from a string
function removeHtmlTags(str) {
    return str.replace(/(<([^>]+)>)/gi, "");
}

// Function to mute the cahtbot
// when it is speaking.
//
// NOTE: speechSynthesis.cancel() does not reliably fire the
// utterance's 'onend' event in every browser. The mic restart
// used to live only inside onend, which meant muting mid-speech
// could permanently leave voicechat mode with the mic off and
// no visual indication. To fix this, muting now explicitly
// restarts the mic itself (if it's supposed to be on), instead
// of depending on onend to do it.
function quiet_please() {
    speechSynthesis.cancel();
    
    // Stop whichever per-message icon is currently pulsing.
    clearSpeakingIcon();
    
    // Hide the moving audio bars and show the three dots bars
    hide('audioIndicator');
    show('audioIndicator1');
    
    // If the mic is supposed to be listening (voicechat mode is on),
    // make sure it actually is - don't rely on onend firing.
    if (micShouldBeOn) {
        console.log('Muted mid-speech - restarting mic directly...');
        restart_recognition_if_needed();
    }
}


// Stops the currently-animating speaker icon (if any) from pulsing,
// and resets its tooltip back to "Click to play". Centralized here
// since speak(), quiet_please(), and the utterance's onend/onerror
// handlers all need to agree on which icon (if any) is "speaking".
function clearSpeakingIcon() {
    if (window.currentSpeakingIcon) {
        window.currentSpeakingIcon.classList.remove('speaking');
        window.currentSpeakingIcon.setAttribute('title', 'Click to play');
        window.currentSpeakingIcon = null;
    }
}


// Function that converts text to speech
function speak(text, speech_lang_code, speech_voice_name, speech_rate = 1, iconElement = null) {

	if (!speechSynthesisSupported) {
		console.log('Speech synthesis is not supported in this browser - skipping TTS.');
		return;
	}

    // Only one utterance plays at a time, so only one icon should ever
    // show the "speaking" animation - clear whichever one was
    // animating before starting this one.
    clearSpeakingIcon();

    // Flush any utterance that's currently speaking or still queued
    // before starting this one. Without this, calling speak() again
    // while a previous utterance hasn't finished (e.g. clicking "play
    // translation" right after the English reply auto-speaks) queues
    // the new utterance instead of replacing it - and some browsers
    // (Chrome in particular) can bleed the voice/lang from one queued
    // utterance into another when that happens. This is what caused
    // the Thai voice to occasionally speak the English text.
    speechSynthesis.cancel();

    // Create a new instance of SpeechSynthesisUtterance
    const utterance = new SpeechSynthesisUtterance();
    
    // Set the text that you want to speak
    utterance.text = text;
	
	// If speech recognition is currently running, stop it while the
	// bot talks (otherwise the mic will hear - and respond to - the
	// bot's own voice). We do NOT touch micShouldBeOn here: that flag
	// tracks the user's intent (voicechat mode on/off), independent of
	// this temporary pause. handleEnd() and quiet_please() both check
	// micShouldBeOn to decide whether to restart the mic.
	  if (window.recognition) {
		  
		  console.log('Stopping recognition while bot speaks...')
	  
		  window.recognition.removeEventListener('end', handleEnd);
		  window.recognition.stop();
	  
	  }
	
	
	/////////////
	
	 // Ensure the language is set
    utterance.lang = speech_lang_code;

    // Get the list of available voices. Prefer the cached list (populated
    // by onvoiceschanged) since getVoices() can return an empty array on
    // first call before the browser has finished loading its voice list.
    const voices = (cachedVoices && cachedVoices.length) ? cachedVoices : window.speechSynthesis.getVoices();

    // If the user has picked a voice from the Settings panel (see
    // populateVoiceSelect()/onVoiceSelected() above), it takes
    // priority over the server-provided default - but only for
    // English speech. Translated text is spoken with whatever
    // browser-default voice fits that language, so a user's English
    // voice preference shouldn't hijack, say, Thai playback.
    const userPreferredVoiceName = (speech_lang_code && speech_lang_code.toLowerCase().startsWith('en'))
        ? localStorage.getItem('ebot_preferred_voice_name')
        : null;
    const requestedVoiceName = userPreferredVoiceName || speech_voice_name;

    // Find the voice with the requested name (e.g. "Serena", "Jorge" -
    // these are hardcoded per-language in main.php, or picked by the
    // user from the Settings dropdown). This is a fragile,
    // OS/browser-specific exact-name match, so it will often come up
    // empty on non-Apple platforms - in that case we work down the
    // fallback chain below rather than failing.
    let selectedVoice = requestedVoiceName ? voices.find(voice => voice.name === requestedVoiceName) : null;

    // Fallback chain, English only: tried in order after the requested
    // voice (e.g. "Serena") comes up empty on this device/browser.
    // Each entry is checked by name AND lang, since voice names aren't
    // guaranteed unique across languages/platforms.
    const FALLBACK_VOICE_CHAIN = [
        { name: 'Arthur', lang: 'en-GB' },
        { name: 'Daniel', lang: 'en-GB' },
        { name: 'Samantha', lang: 'en-US' }
    ];
    if (!selectedVoice && speech_lang_code && speech_lang_code.toLowerCase().startsWith('en')) {
        for (const candidate of FALLBACK_VOICE_CHAIN) {
            const match = voices.find(voice =>
                voice.name === candidate.name &&
                voice.lang && voice.lang.toLowerCase() === candidate.lang.toLowerCase()
            );
            if (match) {
                selectedVoice = match;
                break;
            }
        }
    }

    if (selectedVoice) {
        utterance.voice = selectedVoice;
    } else if (requestedVoiceName) {
        console.log('Requested voice "' + requestedVoiceName + '" and the fallback chain (Arthur/Daniel/Samantha) were not found on this device/browser - using the browser\'s default voice instead.');
    } else {
        console.log('No specific voice requested for lang "' + speech_lang_code + '" - using the browser\'s default voice for it.');
    }
	
	
	/////////////
	
	
	// Set the speaking rate. The Settings panel's Speed slider (see
	// getPreferredSpeechRate() above) always wins here - unlike the
	// voice name override, speed isn't language-specific, so this
	// applies to any text this function is asked to speak.
    utterance.rate = getPreferredSpeechRate();
    

    // When the chatbot starts speaking display the sound bar animation
    hide('audioIndicator1');
    show('audioIndicator');
    
    // Pulse this message's speaker icon (if one was passed in) and
    // remember it so quiet_please()/onend/onerror can find it again.
    window.currentSpeakingIcon = iconElement;
    if (iconElement) {
        iconElement.classList.add('speaking');
        iconElement.setAttribute('title', 'Playing - click to mute');
    }
    
    utterance.onend = function() {
        // When the chatbot stops speaking hide the sound bar animation
        hide('audioIndicator');
        show('audioIndicator1');
        clearSpeakingIcon();
		
		// Only when the speech synthesis ends, start the mic again -
		// but only if voicechat mode is still supposed to be on.
		// If we don't gate this on micShouldBeOn, the mic could start
		// listening again even after the user muted or stopped voicechat.
		restart_recognition_if_needed();
    };
    
    utterance.onerror = function(event) {
        // 'interrupted' / 'canceled' fire whenever speechSynthesis.cancel()
        // preempts this utterance - which happens on every normal replay-
        // a-different-message click, every mute, and every new speak()
        // call (which now flushes the queue first), since speak() and
        // quiet_please() both call cancel(). That's expected, not a
        // failure, so it's logged at a lower level than a real error.
        if (event.error === 'interrupted' || event.error === 'canceled') {
            console.log('Speech synthesis interrupted (expected - a new message started playing or audio was muted).');
        } else {
            console.log('Speech synthesis error:', event.error);
        }
        
        // Don't leave the "speaking" animation stuck on if TTS fails.
        hide('audioIndicator');
        show('audioIndicator1');
        clearSpeakingIcon();
        
        // Make sure the mic doesn't stay stuck off just because TTS failed.
        restart_recognition_if_needed();
    };
    
    // Speak the text. This is deliberately deferred a tick rather than
    // called synchronously: Chrome (and some other browsers) have a
    // known bug where calling speak() immediately after cancel() can
    // silently drop the utterance entirely - no sound, no onerror,
    // nothing - because cancel() hasn't actually finished tearing down
    // the previous utterance yet even though it returns right away.
    // A short delay gives that teardown time to complete first.
    setTimeout(function() {
        speechSynthesis.speak(utterance);
    }, 50);
    
}


// Restarts speech recognition if (and only if) voicechat mode is
// supposed to be on. Centralizing this logic means quiet_please(),
// utterance.onend, and utterance.onerror all agree on whether the
// mic should be listening, instead of each having their own copy
// of this logic (which is how the old mute bug happened).
function restart_recognition_if_needed() {
    if (!micShouldBeOn) {
        return;
    }
    
    if (!window.recognition) {
        // Recognition object was torn down (e.g. by stop_recognition()) -
        // nothing to restart.
        return;
    }
    
    console.log('Restarting recognition...');
    
    window.recognition.removeEventListener('end', handleEnd);
    window.recognition.addEventListener('end', handleEnd);
    
    try {
        window.recognition.start();
    } catch (err) {
        // start() throws if recognition is already running (e.g. it
        // never actually stopped) - safe to ignore.
        console.log('Recognition already running or could not be restarted:', err);
    }
}


// Function to remove items from a json string
// before it gets displayed on the page.
function replaceItemsInString(inputString) {
    const itemsToReplace = ["```", "json", "{", "}", '"correction": "', '"translation": "', "#"];
    
    let modifiedString = inputString;
    itemsToReplace.forEach(item => {
        const regex = new RegExp(item, 'g'); // Create a global regular expression for each item
        modifiedString = modifiedString.replace(regex, "");
    });
    
    modifiedString = modifiedString.trim();
    
    // Only strip a trailing quote character if one is actually left over
    // (see the matching fix in main.php's replaceItemsInString for the
    // full explanation). Previously this unconditionally sliced off the
    // last character of every string, which truncated translated replies
    // that didn't happen to end in a stray '"'.
    if (modifiedString.endsWith('"')) {
        modifiedString = modifiedString.slice(0, -1);
    }
    
    modifiedString = removeEmojis(modifiedString);
    
    return modifiedString;
}


function removeEscapeSlashes(str) {
    // Handles two distinct things that can show up in model output:
    // 1) Genuinely escaped quote/backslash characters, e.g. \" \' \\
    //    -> unescape them to the plain character.
    // 2) A literal two-character "\n" (backslash + n) or "\t" left over
    //    from the model double-escaping newlines/tabs inside its JSON
    //    output (e.g. it emits \\n, which after one layer of JSON
    //    decoding becomes the literal text \n instead of a real
    //    newline). These aren't real control characters, so
    //    removeNewlines() never catches them and they show up as
    //    visible "\n\n" in the chat. Convert them to real control
    //    characters here so removeNewlines() can strip them as normal.
    return str
        .replace(/\\n/g, '\n')
        .replace(/\\t/g, '\t')
        .replace(/\\(["'\\])/g, '$1');
}

function removeNewlines(str) {
  return str.replace(/[\r\n]+/g, '');
}


// Function to remove emojis from text
function removeEmojis(text) {
    return text.replace(/[\u{1F600}-\u{1F64F}]/gu, '')  // Emoticons
               .replace(/[\u{1F300}-\u{1F5FF}]/gu, '')  // Miscellaneous Symbols and Pictographs
               .replace(/[\u{1F680}-\u{1F6FF}]/gu, '')  // Transport and Map Symbols
               .replace(/[\u{1F700}-\u{1F77F}]/gu, '')  // Alchemical Symbols
               .replace(/[\u{1F780}-\u{1F7FF}]/gu, '')  // Geometric Shapes Extended
               .replace(/[\u{1F800}-\u{1F8FF}]/gu, '')  // Supplemental Arrows-C
               .replace(/[\u{1F900}-\u{1F9FF}]/gu, '')  // Supplemental Symbols and Pictographs
               .replace(/[\u{1FA00}-\u{1FA6F}]/gu, '')  // Chess Symbols
               .replace(/[\u{1FA70}-\u{1FAFF}]/gu, '')  // Symbols and Pictographs Extended-A
               .replace(/[\u{2600}-\u{26FF}]/gu, '')    // Miscellaneous Symbols
               .replace(/[\u{2700}-\u{27BF}]/gu, '')    // Dingbats
               .replace(/[\u{FE00}-\u{FE0F}]/gu, '')    // Variation Selectors
               .replace(/[\u{1F1E6}-\u{1F1FF}]/gu, '')  // Flags
               .replace(/[\u{1F900}-\u{1F9FF}]/gu, '')  // Supplemental Symbols and Pictographs
               .replace(/[\u{1FA70}-\u{1FAFF}]/gu, ''); // Symbols and Pictographs Extended-A
}


// Function to get the user's preferred language
function getUserLanguage() {
    // Use navigator.languages if available, otherwise fallback to navigator.language
    const languages = navigator.languages && navigator.languages.length ? navigator.languages : [navigator.language || navigator.userLanguage];
    return languages[0];  // Return the first preferred language
}


function hide(elementId) {
    document.getElementById(elementId).style.display = "none";
}


function show(elementId) {
    document.getElementById(elementId).style.display = "inline-block";
}


function speakText(pElement) {
    playOrToggleMuteMessageAudio(pElement, speech_lang_code, speech_voice_name);
}

function playOrToggleMuteMessageAudio(pElement, langCode, voiceName) {
    var icon = pElement.querySelector('.speaker-icon');

    // If this exact message is the one currently playing, clicking it
    // again mutes/stops playback rather than restarting it - the
    // pulsing icon is the visual cue that a click here means "mute".
    if (icon && icon.classList.contains('speaking')) {
        quiet_please();
        return;
    }

    // .textContent strips every tag inside pElement - including
    // whatever marked.js produced (<strong>, <code>, <ul>, etc.), not
    // just the <i> speaker icon - and decodes HTML entities natively.
    // The icon's glyph itself is a CSS ::before pseudo-element, not a
    // text node, so it doesn't show up here either.
    var processedText = pElement.textContent;
    
    speak(processedText, langCode, voiceName, speech_rate, icon);
}




// Simulates a click.
function simulateClick(tabID) {
    // Simulate a click.
    document.getElementById(tabID).click();
}


// Re-focuses the chat input, but only on desktop. On mobile, focusing
// the input pops the on-screen keyboard open and covers the chat, so
// we skip it there and let the user tap back in when they're ready.
function focusUserInputDesktopOnly() {
    var screenWidth = window.screen.width;
    var isMobile = screenWidth <= 768;
    if (!isMobile) {
        const inputField = document.getElementById("user-input");
        if (inputField) inputField.focus();
    }
}


// Adding and removing the checked attribute ensures
// that the radio button remains checked or unchecked
// after the form is submitted. Otherwise it will return
// to its default status each time a chat message is sent.
function toggleRadio(radio) {
    // Check the previous state stored in a custom property
    if (radio.wasChecked) {
        // If it was previously checked, uncheck it
        radio.checked = false;
        radio.removeAttribute('checked');
        radio.wasChecked = false;  // Update the state to reflect that it's no longer checked
    } else {
        // If it was not checked, check it
        radio.checked = true;
        radio.setAttribute('checked', 'checked');
        radio.wasChecked = true;  // Update the state to reflect that it's now checked
    }

    // Keep the language pill (and its overlay) in sync with the
    // Translation toggle - hide the pill entirely when translation
    // is off, since there's nothing to pick a language for.
    if (radio.id === 'translateid') {
        setLanguagePillVisibility(radio.wasChecked);
    }
}


// Shows/hides the language pill header based on whether translation
// is currently on. Also force-closes the overlay if it happens to be
// open when translation gets turned off.
function setLanguagePillVisibility(visible) {
    const pillHeader = document.getElementById('language-pill-header');
    if (pillHeader) {
        pillHeader.style.display = visible ? '' : 'none';
    }
    if (!visible) {
        const overlay = document.getElementById('language-overlay');
        if (overlay) {
            overlay.classList.remove('open');
        }
    }
}


function checkRadioButton(radioName, radioID) {
    var radio = document.querySelector(`input[name="${radioName}"]`);
    if (radio) {
        radio.checked = true;
    }
    // This makes sure the button does not uncheck
    // when the form is submitted. It stays checked.
    document.getElementById(radioID).setAttribute('checked', 'checked');
}


function uncheckRadioButton(radioName, radioID) {
    var radio = document.querySelector(`input[name="${radioName}"]`);
    if (radio) {
        radio.checked = false;
    }
    document.getElementById(radioID).removeAttribute('checked');
}


// Helper function to update elements correctly
function updateSelectedOption(selectElement) {
    // Remove 'selected' attribute from all options
    for (let option of selectElement.options) {
        option.removeAttribute('selected');
    }

    // Add 'selected' attribute to the currently selected option
    selectElement.options[selectElement.selectedIndex].setAttribute('selected', 'selected');

    // Remember the choice so it's already selected next time the
    // user visits (same pattern as the voice/speed/theme prefs
    // elsewhere in this file).
    try {
        localStorage.setItem('ebot_preferred_language', selectElement.value);
    } catch (e) {
        // localStorage unavailable (e.g. private browsing) - the
        // selection just won't persist across visits.
    }
}


/* ============================================================
   Config
   (formerly js/config.js)
   Set the language that the user is learning
   ============================================================ */
lang_code = "en-GB";
</script>


<script>
// Cache of available TTS voices. getVoices() frequently returns an
// empty list on first page load until the browser's 'voiceschanged'
// event fires, so speak() should read from this cache rather than
// calling getVoices() fresh every time.
let cachedVoices = [];

// The key voice picking is stored under in localStorage, so the
// user's choice survives page reloads (sessionStorage isn't used
// here on purpose - the chat history is wiped per-tab via
// session_unset()/session_destroy() below, but a voice preference
// is a device/browser setting, not a conversation setting, so it
// should persist).
const VOICE_PREF_KEY = 'ebot_preferred_voice_name';

function cacheVoices() {
    const voices = speechSynthesis.getVoices();
    if (voices && voices.length) {
        cachedVoices = voices;
        console.log('Cached ' + voices.length + ' voices.');
        populateVoiceSelect();
    }
}

// Fills the #voice-select dropdown with the English voices this
// browser/OS actually has available, so the list only ever shows
// voices that will really work here (rather than a fixed list that
// might not exist on the visitor's device/browser - see the
// Chrome-vs-Safari voice mismatch this replaces).
function populateVoiceSelect() {
    const select = document.getElementById('voice-select');
    if (!select) return;

    // Practice language is English, so only offer English voices -
    // matches window.speech_lang_code ("en-GB" by default in main.php).
    const englishVoices = cachedVoices
        .filter(v => v.lang && v.lang.toLowerCase().startsWith('en'))
        .sort((a, b) => a.name.localeCompare(b.name));

    const previouslySelected = select.value || localStorage.getItem(VOICE_PREF_KEY) || '';

    // Rebuild the option list (keep the "Default" option first).
    select.innerHTML = '<option value="">Default</option>';
    englishVoices.forEach(voice => {
        const option = document.createElement('option');
        option.value = voice.name;
        option.textContent = voice.name + ' (' + voice.lang + ')';
        select.appendChild(option);
    });

    // Restore the saved/previous choice if it's still available on
    // this browser; otherwise fall back to "Default" quietly.
    if (previouslySelected && englishVoices.some(v => v.name === previouslySelected)) {
        select.value = previouslySelected;
    } else {
        select.value = '';
    }
}

// Called when the user picks a voice from the dropdown.
function onVoiceSelected(selectElement) {
    const voiceName = selectElement.value;
    if (voiceName) {
        localStorage.setItem(VOICE_PREF_KEY, voiceName);
    } else {
        localStorage.removeItem(VOICE_PREF_KEY);
    }
}

// Lets the user hear a short sample in the currently selected voice
// before committing to it, without having to send a chat message.
function previewSelectedVoice() {
    const select = document.getElementById('voice-select');
    if (!select) return;

    const voiceName = select.value || null; // null = browser default
    const langCode = (window.speech_lang_code) || 'en-GB';

    // Note: the 4th arg (rate) below is intentionally ignored by
    // speak() now - it always sources the rate from the Speed
    // slider itself via getPreferredSpeechRate(), so this preview
    // automatically plays at whatever speed is currently set.
    speak('Hi! This is a preview of my voice.', langCode, voiceName, 1, null);
}

// Same idea as VOICE_PREF_KEY above: speaking speed is a
// device/browser-level preference, so it's saved separately from
// the per-session chat state and persists across reloads.
const SPEED_PREF_KEY = 'ebot_preferred_speech_rate';

// The Speed slider's own value scale is 0-100 ("position" along the
// track) rather than the speaking rate directly. A plain linear
// mapping of a 0.5x-2x rate onto the track puts 1.0x (the default/
// "normal" speed) at 33% instead of visual center, since 1.0 isn't
// the midpoint of 0.5-2. These two conversions keep the full 0.5x-2x
// range while guaranteeing 1.0x always renders at exactly 50%: the
// left half of the track covers 0.5x-1.0x, the right half covers
// 1.0x-2.0x, each with its own (different) slope.
function speedPositionToRate(position) {
    return position <= 50
        ? 0.5 + (position / 50) * 0.5
        : 1.0 + ((position - 50) / 50) * 1.0;
}

function speedRateToPosition(rate) {
    return rate <= 1.0
        ? (rate - 0.5) * 100
        : 50 + (rate - 1.0) * 50;
}

// Restores the saved speed (or the 1.0x default) into the slider +
// its label. Called once on page load - the slider itself doesn't
// depend on voices being cached, so it doesn't need to wait for
// cacheVoices()/onvoiceschanged like the voice dropdown does.
function initSpeedSlider() {
    const slider = document.getElementById('speed-select');
    const label = document.getElementById('speed-value-label');
    if (!slider || !label) return;

    const saved = parseFloat(localStorage.getItem(SPEED_PREF_KEY));
    const rate = (!isNaN(saved) && saved >= 0.5 && saved <= 2) ? saved : 1;

    slider.value = speedRateToPosition(rate);
    slider.setAttribute('aria-valuetext', rate.toFixed(1) + 'x');
    label.textContent = rate.toFixed(1) + 'x';
}

// Called continuously while the user drags the slider (oninput, not
// onchange) so the "1.2x" label updates live as they move it. Rounds
// to the nearest 0.1x (matching the slider's old 0.1 step, before it
// was switched to an internal 0-100 position scale) so the stored/
// applied rate always matches what the label displays.
function onSpeedChanged(sliderElement) {
    const rawRate = speedPositionToRate(parseFloat(sliderElement.value));
    const rate = Math.round(rawRate * 10) / 10;
    sliderElement.setAttribute('aria-valuetext', rate.toFixed(1) + 'x');
    document.getElementById('speed-value-label').textContent = rate.toFixed(1) + 'x';
    localStorage.setItem(SPEED_PREF_KEY, rate);
}

// Returns the user's saved speaking speed, or 1 (normal speed) if
// they haven't set one yet.
function getPreferredSpeechRate() {
    const saved = parseFloat(localStorage.getItem(SPEED_PREF_KEY));
    return (!isNaN(saved) && saved >= 0.5 && saved <= 2) ? saved : 1;
}

initSpeedSlider();

// Some browsers (e.g. Chrome) have voices ready immediately; others
// only populate them once 'voiceschanged' fires. Try both.
cacheVoices();
speechSynthesis.onvoiceschanged = cacheVoices;
</script>

<script>
    // Opens/closes the settings panel with the smooth max-height
    // transition, keeping the gear button's active state and
    // aria-expanded in sync. Pulled out into named functions (rather
    // than only living inside the accordion's click handler) so the
    // click-outside-to-close listener below can call closeSettingsPanel()
    // directly without needing to fake a click on the accordion button.
    function isSettingsPanelOpen() {
        var panel = document.getElementById('panel');
        return !!panel.style.maxHeight;
    }

    function openSettingsPanel() {
        var panel = document.getElementById('panel');
        var accordionBtn = document.getElementById('accordion');
        var stickyBar = document.querySelector('.sticky-bar');
        panel.style.maxHeight = panel.scrollHeight + "px";
        accordionBtn.classList.add('settings-active');
        accordionBtn.setAttribute('aria-expanded', 'true');
        if (stickyBar) {
            stickyBar.classList.add('settings-open');
        }
    }

    function closeSettingsPanel() {
        var panel = document.getElementById('panel');
        var accordionBtn = document.getElementById('accordion');
        var stickyBar = document.querySelector('.sticky-bar');
        panel.style.maxHeight = null;
        accordionBtn.classList.remove('settings-active');
        accordionBtn.setAttribute('aria-expanded', 'false');
        if (stickyBar) {
            stickyBar.classList.remove('settings-open');
        }
    }

    // Event listener that prevents the form from submitting when
    // the "Settings" button is clicked.
    document.getElementById('accordion').addEventListener('click', function(event) {
        event.preventDefault();
        // Add your settings toggle code here
        console.log('Settings button clicked');
    });

    // JavaScript to toggle the visibility of the panel with a smooth transition
    document.getElementById('accordion').addEventListener('click', function() {
        if (isSettingsPanelOpen()) {
            closeSettingsPanel();
        } else {
            openSettingsPanel();
        }
    });

    // Click-outside-to-close: while the settings panel is open, a
    // click/tap anywhere outside the sticky bottom bar (which holds
    // the accordion button and the panel itself) closes it. Scoped to
    // .sticky-bar rather than just #panel so that clicking the message
    // input, the mic button, etc. also closes settings - not just
    // clicks on the chat messages above.
    document.addEventListener('click', function(event) {
        if (!isSettingsPanelOpen()) return;

        var stickyBar = document.querySelector('.sticky-bar');
        if (stickyBar && !stickyBar.contains(event.target)) {
            closeSettingsPanel();
        }
    });
	
	

	
	// *** ON LOAD ***
    // Auto Speak defaults to off - nothing here checks it, so it
    // simply keeps whatever unchecked state is in the markup above.
    window.onload = function() {
        // The Translation toggle (and its language pill) has been
        // removed, so the pill stays hidden unconditionally.
        setLanguagePillVisibility(false);
    };

    // Sync the Dark Mode checkbox as soon as the DOM is parsed rather
    // than waiting for window.onload (which only fires once every
    // resource on the page - including the hero image - has finished
    // loading), so on a slow connection the settings panel couldn't be
    // opened while the checkbox still hadn't been synced to the saved
    // theme yet.
    document.addEventListener('DOMContentLoaded', function() {
        // Load saved theme preference on page load. Light mode is the
        // default (the <body> tag has no dark-mode class by default),
        // so dark mode is only applied when explicitly saved.
        const savedTheme = localStorage.getItem('theme');
        const themeToggle = document.getElementById('theme-toggle');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeToggle) {
                themeToggle.checked = true;
            }
        } else {
            document.body.classList.remove('dark-mode');
            if (themeToggle) {
                themeToggle.checked = false;
            }
        }
    });

    // Toggle theme function (activates/deactivates Dark Mode)
    function toggleTheme(checkbox) {
        if (checkbox.checked) {
            document.body.classList.add('dark-mode');
            localStorage.setItem('theme', 'dark');
        } else {
            document.body.classList.remove('dark-mode');
            localStorage.setItem('theme', 'light');
        }
    }
</script>

<script>
    // These names are set in PHP at the top of this file.
    const bot_name = "<?php echo $bot_name; ?>";
    const user_name = "<?php echo $user_name; ?>";
</script>

<script>
    // ============================================================
    // Access-token gate + authFetch wrapper for api.php.
    //
    // api.php requires every request to carry a valid token (as the
    // X-Access-Token header). This block owns that token: it checks
    // for a saved one on load, shows the gate overlay if there isn't
    // a valid one, and wraps fetch() so every other script in this
    // page can just call authFetch(...) without thinking about the
    // token. Runs before the AJAX block below, which depends on
    // authFetch existing already.
    // ============================================================
    const API_BASE = "api.php";
    const TOKEN_STORAGE_KEY = 'w2h_access_token';

    // pollForReply() below long-polls api.php?action=status&wait=done:
    // the server itself holds each request open until the reply is
    // ready (or its own ~20s wait window elapses), so there's no fixed
    // "check every N ms" interval anymore - the loop just re-issues the
    // request immediately when one returns without a reply yet.
    // POLL_TIMEOUT_MS is still how long *this page* keeps re-issuing
    // long-polls before giving up altogether - kept at 5 minutes since
    // the Mac-side agent's multi-step tool-calling loop can legitimately
    // take a while (each tool call can boot a fresh sandbox VM).
    const POLL_TIMEOUT_MS = 5 * 60 * 1000; // 5 minutes

    // How long the browser's fetch() waits for a single long-poll
    // request before treating it as hung and retrying - comfortably
    // longer than the server's own wait window (STATUS_WAIT_MAX_SECONDS
    // in api.php, currently 20s) plus margin for real network latency.
    const LONGPOLL_FETCH_TIMEOUT_MS = 35 * 1000;

    // Safety-net floor between successive long-poll requests, only hit
    // if the server ever responds instantly and repeatedly (e.g. a
    // misconfiguration) - prevents that from becoming a tight loop.
    const LONGPOLL_MIN_GAP_MS = 250;

    const tokenGateEl = document.getElementById('tokenGate');
    const tokenInputEl = document.getElementById('tokenInput');
    const tokenSubmitBtn = document.getElementById('tokenSubmitBtn');
    const tokenErrorEl = document.getElementById('tokenError');

    // Wraps fetch() and automatically attaches the saved access token
    // as a header on every request. If the server rejects it (token
    // revoked, etc.), falls back to the token gate automatically.
    async function authFetch(url, options = {}) {
        const token = localStorage.getItem(TOKEN_STORAGE_KEY) || '';
        const headers = Object.assign({}, options.headers, {
            'X-Access-Token': token
        });
        const res = await fetch(url, Object.assign({}, options, { headers }));

        if (res.status === 401) {
            // Diagnostic only - doesn't change behavior. If the token
            // gate keeps reappearing unexpectedly, open devtools'
            // console next time it happens: this line fires exactly
            // when (and why) the stored token gets wiped, so it's
            // possible to tell a genuine server-side 401 apart from
            // something else clearing/blocking localStorage.
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

    // Validates a token against a harmless read-only endpoint.
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
    // against the server on every page load. That round-trip used to
    // gate closeTokenGate() itself, which meant any transient network
    // hiccup - a slow connection, a brief drop while switching wifi/
    // mobile data, etc. - looked exactly like a revoked token: the
    // check would fail, the saved (perfectly valid) token would get
    // wiped from localStorage, and the gate would reopen. That made
    // simply navigating away and back (e.g. the "Back to E-Bot" link
    // on control-panel.php) intermittently force a re-entry, since
    // every such navigation is a fresh page load that reran this same
    // check. Now a saved token is trusted at load time, and a genuinely
    // revoked token is instead caught the normal way - by authFetch's
    // existing 401 handler - the next time a real request is made,
    // which is a much rarer and more meaningful signal than "one
    // validation request happened to fail."
    function initTokenGate() {
        const savedToken = localStorage.getItem(TOKEN_STORAGE_KEY);

        if (savedToken) {
            closeTokenGate();
            return;
        }

        // Diagnostic only. If this logs on a page you expect to already
        // be signed in on (e.g. right after clicking a link from
        // control-panel.php), the token was never in localStorage at
        // load time at all - meaning it was cleared before this page
        // even started running, or the two pages aren't sharing storage
        // (different protocol/domain, a redirect in between, etc.)
        // rather than something in this page's own code clearing it.
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
    });

    tokenInputEl.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') tokenSubmitBtn.click();
    });

	/*
    document.getElementById('changeTokenLink').addEventListener('click', (e) => {
        e.preventDefault();
        resetToTokenGate();
    });
	*/

    initTokenGate();
</script>

<script>
    // ============================================================
    // Upload-to-workspace button
    // ============================================================
    // Silently sends an arbitrary file to api.php's upload_file
    // endpoint, which just drops it in a holding folder on Dreamhost.
    // mac_poller.py picks it up on its next status poll and moves it
    // straight into the Mac's Workspace folder - this is entirely
    // separate from the chat flow: no message is sent, no chat bubble
    // is added, and the agent isn't told. The only feedback here is
    // this button's own icon (spinner while uploading, a brief
    // check/error afterward) confirming the file reached Dreamhost -
    // confirming it actually landed in Workspace is left to asking the
    // agent, in chat, to look.

    var workspaceFileInput = document.getElementById('workspace-file-input');
    var uploadFileBtn = document.getElementById('upload-file-btn');
    var uploadFileIcon = uploadFileBtn ? uploadFileBtn.querySelector('i') : null;

    // Guards against a slow upload's result clobbering the icon state
    // if the user has already started (and possibly finished) another
    // upload in the meantime.
    var uploadGeneration = 0;

    function setUploadIcon(iconClass, extraButtonClass) {
        if (!uploadFileIcon) return;
        uploadFileIcon.className = 'fa ' + iconClass;
        uploadFileBtn.classList.remove('upload-success', 'upload-error');
        if (extraButtonClass) uploadFileBtn.classList.add(extraButtonClass);
    }

    function resetUploadIconAfterDelay(myGeneration) {
        setTimeout(function() {
            if (myGeneration !== uploadGeneration) return; // a newer upload has since taken over
            setUploadIcon('fa-upload');
            uploadFileIcon.style.fontSize = '20px';
        }, 2000);
    }

    async function uploadWorkspaceFile(file) {
        var myGeneration = ++uploadGeneration;

        setUploadIcon('fa-spinner fa-spin');

        var formData = new FormData();
        formData.append('file', file, file.name);

        try {
            var res = await authFetch(`${API_BASE}?action=upload_file`, {
                method: 'POST',
                body: formData
            });

            if (myGeneration !== uploadGeneration) return; // superseded by a newer upload

            if (!res.ok) {
                var errorData = await res.json().catch(function() { return {}; });
                console.log('upload_file failed:', res.status, errorData.error);
                setUploadIcon('fa-exclamation-triangle', 'upload-error');
                resetUploadIconAfterDelay(myGeneration);
                return;
            }

            setUploadIcon('fa-check', 'upload-success');
            resetUploadIconAfterDelay(myGeneration);
        } catch (err) {
            console.log('upload_file failed:', err);
            if (myGeneration !== uploadGeneration) return;
            setUploadIcon('fa-exclamation-triangle', 'upload-error');
            resetUploadIconAfterDelay(myGeneration);
        }
    }

    if (uploadFileBtn && workspaceFileInput) {
        uploadFileBtn.addEventListener('click', function() {
            workspaceFileInput.click();
        });

        workspaceFileInput.addEventListener('change', function() {
            var file = workspaceFileInput.files[0];
            workspaceFileInput.value = ''; // allow picking the same file again later
            if (file) uploadWorkspaceFile(file);
        });
    }
</script>

<script>
    // ============================================================
    // Image attach: pick, compress, preview
    // ============================================================
    // Selecting a file compresses it client-side (via canvas) into a
    // small JPEG data URI and shows a thumbnail preview immediately.
    // The actual image only gets sent to the server when the message
    // is submitted - see form.onsubmit below.

    var imageInput = document.getElementById('image-input');
    var imagePreviewRow = document.getElementById('image-preview-row');
    var imagePreviewThumb = document.getElementById('image-preview-thumb');
    var removeImageBtn = document.getElementById('remove-image-btn');

    // Holds the compressed data URI of the currently-selected image,
    // once compression has finished, or null if none is selected/ready.
    var pendingImageDataUrl = null;

    // While a file is being compressed, this holds a Promise that
    // resolves to its data URI (or null on failure). form.onsubmit
    // waits on THIS - not on pendingImageDataUrl directly - so that a
    // fast, hands-free voice submission can't race ahead of a still-
    // in-progress compression and get sent without its image.
    var pendingImageCompressionPromise = null;

    // Bumped on every new selection or removal, so a slow compression
    // that finishes after the user has already moved on (picked a
    // different file, or hit remove) doesn't clobber the current state.
    var imageGeneration = 0;

    // Resizes/re-encodes an image file down to a max dimension and
    // JPEG quality, keeping the request payload small. Returns a
    // Promise that resolves with a "data:image/jpeg;base64,..." string.
    function compressImageForUpload(file, maxDim, quality) {
        return new Promise(function(resolve, reject) {
            var objectUrl = URL.createObjectURL(file);
            var img = new Image();

            img.onload = function() {
                URL.revokeObjectURL(objectUrl);

                var scale = Math.min(1, maxDim / Math.max(img.width, img.height));
                var targetWidth = Math.round(img.width * scale);
                var targetHeight = Math.round(img.height * scale);

                var canvas = document.createElement('canvas');
                canvas.width = targetWidth;
                canvas.height = targetHeight;

                var ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, targetWidth, targetHeight);

                resolve(canvas.toDataURL('image/jpeg', quality));
            };

            img.onerror = function(err) {
                URL.revokeObjectURL(objectUrl);
                reject(err);
            };

            img.src = objectUrl;
        });
    }

    // Clears the pending image state, hides the preview row, and
    // resets the file input so the same file can be picked again later.
    function clearImagePreview() {
        imageGeneration++;
        pendingImageDataUrl = null;
        pendingImageCompressionPromise = null;
        imagePreviewThumb.src = '';
        imagePreviewRow.style.display = 'none';
        imageInput.value = '';
    }

    // Takes a File (from either the file picker or a drag-and-drop),
    // validates it, and kicks off compression/preview. Shared by both
    // entry points so they behave identically.
    function handleSelectedImageFile(file) {
        if (!file) {
            return;
        }

        if (!file.type.startsWith('image/')) {
            alert('Please choose an image file.');
            clearImagePreview();
            return;
        }

        var myGeneration = ++imageGeneration;
        pendingImageDataUrl = null;
        imagePreviewRow.style.display = 'none';

        var compressionPromise = compressImageForUpload(file, 1024, 0.7).then(function(dataUrl) {
            // Only touch the visible preview/state if this is still the
            // most recent selection - an older, slower compression
            // finishing after a newer pick (or a removal) shouldn't
            // clobber it. The resolved value is still returned either
            // way, for whichever submit is actually waiting on it.
            if (myGeneration === imageGeneration) {
                pendingImageDataUrl = dataUrl;
                imagePreviewThumb.src = dataUrl;
                imagePreviewRow.style.display = 'flex';
            }
            return dataUrl;
        }).catch(function(err) {
            console.log('Image compression failed:', err);
            if (myGeneration === imageGeneration) {
                alert('Sorry, that image could not be processed.');
                clearImagePreview();
            }
            return null;
        });

        pendingImageCompressionPromise = compressionPromise;
    }

    imageInput.addEventListener('change', function() {
        handleSelectedImageFile(imageInput.files[0]);
    });

    removeImageBtn.addEventListener('click', function() {
        clearImagePreview();
    });

    // ============================================================
    // Drag-and-drop: whole page is a drop zone
    // ============================================================
    // No full-page overlay - instead, a subtle border appears around
    // the page and the attach (camera) icon lights up, so a drop feels
    // available without dimming or blocking the rest of the content.
    //
    // dragenter/dragleave both bubble up from every element the cursor
    // crosses while dragging, so a plain "add on enter, remove on
    // leave" toggle flickers constantly as the cursor moves over child
    // elements. A counter fixes that: only the very first enter (from
    // outside the window) turns the indicator on, and only the count
    // dropping back to zero turns it off.
    var dragCounter = 0;

    // Ignores drags that aren't carrying files (e.g. dragging selected
    // text around the page) - only file drags should arm the indicator.
    function dragEventHasFiles(event) {
        return event.dataTransfer && Array.prototype.indexOf.call(event.dataTransfer.types || [], 'Files') !== -1;
    }

    document.addEventListener('dragenter', function(event) {
        if (!dragEventHasFiles(event)) {
            return;
        }
        event.preventDefault();
        dragCounter++;
        document.body.classList.add('page-drag-active');
    });

    document.addEventListener('dragover', function(event) {
        // Always prevent the default here - this is what allows a
        // 'drop' event to fire at all instead of the browser
        // navigating to the dropped file.
        if (dragEventHasFiles(event)) {
            event.preventDefault();
        }
    });

    document.addEventListener('dragleave', function(event) {
        if (!dragEventHasFiles(event)) {
            return;
        }
        dragCounter = Math.max(0, dragCounter - 1);
        if (dragCounter === 0) {
            document.body.classList.remove('page-drag-active');
        }
    });

    document.addEventListener('drop', function(event) {
        if (!dragEventHasFiles(event)) {
            return;
        }
        event.preventDefault();
        dragCounter = 0;
        document.body.classList.remove('page-drag-active');

        var file = event.dataTransfer.files && event.dataTransfer.files[0];
        handleSelectedImageFile(file);
    });
</script>

<script>
	// *** DEV_MODE ***
    // Async queue integration with api.php
    ///////////////////

    // Dev-only: set to true locally to show the token/cost panel
    // (#dev-usage-panel). Leave false in production. api.php doesn't
    // send usage/cost data (that was an OpenRouter-specific field from
    // the old main.php backend), so this stays a no-op for now.
    var DEV_MODE = false; // true/false

    function renderDevUsagePanel(debugUsage) {
        var panel = document.getElementById('dev-usage-panel');
        if (!panel || !DEV_MODE || !debugUsage) {
            return;
        }

        function fmtAgent(label, usage) {
            if (!usage) {
                return label + ': (not called)';
            }
            var cost = (usage.cost !== undefined) ? '$' + Number(usage.cost).toFixed(6) : 'n/a';
            var model = usage.model_id ? ' [' + usage.model_id + ']' : '';
            return label + model + ': ' + (usage.prompt_tokens ?? '?') + ' in / '
                + (usage.completion_tokens ?? '?') + ' out - ' + cost;
        }

        var lines = [
            fmtAgent('Proofreader', debugUsage.agents.proofreader),
            fmtAgent('Chat', debugUsage.agents.chat),
            fmtAgent('Translation', debugUsage.agents.translation),
            '---',
            'History pairs: ' + debugUsage.history_pairs,
            'Total tokens: ' + debugUsage.total_prompt_tokens + ' in / ' + debugUsage.total_completion_tokens + ' out',
            'Total cost this request: $' + Number(debugUsage.total_cost).toFixed(6)
        ];

        panel.innerHTML = lines.join('<br>');
        panel.style.display = 'block';
    }

    // Converts a "data:image/jpeg;base64,..." string (from
    // compressImageForUpload) into a Blob, then uploads it to
    // api.php's upload_image endpoint. Resolves with the filename
    // api.php assigned it, which is what send_message expects back.
    async function uploadImageDataUrl(dataUrl) {
        const blob = await (await fetch(dataUrl)).blob();

        const uploadFormData = new FormData();
        uploadFormData.append('image', blob, 'image.jpg');

        const res = await authFetch(`${API_BASE}?action=upload_image`, {
            method: 'POST',
            body: uploadFormData
        });

        if (!res.ok) {
            throw new Error('image upload failed');
        }

        const data = await res.json();
        return data.filename;
    }

    // Long-polls api.php?action=status&wait=done until the Mac script
    // has written a reply, then fetches it and resets the queue back to
    // idle. Unlike the old fixed-interval version, each request here
    // can itself take up to ~20s (the server holds it open waiting for
    // "done") - so this is a self-scheduling loop (issue one request,
    // wait for it, then issue the next) rather than a setInterval timer,
    // which would otherwise start overlapping requests once a single
    // poll takes longer than the timer's tick.
    var polling = false;

    async function pollForReply(onDone, onError) {
        if (polling) return;
        polling = true;

        var deadline = Date.now() + POLL_TIMEOUT_MS;

        while (true) {
            if (Date.now() >= deadline) {
                polling = false;
                onError('No reply received. Is the Mac script running?');
                return;
            }

            var statusData = null;
            var requestStarted = Date.now();
            try {
                var controller = new AbortController();
                var timeoutId = setTimeout(() => controller.abort(), LONGPOLL_FETCH_TIMEOUT_MS);
                var statusRes = await authFetch(`${API_BASE}?action=status&wait=done`, {
                    cache: 'no-store',
                    signal: controller.signal,
                });
                clearTimeout(timeoutId);
                statusData = await statusRes.json();
            } catch (err) {
                // Transient network hiccup, or our own client-side
                // timeout firing because the long-held request hung -
                // stay in the loop rather than giving up on one blip,
                // but still respect LONGPOLL_MIN_GAP_MS below so a run
                // of immediate failures doesn't spin hot.
                console.log('Status long-poll failed, retrying:', err);
            }

            if (statusData && statusData.status === 'done') {
                polling = false;

                try {
                    var replyRes = await authFetch(`${API_BASE}?action=get_outgoing`, { cache: 'no-store' });
                    var reply = await replyRes.json();
                    await authFetch(`${API_BASE}?action=reset_status`, { method: 'POST' });
                    onDone(reply);
                } catch (err) {
                    console.log('Failed to fetch/reset reply:', err);
                    onError('Sorry, something went wrong fetching the reply.');
                }
                return;
            }

            // Not done yet (server's own ~20s wait window just elapsed
            // with nothing new, which is the normal case) - loop
            // straight back into the next long-poll. The min-gap only
            // matters if the request above returned unusually fast.
            var elapsed = Date.now() - requestStarted;
            if (elapsed < LONGPOLL_MIN_GAP_MS) {
                await new Promise(resolve => setTimeout(resolve, LONGPOLL_MIN_GAP_MS - elapsed));
            }
        }
    }

    // Takes api.php's reply ({text, timestamp}) and renders it through
    // e-bot's existing display/speak pipeline. api.php's Mac-bridge
    // backend doesn't (yet) know about correction/translation/voice
    // metadata the way the old main.php did, so those fields are
    // synthesized here from the current client-side settings instead
    // of being read off the response. That wiring point is exactly
    // what a future w2h-aware Mac script would take over.
    function applyBotResponse(reply) {
        var speakToggle = document.getElementById('speakid');
        var speak_status = (speakToggle && speakToggle.checked) ? 'selected' : 'not selected';

        var translation_language = document.getElementById('language-select').value;

        window.speech_lang_code = window.speech_lang_code || lang_code || 'en-GB';
        window.speech_voice_name = localStorage.getItem(VOICE_PREF_KEY) || '';
        window.speech_rate = getPreferredSpeechRate();

        // Dev-only token/cost panel (no-op unless DEV_MODE is true;
        // api.php doesn't currently send usage data).
        renderDevUsagePanel(reply.debug_usage);

        var response_text = reply.text;

        // Correction/translation aren't implemented on the api.php
        // side yet, so these stay empty for now - the toggles remain
        // visible in Settings as placeholders for when that's wired up.
        let correctedText = "";
        let translatedText = "";

        // The model often replies in markdown (bold, lists, code blocks,
        // etc.) - render it to HTML rather than dumping the raw
        // asterisks/backticks/hashes into the chat. marked.parse()
        // converts markdown -> HTML; DOMPurify.sanitize() strips
        // anything dangerous (e.g. a stray script tag the model echoed
        // back from something the user pasted) before it's ever set via
        // innerHTML in createMessageContainer().
        let chatAgentResponse = DOMPurify.sanitize(marked.parse(removeEmojis(response_text)));

        // marked's output can include block elements (<ul>, <pre>,
        // <h1-4>, etc.), which aren't valid inside a <p> - a browser
        // would silently close the <p> early and break the "click
        // anywhere in the reply to hear it" behaviour. A <div> holds
        // arbitrary block content correctly instead.
        let chatText;
        if (speak_status == 'selected') {
            chatText = `<div class="clickable bot-markdown" onclick="speakText(this)">${chatAgentResponse}<i class="fa fa-volume-up w3-text-teal display-block speaker-icon" style="font-size:18px" title="Click to play"></i></div>`;
        } else {
            chatText = `<div class="clickable bot-markdown" onclick="speakText(this)">${chatAgentResponse}<i class="fa fa-volume-off w3-text-teal display-block speaker-icon" style="font-size:18px" title="Click to play"></i></div>`;
        }

        // Context-window usage caption - mac_poller.py only sends
        // num_ctx/context_size for a normal model turn (not local
        // /status-style commands or error replies), so both being
        // present is exactly the signal that there's something
        // meaningful to show. Kept outside the .bot-markdown div so
        // clicking it doesn't also toggle text-to-speech on the reply.
        let contextCaption = "";
        if (reply.num_ctx != null && reply.context_size != null) {
            contextCaption = `<div class="context-caption">${reply.context_size} / ${reply.num_ctx} tokens in context</div>`;
        }

        let finalText = correctedText + chatText + contextCaption + translatedText;

        var input_message = {
            sender: bot_name,
            text: finalText
        };

        // Keep the language pill/dropdown showing whatever's already
        // selected (api.php doesn't drive this yet).
        updateSelectedLanguage(translation_language);

        // Speak the same rendered content that's on screen, minus the
        // markup - reading raw markdown syntax aloud (asterisks,
        // backticks, "hash hash" for headings) sounds broken. A
        // detached element's .textContent strips all tags and decodes
        // entities correctly in one step; it's safe to use innerHTML
        // here since chatAgentResponse already went through DOMPurify.
        var speechSource = document.createElement('div');
        speechSource.innerHTML = chatAgentResponse;
        let cleaned_text = speechSource.textContent;

        delete_spinner_div();
        addMessageToChat(input_message);

        // Single lookup, reused below for the speak icon, the image
        // attachment point, and clearing the id - avoids querying the
        // DOM for the same element three times.
        var chatbotElement = document.getElementById('chatbot');

        if (speak_status == 'selected') {
            var newIcon = chatbotElement ? chatbotElement.querySelector('.speaker-icon') : null;
            speak(cleaned_text, window.speech_lang_code, window.speech_voice_name, window.speech_rate, newIcon);
        }

        // If the model attached an image (via the send_image tool),
        // fetch it through api.php - same token-gated endpoint the Mac
        // uses to fetch phone-uploaded images, just called from the
        // other side - and drop it into the reply once it's downloaded.
        // Fire-and-forget: doesn't block re-enabling the input below.
        if (reply.image) {
            attachReplyImage(chatbotElement, reply.image);
        }

        simulateClick('scroll-to-bot-message');

        if (chatbotElement) {
            chatbotElement.removeAttribute("id");
        }

        focusUserInputDesktopOnly();
    }

    // Downloads an image the model attached to its reply (see
    // applyBotResponse above) and appends it to that reply's bubble,
    // then tells the server it can delete its copy - mirroring how the
    // Mac already deletes phone-uploaded images once it's downloaded
    // them. A plain <img src="..."> can't be used directly: get_image
    // requires the access-token header, which only fetch()/authFetch
    // can attach (putting the token in the URL instead would leak it
    // into browser history).
    async function attachReplyImage(messageContainerEl, filename) {
        if (!messageContainerEl) return;

        try {
            var imgRes = await authFetch(`${API_BASE}?action=get_image&filename=${encodeURIComponent(filename)}`, { cache: 'no-store' });
            if (!imgRes.ok) {
                throw new Error('image fetch failed with status ' + imgRes.status);
            }
            var blob = await imgRes.blob();
            var objectUrl = URL.createObjectURL(blob);

            var target = messageContainerEl.querySelector('.bot-markdown') || messageContainerEl;
            var img = document.createElement('img');
            img.src = objectUrl;
            img.alt = 'Image from ' + bot_name;
            img.className = 'bot-reply-image';
            img.addEventListener('click', (e) => {
                // Without this, the click would also bubble up to the
                // parent .bot-markdown div's onclick="speakText(this)" -
                // tapping the image to view it would also toggle TTS on
                // the whole reply.
                e.stopPropagation();
                openImageLightbox(objectUrl, img.alt);
            });
            target.appendChild(img);

            simulateClick('scroll-to-bot-message');
        } catch (err) {
            console.log('Failed to load reply image:', err);
            return; // don't try to delete a server copy we never confirmed exists
        }

        try {
            await authFetch(`${API_BASE}?action=delete_image`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'filename=' + encodeURIComponent(filename)
            });
        } catch (err) {
            console.log('Failed to delete server-side reply image (non-fatal):', err);
        }
    }

    // Full-size viewer for .bot-reply-image thumbnails - see the
    // capped-height/object-fit:contain comment on that class for why
    // this exists: a preview small enough to keep the chat feed
    // scannable, with a guaranteed way to see the whole thing at full
    // size, so nothing the model chose to show is ever actually lost.
    var imageLightboxEl = document.getElementById('image-lightbox');
    var imageLightboxImgEl = document.getElementById('image-lightbox-img');

    function openImageLightbox(src, altText) {
        imageLightboxImgEl.src = src;
        imageLightboxImgEl.alt = altText || '';
        imageLightboxEl.classList.add('open');
    }

    function closeImageLightbox() {
        imageLightboxEl.classList.remove('open');
        // Clearing src means an already-open image doesn't briefly
        // flash the previous one the next time the lightbox opens.
        imageLightboxImgEl.src = '';
    }

    // Clicking anywhere in the overlay closes it - the backdrop and the
    // image itself both do the same thing here (cursor: zoom-out hints
    // at that), so one handler on the overlay covers both via bubbling.
    // Only the explicit close button needs its own listener.
    imageLightboxEl.addEventListener('click', () => closeImageLightbox());
    document.getElementById('close-image-lightbox').addEventListener('click', (e) => {
        e.stopPropagation();
        closeImageLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && imageLightboxEl.classList.contains('open')) {
            closeImageLightbox();
        }
    });

    var form = document.getElementById('myForm');

    form.onsubmit = function(event) {
        // Prevent the default form submission behavior
        event.preventDefault();

        // Get the form data. This MUST happen synchronously, right here,
        // in the same tick as the click - not after any await/then below.
        // Voice submissions (submit_text_to_php) set the input's value,
        // simulate a click on the submit button, and then immediately
        // clear that value again on the very next line. If reading the
        // form data were delayed, it would come back empty for voice
        // messages.
        var formData = new FormData(form);
        var $my_message = formData.get("my_message");

        // Snapshot whichever image-compression promise is currently in
        // flight (if any), rather than reading pendingImageDataUrl
        // directly - it may still be null right now even though an
        // image WAS attached, simply because compression hasn't
        // finished yet. Waiting on this specific promise is what stops
        // a fast, hands-free voice message from going out before its
        // attached image is ready, which used to split one message
        // into two separate sends (and two separate bot replies).
        var imageWait = pendingImageCompressionPromise || Promise.resolve(pendingImageDataUrl);

        // Reset the visible form/preview right away so the UI feels
        // responsive - the actual send below still waits on imageWait.
        form.reset();
        clearImagePreview();

        imageWait.then(async function(imageToSend) {

            // This will prevent the form from submitting if there's
            // neither text nor an attached image.
            if ($my_message == "" && !imageToSend) {
                return; // Exit the function if the condition is not met
            }

            // Prevent a second message being sent before the first
            // response has come back. Re-enabled once pollForReply's
            // onDone/onError fires, whichever happens.
            var submitBtn = document.getElementById('submit-btn');
            var userInput = document.getElementById('user-input');
            if (submitBtn) submitBtn.disabled = true;
            if (userInput) userInput.disabled = true;

            // Hide the suggested-prompt chips once a real message goes
            // out - they're a first-message nudge, not a permanent
            // fixture, so they get out of the way after that.
            var suggestedPrompts = document.getElementById("suggested-prompts");
            if (suggestedPrompts) {
                suggestedPrompts.style.display = "none";
            }

            // Format the input into paragraphs.
            $my_message = formatResponse($my_message);

            // Prepend a thumbnail of the sent image to the user's own
            // chat bubble, if one was attached.
            var displayText = imageToSend
                ? '<img src="' + imageToSend + '" class="chat-image-thumb" alt="Sent image">' + $my_message
                : $my_message;

            var input_message = {
                sender: user_name,
                text: displayText
            };

            console.log(input_message.text);

            // Add a user message to the chat
            addMessageToChat(input_message);

            // Show the spinner while waiting for the Mac script's reply
            create_spinner_div();

            // Scroll the page up by clicking on a div at the bottom of the page.
            simulateClick('scroll-page-up');

            // Delete the id from the message container.
            var element = document.getElementById("chatbot");
            element.removeAttribute("id");

            function finishWithError(message) {
                delete_spinner_div();
                alert(message);
                if (submitBtn) submitBtn.disabled = false;
                if (userInput) userInput.disabled = false;
                focusUserInputDesktopOnly();
            }

            // If an image was attached, upload it first - api.php needs
            // it saved to disk before send_message can reference it by
            // filename (unlike the old main.php, which took the raw
            // data URI inline in the same request).
            var imageFilename = '';
            if (imageToSend) {
                try {
                    imageFilename = await uploadImageDataUrl(imageToSend);
                } catch (err) {
                    console.log('Image upload failed:', err);
                    finishWithError('Sorry, that image could not be uploaded. Please try again.');
                    return;
                }
            }

            var body = new URLSearchParams();
            body.set('text', removeHtmlTags($my_message));
            if (imageFilename) body.set('image', imageFilename);

            var sendRes;
            try {
                sendRes = await authFetch(`${API_BASE}?action=send_message`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: body.toString()
                });
            } catch (err) {
                console.log('send_message failed:', err);
                finishWithError('Sorry, something went wrong sending your message. Please try again.');
                return;
            }

            if (!sendRes.ok) {
                console.log('send_message responded with status: ' + sendRes.status);
                finishWithError('Sorry, something went wrong processing your message. Please try again.');
                return;
            }

            pollForReply(function(reply) {
                applyBotResponse(reply);
                if (submitBtn) submitBtn.disabled = false;
                if (userInput) userInput.disabled = false;
                // Return focus to the chat input once the reply's in,
                // so the user can keep typing without clicking back into it.
                // (Desktop only - see focusUserInputDesktopOnly.)
                focusUserInputDesktopOnly();
            }, function(errorMessage) {
                finishWithError(errorMessage);
            });
        }); // end imageWait.then
    };
</script>

<script>

// ============================================================
// Voicechat (Speech-to-Text) state & feature detection
// ============================================================

// Tracks whether the mic is SUPPOSED to be listening right now
// (i.e. whether voicechat mode is turned on). This is tracked
// independently of the SpeechRecognition 'end' event and of TTS
// playback state, so that muting or a stray 'end' event can't
// silently leave the mic stuck on or off - see quiet_please()
// and restart_recognition_if_needed() above.
let micShouldBeOn = false;

// Feature detection, done once up front. Some browsers (Firefox,
// most iOS Safari) don't implement SpeechRecognition at all - the
// old code just silently did nothing when clicked on those browsers.
window.SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
const speechRecognitionSupported = !!window.SpeechRecognition;
const speechSynthesisSupported = !!window.speechSynthesis;

document.addEventListener('DOMContentLoaded', () => {
    const voicechatBtn = document.getElementById('start-voicechat-btn');
    if (voicechatBtn && !speechRecognitionSupported) {
        voicechatBtn.disabled = true;
        voicechatBtn.setAttribute('aria-label', 'Voicechat not supported in this browser');
        voicechatBtn.title = 'Speech recognition is not supported in this browser. Try Google Chrome on desktop or Android.';
        voicechatBtn.style.opacity = '0.5';
        voicechatBtn.style.cursor = 'not-allowed';
    }
});


// Event listener function.
// When the 'end' event fires, this restarts the mic - but only if
// voicechat mode is still supposed to be on. This is how "always
// listening" is simulated, since the API doesn't have a true
// "always on" mode.
function handleEnd() {
    console.log('Recognition ended.');
    if (micShouldBeOn) {
        console.log('Event listener restarting mic...');
        try {
            window.recognition.start();
        } catch (err) {
            console.log('Could not restart recognition:', err);
        }
    }
}


// Handles recognition errors so the user isn't left staring at
// "Listening..." forever with no idea what went wrong.
function handleRecognitionError(event) {
    console.log('Recognition error:', event.error);

    // Not a real problem - the mic just hasn't heard anything yet.
    // Let handleEnd() (which fires right after) restart it as normal.
    if (event.error === 'no-speech') {
        return;
    }

    let message;
    switch (event.error) {
        case 'not-allowed':
        case 'service-not-allowed':
            message = 'Microphone access was denied. Please allow microphone access in your browser and try again.';
            break;
        case 'audio-capture':
            message = 'No microphone was found. Please check your microphone and try again.';
            break;
        case 'network':
            message = 'A network error interrupted voice recognition. Please try again.';
            break;
        default:
            message = 'Voice recognition ran into a problem (' + event.error + '). Please try again.';
    }

    // Stop trying to auto-restart the mic after a real error, and
    // reset the UI so the user can see something went wrong.
    stop_recognition();
    alert(message);
}


function initialize_recognition(lang_code) {

    const recognition = new SpeechRecognition();

    //recognition.continuous = true;

    // *** Comment out this line for better performance on Android. ***
    // When this line is commented out there's no intermediate voice detections,
    // however, the bot works much better on Android.
    //recognition.interimResults = true;

    // Set the language you want
    recognition.lang = lang_code; //'ja-JP'; // or 'th-TH' for Thai // en-US

    console.log('Detection lang:');
    console.log(lang_code);

    // Make the recognition object available globally
    window.recognition = recognition;

    console.log('recognition initialized');

    window.recognition.addEventListener('end', handleEnd);
    window.recognition.addEventListener('error', handleRecognitionError);

    window.recognition.addEventListener("result", (e) => {

        let text = Array.from(e.results)
            .map((result) => result[0])
            .map((result) => result.transcript)
            .join("");

        if (e.results[0].isFinal) {

            // Format the input into paragraphs. This
            // adds paragrah html to the user's chat.
            // It's main use is where the bot's long response needs
            // to be formatted into separate paragraphs.
            text = formatResponse(text);

            // Use the form to submit the text to php for processing
            submit_text_to_php(text);
        }
    });

    window.recognition.start();

    // Select the button by ID
    const button = document.getElementById("start-voicechat-btn");

    // Show the mic as active/listening: swap to the "slash" icon,
    // add an orange border, and update the accessible label.
    if (button) {
        button.style.border = "2px solid orange";
        button.style.borderRadius = "8px";
        button.setAttribute('aria-label', 'Stop Voicechat');
        button.setAttribute('title', 'Stop Voicechat');
        const icon = button.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-microphone');
            icon.classList.add('fa-microphone-slash');
        }
    }

}


// Stops voicechat mode entirely (as opposed to the temporary stop
// that happens while the bot is speaking). Tears down the
// recognition object so a future Start click can't stack a second,
// leaked recognizer on top of a stale one.
function stop_recognition() {
    micShouldBeOn = false;

    if (window.recognition) {
        window.recognition.removeEventListener('end', handleEnd);
        window.recognition.removeEventListener('error', handleRecognitionError);
        try {
            window.recognition.stop();
        } catch (err) {
            console.log('Recognition already stopped:', err);
        }
        window.recognition = null;
    }

    const button = document.getElementById("start-voicechat-btn");
    if (button) {
        button.style.border = "";
        button.setAttribute('aria-label', 'Start Voicechat');
        button.setAttribute('title', 'Start Voicechat');
        const icon = button.querySelector('i');
        if (icon) {
            icon.classList.remove('fa-microphone-slash');
            icon.classList.add('fa-microphone');
        }
    }
}


// Submits generated text
function submit_text_to_php(my_text) {
    // Select the input element by its id
    const inputElement = document.getElementById('user-input');

    // Set the value attribute
    inputElement.setAttribute('value', my_text);

    // Simulate a click on the form submit button
    // This will send the form to the php code for processing.
    simulateClick('submit-btn');

    // Clear the value that was set
    inputElement.setAttribute('value', "");
}


// Source: Speech Recognition App Using Vanilla JavaScript
// https://www.youtube.com/watch?v=-k-PgvbktX4
//
// Toggles voicechat mode on/off. Replaces the old start-only
// button - previously there was no way to turn the mic off short
// of muting (which only affects TTS) or leaving the page. Also
// guards against the old leak where clicking "Start" again while
// already running would spin up a second overlapping recognizer.
function toggle_voicechat(lang_code) {

    if (!speechRecognitionSupported) {
        alert('Sorry, voice recognition is not supported in this browser. Please try Google Chrome.');
        return;
    }

    // Already running - this click means "stop".
    if (window.recognition) {
        stop_recognition();
        return;
    }

    micShouldBeOn = true;
    initialize_recognition(lang_code);
}
</script>

<script>
/* ============================================================
   Commands overlay.
   Reuses the app's existing submit_text_to_php() - the same
   function the mic input and the suggested-prompt buttons call -
   so tapping a command sends it exactly as if the user had typed
   and submitted it themselves. mac_poller.py recognizes these as
   special commands and handles them directly instead of running
   them through the model.
   ============================================================ */
document.addEventListener('DOMContentLoaded', () => {

    const COMMANDS = [
        { command: '/status', description: 'Show the model, memory size, and turn limit.' },
        { command: '/undo', description: "Remove the last exchange from the Mac's memory." },
        { command: '/reset', description: "Clear the Mac's conversation memory." },
        { command: '/get_state', description: 'Show the current home system state (e.g. the LED).' }
    ];

    const triggerBtn = document.getElementById('practice-plan-btn');
    const overlay = document.getElementById('plan-overlay');
    const closeBtn = document.getElementById('close-plan-overlay');
    const listEl = document.getElementById('plan-list');

    if (!triggerBtn || !overlay || !listEl) {
        return;
    }

    function renderCommands() {
        listEl.innerHTML = '';
        COMMANDS.forEach(({ command, description }, index) => {
            const li = document.createElement('li');
            li.className = 'plan-item';

            const numberBadge = document.createElement('span');
            numberBadge.className = 'command-number';
            numberBadge.textContent = index + 1;
            numberBadge.setAttribute('aria-hidden', 'true');

            const promptBtn = document.createElement('button');
            promptBtn.type = 'button';
            promptBtn.className = 'plan-prompt';
            promptBtn.setAttribute('aria-label', command + ' \u2014 ' + description);
            promptBtn.addEventListener('click', () => {
                submit_text_to_php(command);
                closeOverlay();
            });

            const commandLine = document.createElement('span');
            commandLine.className = 'command-line';
            commandLine.textContent = command;

            const commandDescription = document.createElement('span');
            commandDescription.className = 'command-description';
            commandDescription.textContent = description;

            promptBtn.appendChild(commandLine);
            promptBtn.appendChild(commandDescription);

            li.appendChild(numberBadge);
            li.appendChild(promptBtn);
            listEl.appendChild(li);
        });
    }

    function openOverlay() {
        overlay.classList.add('open');
        closeBtn.focus();
        document.addEventListener('keydown', handleEscape);
    }

    function closeOverlay() {
        overlay.classList.remove('open');
        triggerBtn.focus();
        document.removeEventListener('keydown', handleEscape);
    }

    function handleEscape(e) {
        if (e.key === 'Escape') {
            closeOverlay();
        }
    }

    triggerBtn.addEventListener('click', openOverlay);
    closeBtn.addEventListener('click', closeOverlay);

    // Clicking the dimmed backdrop itself (not the list or close
    // button) also closes it, matching the language overlay's feel.
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) {
            closeOverlay();
        }
    });

    renderCommands();
});
</script>

<?php
// This is important.
// If this is not done then the session variables will still
// be available even after the tab is closed. By doing this the
// session variables get deleted when the tab is closed.
// You can print out the message history to confirm that the
// session variable has been deleted: print_r($_SESSION['message_history']);

// remove all session variables
session_unset();

// destroy the session
session_destroy();
?>