<?php

define('QUEUE_DIR', __DIR__);
define('PATH_TO_ALLOWED_TOKENS', 'allowed_tokens.json');
define('UPLOADS_DIR', QUEUE_DIR . '/uploads');
define('MAX_IMAGE_BYTES', 8 * 1024 * 1024); // 8 MB
define('ALLOWED_IMAGE_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

// FILE_UPLOADS_DIR is the drop point for the separate "Upload" button in
// the UI (arbitrary files headed for the Mac's Workspace folder, not
// chat images). It's unrelated to UPLOADS_DIR/incoming.json - nothing
// here touches the chat message queue.
//
// Like UPLOADS_DIR, this folder lives inside the web root (see the note
// above on QUEUE_DIR), so - unlike the image allowlist above - we can't
// just allow "any file": something like a .php file dropped here would
// be directly executable if requested by URL. Rather than restrict file
// *type* (the whole point of this feature is to hand the agent arbitrary
// files), we block extensions that a stock Apache/Dreamhost setup could
// execute or that target server config. This isn't a full sandbox - if
// this domain has CGI handlers enabled for other extensions, add them
// here too.
define('FILE_UPLOADS_DIR', QUEUE_DIR . '/workspace-uploads');

// Valid values for the set_led action's "value" field. Defined here,
// near the other top-of-file config, rather than down in the
// set_led/get_state/report_state section further below - that
// section's handle_set_led() can run (via the dispatch switch) long
// before execution would otherwise reach a define() placed near it.
define('VALID_LED_VALUES', ['on', 'off']);
define('MAX_UPLOAD_FILE_BYTES', 25 * 1024 * 1024); // 25 MB - also bounded by php.ini's upload_max_filesize/post_max_size

// Long-polling config for the status action (see handle_status() far
// below) - defined here rather than next to that function for the same
// reason as VALID_LED_VALUES above: the dispatch switch calls
// handle_status() long before execution would otherwise reach a
// define() placed down near it.
define('STATUS_WAIT_MAX_SECONDS', 20);
define('STATUS_WAIT_CHECK_INTERVAL_USEC', 300000); // 0.3s
define('BLOCKED_FILE_EXTENSIONS', [
    'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar', 'pht',
    'cgi', 'fcgi', 'pl',
    'asp', 'aspx', 'jsp', 'jspx', 'cer', 'shtml',
    'htaccess', 'htpasswd',
]);

$errorLogFile = QUEUE_DIR . '/php_errors.log';

ini_set('display_errors', '0');   // never show raw errors to the client
ini_set('log_errors', '1');
ini_set('error_log', $errorLogFile);
error_reporting(E_ALL);

// Also catch uncaught exceptions and fatal errors, and log them the same way.
set_exception_handler(function ($e) use ($errorLogFile) {
    $line = '[' . date('Y-m-d H:i:s') . "] Uncaught exception: {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}\n";
    file_put_contents($errorLogFile, $line, FILE_APPEND);
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'internal error']);
});

register_shutdown_function(function () use ($errorLogFile) {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        $line = '[' . date('Y-m-d H:i:s') . "] Fatal error: {$error['message']} in {$error['file']}:{$error['line']}\n";
        file_put_contents($errorLogFile, $line, FILE_APPEND);
    }
});

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate');

if (!is_dir(QUEUE_DIR) || !is_writable(QUEUE_DIR)) {
    $line = '[' . date('Y-m-d H:i:s') . '] Queue directory missing or not writable: ' . QUEUE_DIR . "\n";
    // Try to log to a fallback location in case the intended log file itself
    // isn't writable either.
    @file_put_contents(sys_get_temp_dir() . '/chat_demo_fallback.log', $line, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'server misconfigured: queue directory not writable']);
    exit;
}

if (!is_dir(UPLOADS_DIR)) {
    @mkdir(UPLOADS_DIR, 0755, true);
}

if (!is_dir(UPLOADS_DIR) || !is_writable(UPLOADS_DIR)) {
    $line = '[' . date('Y-m-d H:i:s') . '] Uploads directory missing or not writable: ' . UPLOADS_DIR . "\n";
    @file_put_contents(sys_get_temp_dir() . '/chat_demo_fallback.log', $line, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'server misconfigured: uploads directory not writable']);
    exit;
}

if (!is_dir(FILE_UPLOADS_DIR)) {
    @mkdir(FILE_UPLOADS_DIR, 0755, true);
}

if (!is_dir(FILE_UPLOADS_DIR) || !is_writable(FILE_UPLOADS_DIR)) {
    $line = '[' . date('Y-m-d H:i:s') . '] Workspace-uploads directory missing or not writable: ' . FILE_UPLOADS_DIR . "\n";
    @file_put_contents(sys_get_temp_dir() . '/chat_demo_fallback.log', $line, FILE_APPEND);
    http_response_code(500);
    echo json_encode(['error' => 'server misconfigured: workspace-uploads directory not writable']);
    exit;
}

// Helper for atomic writes: write to a temp file, then rename into place,
// so nothing ever reads a half-written file.
function atomic_write(string $path, string $contents): void
{
    $tmpPath = $path . '.tmp';
    file_put_contents($tmpPath, $contents);
    rename($tmpPath, $path);
}

// Resolves a user-supplied filename to a real path strictly inside
// $dir, refusing anything that tries to escape it (e.g. "../../").
// Returns null if the filename is invalid or the resolved file doesn't
// actually live inside $dir.
function resolve_path_in_dir(string $filename, string $dir): ?string
{
    $filename = basename($filename); // strips any directory components
    $path = $dir . '/' . $filename;

    $realDir = realpath($dir);
    $realPath = realpath($path);

    if ($realPath === false || $realDir === false) {
        return null;
    }

    if (strpos($realPath, $realDir) !== 0) {
        return null;
    }

    return $realPath;
}

// Convenience wrapper for the chat-image upload dir specifically.
function resolve_upload_path(string $filename): ?string
{
    return resolve_path_in_dir($filename, UPLOADS_DIR);
}

// Sanitizes an uploader-supplied filename for safe use on disk inside
// $dir, while keeping it recognizable (unlike a fully random name).
// Strips any directory components and anything that isn't
// alphanumeric, dash, or underscore. If a file with the same resulting
// name already exists in $dir, a short numeric suffix is appended so
// nothing gets overwritten.
function sanitize_filename_for_dir(string $originalName, string $dir, string $fallbackBaseName = 'file'): string
{
    $originalName = basename($originalName);

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $baseName = pathinfo($originalName, PATHINFO_FILENAME);

    $baseName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $baseName);
    $baseName = trim($baseName, '_') ?: $fallbackBaseName;

    $filename = $extension !== '' ? "$baseName.$extension" : $baseName;
    $counter = 1;

    while (file_exists($dir . '/' . $filename)) {
        $filename = $extension !== '' ? "{$baseName}-{$counter}.{$extension}" : "{$baseName}-{$counter}";
        $counter++;
    }

    return $filename;
}

// Convenience wrapper for the chat-image upload dir specifically.
function sanitize_upload_filename(string $originalName): string
{
    return sanitize_filename_for_dir($originalName, UPLOADS_DIR, 'image');
}

// =========================================================================
// SECTION: access control
// Every request must include a valid token, either as a header
// ("X-Access-Token") or a query/POST param ("token"). Tokens are looked
// up against the file at PATH_TO_ALLOWED_TOKENS (outside the web root),
// which maps token -> device label. Add or remove a line in that file to
// whitelist or revoke a device.
// =========================================================================
function check_access_token(): void
{
    $headerToken = $_SERVER['HTTP_X_ACCESS_TOKEN'] ?? '';
    $paramToken = $_REQUEST['token'] ?? '';
    $providedToken = $headerToken !== '' ? $headerToken : $paramToken;

    if ($providedToken === '') {
        http_response_code(401);
        echo json_encode(['error' => 'missing access token']);
        exit;
    }

    if (!file_exists(PATH_TO_ALLOWED_TOKENS)) {
        http_response_code(500);
        echo json_encode(['error' => 'server misconfigured: no token whitelist found']);
        exit;
    }

    $allowedTokens = json_decode(file_get_contents(PATH_TO_ALLOWED_TOKENS), true) ?? [];

    if (!array_key_exists($providedToken, $allowedTokens)) {
        http_response_code(401);
        echo json_encode(['error' => 'invalid access token']);
        exit;
    }

    // Token is valid — request proceeds.
}

check_access_token();

$action = $_REQUEST['action'] ?? '';

switch ($action) {
    case 'send_message':
        handle_send_message();
        break;

    case 'status':
        handle_status();
        break;

    case 'get_incoming':
        handle_get_incoming();
        break;

    case 'write_reply':
        handle_write_reply();
        break;

    case 'get_outgoing':
        handle_get_outgoing();
        break;

    case 'reset_status':
        handle_reset_status();
        break;

    case 'upload_image':
        handle_upload_image();
        break;

    case 'get_image':
        handle_get_image();
        break;

    case 'delete_image':
        handle_delete_image();
        break;

    case 'upload_file':
        handle_upload_file();
        break;

    case 'get_uploaded_file':
        handle_get_uploaded_file();
        break;

    case 'delete_uploaded_file':
        handle_delete_uploaded_file();
        break;

    case 'set_led':
        handle_set_led();
        break;

    case 'get_state':
        handle_get_state();
        break;

    case 'report_state':
        handle_report_state();
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'unknown or missing action']);
        break;
}

// =========================================================================
// SECTION: send_message
// Called by the phone when the user sends a chat message. If an image
// was attached, it should already have been uploaded via upload_image
// first, and its filename passed here in the "image" field.
// Overwrites incoming.json and flips status.json to "pending".
// =========================================================================
function handle_send_message(): void
{
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    $imageFilename = isset($_POST['image']) ? trim($_POST['image']) : '';

    if ($text === '' && $imageFilename === '') {
        http_response_code(400);
        echo json_encode(['error' => 'text or image is required']);
        return;
    }

    // If an image was referenced, make sure it's actually a real,
    // already-uploaded file before accepting the message.
    if ($imageFilename !== '' && resolve_upload_path($imageFilename) === null) {
        http_response_code(400);
        echo json_encode(['error' => 'referenced image not found']);
        return;
    }

    $incoming = json_encode([
        'text' => $text,
        'image' => $imageFilename !== '' ? $imageFilename : null,
        'timestamp' => time(),
    ]);
    atomic_write(QUEUE_DIR . '/incoming.json', $incoming);

    $status = json_encode(['status' => 'pending']);
    atomic_write(QUEUE_DIR . '/status.json', $status);

    echo json_encode(['ok' => true]);
}

// =========================================================================
// SECTION: status
// Both the Mac and the phone poll this to check current state:
// "idle" -> nothing waiting, "pending" -> waiting on the Mac,
// "done" -> reply ready for the phone.
//
// Long-polling: pass ?wait=pending (Mac) or ?wait=done (phone) and this
// call will hold the connection open, rechecking every
// STATUS_WAIT_CHECK_INTERVAL_USEC, until either the awaited condition
// becomes true or STATUS_WAIT_MAX_SECONDS elapses - whichever comes
// first. Without ?wait it behaves exactly as before (single
// read-and-return), so tryToken()'s plain status check and any other
// caller that doesn't ask to wait are unaffected.
//
// Kept deliberately short (well under typical shared-hosting execution
// time limits) since the host's own PHP timeout can override anything
// set here, and because a shared plan may only allow a small number of
// concurrent PHP processes - two long-held connections (Mac + phone)
// competing with other requests on a cheap plan is already worth being
// conservative about. A caller that times out just calls again
// immediately; that's the normal long-poll contract, not an error.
// =========================================================================
function handle_status(): void
{
    $wait = $_GET['wait'] ?? '';
    $wait = in_array($wait, ['pending', 'done'], true) ? $wait : null;

    if ($wait !== null) {
        // Best-effort only - some shared hosts (Dreamhost included, on
        // some plans) enforce their own hard execution-time ceiling
        // that this can't override. STATUS_WAIT_MAX_SECONDS is kept
        // well under the usual ~30s default specifically so this call
        // doesn't depend on set_time_limit() actually working.
        @set_time_limit(STATUS_WAIT_MAX_SECONDS + 5);
    }

    $deadline = microtime(true) + STATUS_WAIT_MAX_SECONDS;

    while (true) {
        $statusData = read_status_snapshot();

        if ($wait === null) {
            // Original, non-waiting behavior.
            echo json_encode($statusData);
            return;
        }

        $conditionMet = false;
        if ($wait === 'pending') {
            // The Mac is waiting for *any* reason to wake up: a new
            // chat message, a workspace file drop, or a queued LED
            // command - matching everything main()'s poll loop already
            // acts on.
            $conditionMet = $statusData['status'] === 'pending'
                || !empty($statusData['pending_uploads'])
                || $statusData['pending_led_command'] !== null;
        } elseif ($wait === 'done') {
            // The phone is only ever waiting on the reply.
            $conditionMet = $statusData['status'] === 'done';
        }

        if ($conditionMet || microtime(true) >= $deadline) {
            echo json_encode($statusData);
            return;
        }

        // Release PHP's session lock if one happens to be held (not
        // used today, but harmless/cheap to guard against if that ever
        // changes) so a long-held status request can't block other
        // requests from the same client unnecessarily.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_write_close();
        }

        usleep(STATUS_WAIT_CHECK_INTERVAL_USEC);

        // Some hosts buffer/flush oddly on long-running requests behind
        // a proxy; connection_aborted() lets us bail out promptly if
        // the client already gave up rather than spinning uselessly
        // for the rest of the wait window.
        if (connection_aborted()) {
            return;
        }
    }
}

// Reads status.json plus the two side-channel signals the Mac's poll
// loop also cares about, in one place, so both the waiting and
// non-waiting paths above build the exact same shape of response.
function read_status_snapshot(): array
{
    $statusFile = QUEUE_DIR . '/status.json';

    $statusData = null;
    if (file_exists($statusFile)) {
        $statusData = json_decode(file_get_contents($statusFile), true);
    }
    if (!is_array($statusData)) {
        $statusData = ['status' => 'idle'];
    }

    // Folded into the same poll the Mac already does, so picking up
    // newly-uploaded workspace files doesn't need its own separate
    // polling loop or HTTP round trip.
    $statusData['pending_uploads'] = list_pending_uploads();

    // Same trick for the control panel's pending LED command, if any -
    // see the set_led/report_state section below. Null when there's
    // nothing queued.
    $statusData['pending_led_command'] = read_pending_led_command();

    return $statusData;
}

// Lists filenames currently sitting in FILE_UPLOADS_DIR, waiting for
// the Mac to claim them.
function list_pending_uploads(): array
{
    $files = [];
    foreach ((scandir(FILE_UPLOADS_DIR) ?: []) as $entry) {
        if ($entry === '.' || $entry === '..') {
            continue;
        }
        if (is_file(FILE_UPLOADS_DIR . '/' . $entry)) {
            $files[] = $entry;
        }
    }
    sort($files);
    return $files;
}

// =========================================================================
// SECTION: get_incoming
// Called by the Mac's polling script to fetch the message it needs
// to process.
// =========================================================================
function handle_get_incoming(): void
{
    $incomingFile = QUEUE_DIR . '/incoming.json';

    if (!file_exists($incomingFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'no incoming message']);
        return;
    }

    echo file_get_contents($incomingFile);
}

// =========================================================================
// SECTION: write_reply
// Called by the Mac's polling script once it has generated a reply.
// Overwrites outgoing.json and flips status.json to "done". The "image"
// field is optional - if present, it must already be a real file in
// UPLOADS_DIR (the Mac uploads it first via upload_image, the same way
// the phone does for outgoing messages - see handle_send_message above).
// "num_ctx" and "context_size" are also optional - mac_poller.py only
// sends them when the reply came out of a normal model turn (not a local
// /status-style command, and not an error path), so the phone's UI knows
// to hide the token-usage caption when there's nothing meaningful to show.
// =========================================================================
function handle_write_reply(): void
{
    $text = isset($_POST['text']) ? trim($_POST['text']) : '';
    $imageFilename = isset($_POST['image']) ? trim($_POST['image']) : '';
    $numCtx = isset($_POST['num_ctx']) && $_POST['num_ctx'] !== '' ? (int) $_POST['num_ctx'] : null;
    $contextSize = isset($_POST['context_size']) && $_POST['context_size'] !== '' ? (int) $_POST['context_size'] : null;

    if ($text === '' && $imageFilename === '') {
        http_response_code(400);
        echo json_encode(['error' => 'text or image is required']);
        return;
    }

    if ($imageFilename !== '' && resolve_upload_path($imageFilename) === null) {
        http_response_code(400);
        echo json_encode(['error' => 'referenced image not found']);
        return;
    }

    $outgoing = json_encode([
        'text' => $text,
        'image' => $imageFilename !== '' ? $imageFilename : null,
        'num_ctx' => $numCtx,
        'context_size' => $contextSize,
        'timestamp' => time(),
    ]);
    atomic_write(QUEUE_DIR . '/outgoing.json', $outgoing);

    $status = json_encode(['status' => 'done']);
    atomic_write(QUEUE_DIR . '/status.json', $status);

    echo json_encode(['ok' => true]);
}

// =========================================================================
// SECTION: get_outgoing
// Called by the phone to fetch the Mac's reply once status is "done".
// =========================================================================
function handle_get_outgoing(): void
{
    $outgoingFile = QUEUE_DIR . '/outgoing.json';

    if (!file_exists($outgoingFile)) {
        http_response_code(404);
        echo json_encode(['error' => 'no reply yet']);
        return;
    }

    echo file_get_contents($outgoingFile);
}

// =========================================================================
// SECTION: reset_status
// Called by the phone after displaying the reply, so the conversation
// is ready to accept a new message.
// =========================================================================
function handle_reset_status(): void
{
    $status = json_encode(['status' => 'idle']);
    atomic_write(QUEUE_DIR . '/status.json', $status);

    echo json_encode(['ok' => true]);
}

// =========================================================================
// SECTION: upload_image
// Called by the phone (or laptop) to upload an image ahead of sending
// a message. Expects a multipart/form-data POST with a file field named
// "image". Saves it under UPLOADS_DIR using the original filename
// (sanitized, with a numeric suffix added on collision) and returns that
// filename — the caller passes it back in send_message's "image" field.
// =========================================================================
function handle_upload_image(): void
{
    if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'no image uploaded, or upload failed']);
        return;
    }

    $file = $_FILES['image'];

    if ($file['size'] > MAX_IMAGE_BYTES) {
        http_response_code(400);
        echo json_encode(['error' => 'image too large (max ' . (MAX_IMAGE_BYTES / 1024 / 1024) . 'MB)']);
        return;
    }

    $originalExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($originalExtension, ALLOWED_IMAGE_EXTENSIONS, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'unsupported image type']);
        return;
    }

    // Keeps the original filename (sanitized) rather than a random one,
    // so what's stored stays recognizable. Collisions get a numeric
    // suffix rather than overwriting an existing file.
    $filename = sanitize_upload_filename($file['name']);
    $destination = UPLOADS_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'failed to save uploaded image']);
        return;
    }

    echo json_encode(['ok' => true, 'filename' => $filename]);
}

// =========================================================================
// SECTION: get_image
// Called by the Mac's polling script to download an image referenced in
// the incoming message.
// =========================================================================
function handle_get_image(): void
{
    $filename = $_GET['filename'] ?? '';
    $path = $filename !== '' ? resolve_upload_path($filename) : null;

    if ($path === null || !file_exists($path)) {
        http_response_code(404);
        echo json_encode(['error' => 'image not found']);
        return;
    }

    $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
    $mimeTypes = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
    ];

    // Overrides the default "Content-Type: application/json" header set earlier.
    header('Content-Type: ' . ($mimeTypes[$extension] ?? 'application/octet-stream'));
    readfile($path);
}

// =========================================================================
// SECTION: delete_image
// Called by the Mac's polling script once it has downloaded an image,
// so uploaded files don't accumulate on the server.
// =========================================================================
function handle_delete_image(): void
{
    $filename = $_POST['filename'] ?? '';
    $path = $filename !== '' ? resolve_upload_path($filename) : null;

    if ($path === null || !file_exists($path)) {
        // Already gone, or never existed — treat as success either way.
        echo json_encode(['ok' => true]);
        return;
    }

    unlink($path);
    echo json_encode(['ok' => true]);
}

// =========================================================================
// SECTION: upload_file
// Called by the UI's "Upload" button to silently drop an arbitrary file
// into the Mac's Workspace folder. Unlike upload_image, this is NOT part
// of the chat flow: it doesn't touch incoming.json/status.json's
// pending/done state and doesn't create a message the agent sees. It
// just lands in FILE_UPLOADS_DIR, where handle_status() reports it and
// mac_poller.py picks it up on its next poll.
// Expects a multipart/form-data POST with a file field named "file".
// =========================================================================
function handle_upload_file(): void
{
    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'no file uploaded, or upload failed']);
        return;
    }

    $file = $_FILES['file'];

    if ($file['size'] > MAX_UPLOAD_FILE_BYTES) {
        http_response_code(400);
        echo json_encode(['error' => 'file too large (max ' . (MAX_UPLOAD_FILE_BYTES / 1024 / 1024) . 'MB)']);
        return;
    }

    $originalName = $file['name'];

    // Guards against uploading a literal ".htaccess"/".htpasswd" (which
    // has no "extension" in PHP's eyes, since pathinfo treats the part
    // before the first dot as empty) as well as anything with a
    // blocked extension.
    if (in_array(strtolower(basename($originalName)), ['.htaccess', '.htpasswd'], true)) {
        http_response_code(400);
        echo json_encode(['error' => 'this file name is not allowed']);
        return;
    }

    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (in_array($extension, BLOCKED_FILE_EXTENSIONS, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'this file type is not allowed']);
        return;
    }

    $filename = sanitize_filename_for_dir($originalName, FILE_UPLOADS_DIR);
    $destination = FILE_UPLOADS_DIR . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(['error' => 'failed to save uploaded file']);
        return;
    }

    echo json_encode(['ok' => true, 'filename' => $filename]);
}

// =========================================================================
// SECTION: get_uploaded_file
// Called by the Mac's polling script to download a file it saw listed
// in handle_status()'s pending_uploads.
// =========================================================================
function handle_get_uploaded_file(): void
{
    $filename = $_GET['filename'] ?? '';
    $path = $filename !== '' ? resolve_path_in_dir($filename, FILE_UPLOADS_DIR) : null;

    if ($path === null || !file_exists($path)) {
        http_response_code(404);
        echo json_encode(['error' => 'file not found']);
        return;
    }

    // Generic download - unlike get_image, this isn't rendered inline
    // anywhere, so there's no need to guess a specific MIME type.
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($path) . '"');
    readfile($path);
}

// =========================================================================
// SECTION: delete_uploaded_file
// Called by the Mac's polling script once it has downloaded a file into
// Workspace, so uploads don't pile up on Dreamhost.
// =========================================================================
function handle_delete_uploaded_file(): void
{
    $filename = $_POST['filename'] ?? '';
    $path = $filename !== '' ? resolve_path_in_dir($filename, FILE_UPLOADS_DIR) : null;

    if ($path === null || !file_exists($path)) {
        // Already gone, or never existed - treat as success either way.
        echo json_encode(['ok' => true]);
        return;
    }

    unlink($path);
    echo json_encode(['ok' => true]);
}

// =========================================================================
// SECTION: set_led / get_state / report_state
// The control panel's LED toggle, kept completely separate from the
// chat/LLM pipeline above. Two small JSON files live in QUEUE_DIR:
//
//   led_command.json  - a pending desired change, written by set_led,
//                        read (and cleared) by the Mac via the
//                        pending_led_command field folded into
//                        handle_status() above.
//   device_state.json - the last CONFIRMED state, written only by the
//                        Mac (via report_state) once it has actually
//                        driven the hardware. get_state only ever
//                        returns this file's contents - never the
//                        pending command - so callers always see
//                        confirmed state, not an intent.
//
// device_state.json is a JSON object rather than a single led=on/off
// flag so more fields (more devices, sensor readings, etc.) can be
// added later without another migration.
//
// VALID_LED_VALUES itself is defined up in the configuration section
// near the top of this file (with QUEUE_DIR etc.) rather than here,
// since handle_set_led() below can be reached via the dispatch switch
// long before execution would otherwise reach this point in the file.
// =========================================================================

// Reads led_command.json, if present. Returns null if there's nothing
// queued (the normal case - most polls have no pending command).
function read_pending_led_command(): ?array
{
    $path = QUEUE_DIR . '/led_command.json';
    if (!file_exists($path)) {
        return null;
    }
    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}

// Called by the control panel when the user taps the LED button.
// Just queues the desired value - it does NOT touch device_state.json,
// since that file is reserved for CONFIRMED state. The Mac picks this
// up on its next poll (see pending_led_command in handle_status), acts
// on it, and reports the outcome via report_state.
function handle_set_led(): void
{
    $value = isset($_POST['value']) ? trim($_POST['value']) : '';

    if (!in_array($value, VALID_LED_VALUES, true)) {
        http_response_code(400);
        echo json_encode(['error' => 'value must be "on" or "off"']);
        return;
    }

    $command = json_encode(['led' => $value, 'timestamp' => time()]);
    atomic_write(QUEUE_DIR . '/led_command.json', $command);

    echo json_encode(['ok' => true]);
}

// Called by the control panel (on load, and while polling for updates)
// and by the Mac (to inject current state into the LLM's prompt).
// Always returns the last-confirmed state, never the pending command -
// if nothing has ever been reported yet, every field just comes back
// null so the caller can show "unknown" rather than guessing.
function handle_get_state(): void
{
    $path = QUEUE_DIR . '/device_state.json';

    if (!file_exists($path)) {
        echo json_encode(['led' => null]);
        return;
    }

    echo file_get_contents($path);
}

// Called by the Mac's polling script once it has actually driven the
// hardware (successfully or not) in response to a pending_led_command,
// and also after any LLM-driven switch_led_on/switch_led_off tool call
// - both paths funnel through this same endpoint, so device_state.json
// always reflects the most recent confirmed change regardless of which
// side triggered it. Overwrites device_state.json wholesale (the Mac
// sends the full current state object each time, not a partial patch)
// and clears led_command.json so the same command isn't re-applied on
// the next poll.
function handle_report_state(): void
{
    $stateJson = $_POST['state'] ?? '';

    $decoded = json_decode($stateJson, true);
    if (!is_array($decoded)) {
        http_response_code(400);
        echo json_encode(['error' => 'state must be a valid JSON object']);
        return;
    }

    atomic_write(QUEUE_DIR . '/device_state.json', json_encode($decoded));

    $commandPath = QUEUE_DIR . '/led_command.json';
    if (file_exists($commandPath)) {
        unlink($commandPath);
    }

    echo json_encode(['ok' => true]);
}
