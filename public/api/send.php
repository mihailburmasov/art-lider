<?php
/**
 * Обработчик формы заявки: серверная валидация, антиспам, отправка в
 * Telegram-бот и на email. Ответ — JSON для fetch-запросов и 303-редирект
 * на /spasibo/ для обычного POST без JavaScript.
 */

declare(strict_types=1);

header('X-Content-Type-Options: nosniff');

$configPath = __DIR__ . '/config.php';
$config = is_file($configPath) ? require $configPath : require __DIR__ . '/config.example.php';

$isAjax = (
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
    || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'))
);

function respond(bool $ok, string $message, bool $isAjax, int $statusCode = 200): never
{
    if ($isAjax) {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Fallback без JS: редирект на страницу благодарности либо назад с ошибкой в query-параметре.
    $redirectTo = $ok ? '/spasibo/' : ($_SERVER['HTTP_REFERER'] ?? '/') . (str_contains($_SERVER['HTTP_REFERER'] ?? '', '?') ? '&' : '?') . 'form_error=1';
    header('Location: ' . $redirectTo, true, 303);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(false, 'Неверный метод запроса.', $isAjax, 405);
}

// ---- Rate limit по IP (файловый, простой) ----
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$rateDir = sys_get_temp_dir() . '/art-lider-rate-limit';
if (!is_dir($rateDir)) {
    mkdir($rateDir, 0700, true);
}
$rateFile = $rateDir . '/' . md5($ip) . '.json';
$now = time();
$window = (int) ($config['rate_limit_window_seconds'] ?? 3600);
$maxRequests = (int) ($config['rate_limit_max_requests'] ?? 5);

$attempts = [];
if (is_file($rateFile)) {
    $attempts = json_decode((string) file_get_contents($rateFile), true) ?: [];
}
$attempts = array_values(array_filter($attempts, fn($t) => $t > $now - $window));

if (count($attempts) >= $maxRequests) {
    respond(false, 'Слишком много заявок. Попробуйте позже или позвоните нам напрямую.', $isAjax, 429);
}

// ---- Honeypot ----
$honeypot = trim((string) ($_POST['website'] ?? ''));
if ($honeypot !== '') {
    // Бот — молча "успех", не отправляем никуда.
    respond(true, 'Заявка отправлена.', $isAjax);
}

// ---- Время заполнения формы (антиспам) ----
$renderedAt = (int) ($_POST['rendered_at'] ?? 0);
if ($renderedAt > 0) {
    $elapsedMs = (int) (microtime(true) * 1000) - $renderedAt;
    if ($elapsedMs >= 0 && $elapsedMs < 1500) {
        // Форма отправлена подозрительно быстро — вероятно, бот.
        respond(true, 'Заявка отправлена.', $isAjax);
    }
}

// ---- Валидация полей ----
$name = trim((string) ($_POST['name'] ?? ''));
$phoneRaw = trim((string) ($_POST['phone'] ?? ''));
$phoneDigits = preg_replace('/\D/', '', $phoneRaw) ?? '';
$service = trim((string) ($_POST['service'] ?? ''));
$city = trim((string) ($_POST['city'] ?? ''));
$comment = trim((string) ($_POST['comment'] ?? ''));
$consent = isset($_POST['consent']) && $_POST['consent'] !== '' && $_POST['consent'] !== 'false';
$sourcePage = trim((string) ($_POST['source_page'] ?? ''));

$utm = [];
foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_term', 'utm_content'] as $key) {
    $utm[$key] = trim((string) ($_POST[$key] ?? ''));
}

$errors = [];
if ($name === '' || mb_strlen($name) < 2) {
    $errors[] = 'Укажите имя';
}
if (strlen($phoneDigits) !== 11) {
    $errors[] = 'Укажите корректный номер телефона';
}
if (!$consent) {
    $errors[] = 'Необходимо согласие на обработку персональных данных';
}

if ($errors) {
    respond(false, implode('. ', $errors) . '.', $isAjax, 422);
}

// ---- Фото (опционально) ----
$allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/heic', 'image/heif'];
$maxTotalSize = 10 * 1024 * 1024;
$maxFiles = 5;
$savedFiles = [];

if (!empty($_FILES['photos']) && is_array($_FILES['photos']['name'])) {
    $count = count($_FILES['photos']['name']);
    $totalSize = 0;
    $uploadDir = __DIR__ . '/../uploads/' . date('Y-m');
    if ($count > 0 && !is_dir($uploadDir)) {
        mkdir($uploadDir, 0700, true);
    }

    for ($i = 0; $i < min($count, $maxFiles); $i++) {
        if ($_FILES['photos']['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $tmpName = $_FILES['photos']['tmp_name'][$i];
        $size = (int) $_FILES['photos']['size'][$i];
        $totalSize += $size;
        if ($totalSize > $maxTotalSize) {
            break;
        }
        $mime = mime_content_type($tmpName) ?: '';
        if (!in_array($mime, $allowedMime, true)) {
            continue;
        }
        $ext = match ($mime) {
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/heic', 'image/heif' => 'heic',
            default => 'jpg',
        };
        $destName = bin2hex(random_bytes(8)) . '.' . $ext;
        $destPath = $uploadDir . '/' . $destName;
        if (move_uploaded_file($tmpName, $destPath)) {
            $savedFiles[] = $destPath;
        }
    }
}

// ---- Формирование сообщения ----
$lines = [
    "Новая заявка с сайта",
    "Имя: {$name}",
    "Телефон: +{$phoneDigits}",
];
if ($service !== '') $lines[] = "Услуга: {$service}";
if ($city !== '') $lines[] = "Город: {$city}";
if ($comment !== '') $lines[] = "Комментарий: {$comment}";
if ($sourcePage !== '') $lines[] = "Страница: {$sourcePage}";
$utmLine = implode(', ', array_filter(array_map(
    fn($k, $v) => $v !== '' ? "{$k}={$v}" : null,
    array_keys($utm),
    array_values($utm),
)));
if ($utmLine !== '') $lines[] = "UTM: {$utmLine}";
if ($savedFiles) $lines[] = 'Приложено фото: ' . count($savedFiles);

$messageText = implode("\n", $lines);

// ---- Отправка в Telegram ----
$telegramOk = true;
$telegramToken = $config['telegram_bot_token'] ?? '';
$telegramChatId = $config['telegram_chat_id'] ?? '';

if ($telegramToken && $telegramChatId && !str_starts_with($telegramToken, 'TODO')) {
    $ch = curl_init("https://api.telegram.org/bot{$telegramToken}/sendMessage");
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'chat_id' => $telegramChatId,
            'text' => $messageText,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 8,
    ]);
    $result = curl_exec($ch);
    $telegramOk = $result !== false;
    curl_close($ch);

    foreach ($savedFiles as $filePath) {
        $chPhoto = curl_init("https://api.telegram.org/bot{$telegramToken}/sendPhoto");
        curl_setopt_array($chPhoto, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => [
                'chat_id' => $telegramChatId,
                'photo' => new CURLFile($filePath),
            ],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 15,
        ]);
        curl_exec($chPhoto);
        curl_close($chPhoto);
    }
} else {
    $telegramOk = false;
}

// ---- Дублирование на email ----
$notifyEmail = $config['notify_email'] ?? '';
if ($notifyEmail && !str_starts_with($notifyEmail, 'TODO')) {
    $fromEmail = $config['notify_email_from'] ?? $notifyEmail;
    $headers = "From: {$fromEmail}\r\nContent-Type: text/plain; charset=UTF-8";
    @mail($notifyEmail, 'Новая заявка с сайта Арт Лидер', $messageText, $headers);
}

// ---- Запись попытки для rate limit ----
$attempts[] = $now;
file_put_contents($rateFile, json_encode($attempts));

if (!$telegramToken || str_starts_with((string) $telegramToken, 'TODO')) {
    // Конфигурация ещё не заполнена владельцем — сообщаем корректной ошибкой,
    // но не считаем это виной пользователя.
    respond(false, 'Форма настроена не полностью: заполните public/api/config.php (см. README.md).', $isAjax, 200);
}

if (!$telegramOk) {
    respond(false, 'Не удалось отправить заявку в Telegram. Мы получили её по email, либо позвоните нам напрямую.', $isAjax, 200);
}

respond(true, 'Заявка отправлена.', $isAjax);
