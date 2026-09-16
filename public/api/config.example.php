<?php
/**
 * Скопируйте этот файл в config.php и заполните реальными значениями.
 * config.php добавлен в .gitignore и не должен попадать в репозиторий.
 *
 * Как получить токен и chat_id Telegram-бота — см. README.md, раздел
 * «Форма обратной связи → Telegram-бот».
 */

return [
    // Токен бота, полученный у @BotFather
    'telegram_bot_token' => 'TODO_BOT_TOKEN',

    // ID чата/группы, куда будут приходить заявки (узнать у @userinfobot
    // или через https://api.telegram.org/bot<token>/getUpdates)
    'telegram_chat_id' => 'TODO_CHAT_ID',

    // Email для дублирования заявок (используется функция mail())
    'notify_email' => 'TODO@example.com',
    'notify_email_from' => 'noreply@art-lider-remont.ru', // TODO: домен сайта

    // Опционально: секретный ключ Яндекс SmartCaptcha (server-side проверка)
    'smartcaptcha_secret' => '',

    // Максимум заявок с одного IP за окно времени ниже (антиспам)
    'rate_limit_max_requests' => 5,
    'rate_limit_window_seconds' => 3600,
];
