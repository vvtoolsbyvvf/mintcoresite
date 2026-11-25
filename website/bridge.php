<?php
declare(strict_types=1);

const MAX_BODY_BYTES = 1048576;
const MAX_EVENTS = 500;
const MAX_COMMANDS = 100;

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: no-referrer');
header('Cache-Control: no-store');

$forwardedProto = strtolower(trim((string)($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')));
$isHttps = (isset($_SERVER['HTTPS']) && strtolower((string)$_SERVER['HTTPS']) !== 'off')
    || $forwardedProto === 'https';
$isLocalRequest = in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true);
if (!$isHttps && !$isLocalRequest) {
    send_json(400, ['error' => 'HTTPS is required.']);
}
if ($isHttps) {
    header('Strict-Transport-Security: max-age=31536000');
}

$settings = require __DIR__ . '/bridge-config.php';
if (!is_array($settings)) {
    send_json(500, ['error' => 'Relay configuration is invalid.']);
}

$action = (string)($_GET['action'] ?? '');
if ($action === 'plugin') {
    handle_plugin_request($settings);
}

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isHttps,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if ($action === 'login') {
    handle_login($settings);
}

if (empty($_SESSION['mintcore_authenticated'])) {
    send_json(401, ['error' => 'Zaloguj sie ponownie.']);
}

if ($action === 'state' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    handle_state();
}

if ($action === 'command' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_command();
}

send_json(404, ['error' => 'Unknown relay action.']);

function send_json(int $status, array $data): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

function handle_login(array $settings): never
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        send_json(405, ['error' => 'Use POST to log in.']);
    }

    $now = time();
    $windowStart = (int)($_SESSION['login_window'] ?? 0);
    if ($now - $windowStart > 600) {
        $_SESSION['login_window'] = $now;
        $_SESSION['login_attempts'] = 0;
    }
    if ((int)($_SESSION['login_attempts'] ?? 0) >= 8) {
        send_json(429, ['error' => 'Za duzo prob. Sprobuj ponownie za 10 minut.']);
    }
    $_SESSION['login_attempts'] = (int)($_SESSION['login_attempts'] ?? 0) + 1;

    $expected = (string)($settings['dashboard_password'] ?? '');
    $supplied = (string)($_POST['password'] ?? '');
    if (strlen($expected) < 16 || str_starts_with($expected, 'CHANGE_THIS_') || !hash_equals($expected, $supplied)) {
        send_json(401, ['error' => 'Nieprawidlowe haslo panelu.']);
    }

    session_regenerate_id(true);
    $_SESSION['mintcore_authenticated'] = true;
    $_SESSION['login_attempts'] = 0;
    $_SESSION['login_window'] = $now;
    send_json(200, ['ok' => true]);
}

function handle_state(): never
{
    $watch = (string)($_GET['watch'] ?? '');
    if ($watch !== '' && !valid_uuid($watch)) {
        send_json(400, ['error' => 'Invalid player id.']);
    }

    $snapshot = update_store(static function (array &$store) use ($watch): array {
        $store['watch'] = $watch === '' ? null : $watch;
        return [
            'online' => (int)($store['state']['online'] ?? 0),
            'players' => $store['state']['players'] ?? [],
            'gui' => $store['state']['gui'] ?? null,
            'events' => $store['events'] ?? [],
        ];
    });

    send_json(200, $snapshot);
}

function handle_command(): never
{
    $command = trim((string)($_POST['command'] ?? ''));
    $player = trim((string)($_POST['player'] ?? ''));
    if (str_starts_with($command, '/')) {
        $command = substr($command, 1);
    }
    if ($command === '' || strlen($command) > 1024 || preg_match('/[\r\n\x00]/', $command) === 1) {
        send_json(400, ['error' => 'Wpisz jedna komende do 1024 znakow.']);
    }
    if ($player !== '' && !valid_uuid($player)) {
        send_json(400, ['error' => 'Invalid player id.']);
    }

    $id = bin2hex(random_bytes(16));
    $queued = update_store(static function (array &$store) use ($id, $command, $player): bool {
        if (count($store['commands']) >= MAX_COMMANDS) {
            return false;
        }
        $store['commands'][] = [
            'id' => $id,
            'command' => $command,
            'player' => $player === '' ? null : $player,
            'state' => 'pending',
        ];
        return true;
    });
    if (!$queued) {
        send_json(503, ['error' => 'Command queue is full. Try again shortly.']);
    }

    send_json(200, ['ok' => true, 'message' => 'Komenda dodana do kolejki serwera.']);
}

function handle_plugin_request(array $settings): never
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit;
    }

    $expected = (string)($settings['bridge_secret'] ?? '');
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    $supplied = (string)($_SERVER['HTTP_X_MINTCORE_RELAY_KEY'] ?? $headers['X-MintCore-Relay-Key'] ?? '');
    if (strlen($expected) < 64 || str_starts_with($expected, 'REPLACE_') || !hash_equals($expected, $supplied)) {
        http_response_code(401);
        exit;
    }

    $length = (int)($_SERVER['CONTENT_LENGTH'] ?? 0);
    if ($length > MAX_BODY_BYTES) {
        http_response_code(413);
        exit;
    }
    $raw = file_get_contents('php://input', false, null, 0, MAX_BODY_BYTES + 1);
    if ($raw === false || strlen($raw) > MAX_BODY_BYTES) {
        http_response_code(413);
        exit;
    }
    $payload = json_decode($raw, true);
    if (!is_array($payload) || !is_array($payload['state'] ?? null) || !is_array($payload['events'] ?? null)) {
        http_response_code(400);
        exit;
    }

    $nextCommand = update_store(static function (array &$store) use ($payload): ?array {
        $incoming = $payload['state'];
        $store['state'] = [
            'online' => max(0, min(1000, (int)($incoming['online'] ?? 0))),
            'players' => is_array($incoming['players'] ?? null) ? array_slice($incoming['players'], 0, 1000) : [],
            'gui' => is_array($incoming['gui'] ?? null) ? $incoming['gui'] : null,
        ];

        $knownIds = [];
        foreach ($store['events'] as $event) {
            if (is_array($event) && isset($event['id'])) {
                $knownIds[(string)$event['id']] = true;
            }
        }
        foreach (array_slice($payload['events'], 0, 100) as $event) {
            if (!is_array($event) || !isset($event['id']) || isset($knownIds[(string)$event['id']])) {
                continue;
            }
            $store['events'][] = $event;
            $knownIds[(string)$event['id']] = true;
        }
        $store['events'] = array_slice($store['events'], -MAX_EVENTS);

        foreach ($store['commands'] as $index => $command) {
            if (($command['state'] ?? '') === 'pending') {
                $store['commands'] = array_values(array_filter(
                    $store['commands'],
                    static fn(array $entry): bool => ($entry['id'] ?? '') !== ($command['id'] ?? '')
                ));
                return $command;
            }
        }
        return null;
    });

    $watch = update_store(static fn(array &$store): ?string => isset($store['watch']) ? (string)$store['watch'] : null);
    $fields = [
        $watch ?? '-',
        $nextCommand['id'] ?? '-',
        $nextCommand['player'] ?? '-',
        isset($nextCommand['command']) ? base64_encode((string)$nextCommand['command']) : '-',
    ];
    header('Content-Type: text/plain; charset=utf-8');
    echo implode("\t", $fields);
    exit;
}

function update_store(callable $callback): mixed
{
    $configuredDirectory = getenv('MINTCORE_STORAGE_DIR');
    $directory = $configuredDirectory !== false && $configuredDirectory !== ''
        ? $configuredDirectory
        : (is_dir('/data') ? '/data' : __DIR__ . '/storage');
    if (!is_dir($directory) && !mkdir($directory, 0700, true) && !is_dir($directory)) {
        send_json(500, ['error' => 'Relay storage is not writable.']);
    }
    if (!is_file($directory . '/.htaccess')
        && file_put_contents($directory . '/.htaccess', "Require all denied\n", LOCK_EX) === false) {
        send_json(500, ['error' => 'Could not protect relay storage.']);
    }
    $path = $directory . '/state.json';
    $file = fopen($path, 'c+');
    if ($file === false || !flock($file, LOCK_EX)) {
        send_json(500, ['error' => 'Could not open relay storage.']);
    }

    try {
        rewind($file);
        $raw = stream_get_contents($file);
        $store = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        if (!is_array($store)) {
            $store = [
                'state' => ['online' => 0, 'players' => [], 'gui' => null],
                'events' => [],
                'commands' => [],
                'watch' => null,
            ];
        }
        $store += [
            'state' => ['online' => 0, 'players' => [], 'gui' => null],
            'events' => [],
            'commands' => [],
            'watch' => null,
        ];
        if (!is_array($store['events'])) {
            $store['events'] = [];
        }
        if (!is_array($store['commands'])) {
            $store['commands'] = [];
        }
        $result = $callback($store);
        ftruncate($file, 0);
        rewind($file);
        $encoded = json_encode($store, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($encoded === false || fwrite($file, $encoded) !== strlen($encoded) || !fflush($file)) {
            throw new RuntimeException('Could not write relay storage.');
        }
        return $result;
    } finally {
        flock($file, LOCK_UN);
        fclose($file);
    }
}

function valid_uuid(string $value): bool
{
    return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i', $value) === 1;
}
