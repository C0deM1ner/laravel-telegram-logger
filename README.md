# Laravel Telegram Logger

A simple Laravel package to send log messages directly to a Telegram chat or group using the Telegram Bot API.

## Features

- Log messages from Laravel to Telegram in real-time.
- Easy setup with simple configuration.
- Customizable Telegram message format.

---

## Installation

```bash
composer require c0dem1ner/laravel-telegram-logger
```

## Configuration

1. Publish the config file:

```bash
php artisan vendor:publish --tag=telegram-logger-config
```

2. Update the `.env` file:

```
TELEGRAM_BOT_TOKEN=your-telegram-bot-token
TELEGRAM_CHAT_ID=your-telegram-chat-id
```

3. Configure logging in `config/logging.php`:

```php
'channels' => [
    // other channels...

    'telegram' => [
            'driver' => 'custom',
            'level' => env('LOG_LEVEL', 'debug'),
            'via' => \C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger::class,
            'chat_id' => env('TELEGRAM_CHAT_ID', '1234567'),
            'token' => env('TELEGRAM_BOT_TOKEN', '')
            'type' => 'alert', // can be 'alert', 'error', 'info'
        ],
        
        // You can also add multiple Telegram channels
    ],
],
```

---

## Usage

You can log directly to Telegram using:

```php
telegramLog()->info('Your message here');
telegramLog()->alert('Your message here');
telegramLog()->error('Your message here');
```

Or use Laravel’s global logging:

```php
    logger()
        ->channel('telegram')
        ->debug('Your message here');
```

> Any log message matching the configured level will be sent to Telegram.

---

## Customizing Messages

If needed, you can publish and edit the view for Telegram messages:

```bash
php artisan vendor:publish --tag=telegram-logger-views
```

Customize the message structure in `resources/views/vendor/telegram-logger/messages`.

---

## Testing

You can test your configuration by running:

```bash
php artisan telegram-log:send-test-message
```

---

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

---

## Credits

- Developed by C0deM1ner
- Telegram Bot API

---

## Contributions

Contributions are welcome! Please open issues or submit PRs.

---

## Security

If you discover any security-related issues, please email noro.danielyan.1998@gmail.com instead of using the issue tracker.

