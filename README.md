# Laravel Telegram Logger

Send log messages and application exceptions from Laravel to a Telegram chat or group through the Telegram Bot API.

## Features

- Automatically reports exceptions (by HTTP status code) to Telegram, with request details and a short trace.
- Reports queued jobs that fail after their last attempt.
- `telegramLog()` helper for sending info, alert and error messages.
- `telegram-log:get-updates` command to find your chat id.
- A `telegram` log channel for Laravel's logger.
- Ignore rules for exceptions you don't want in Telegram.
- Duplicate messages are rate limited, and long messages are split to fit Telegram's 4096-character limit.
- Message templates are Blade views you can publish and edit.

## Requirements

- PHP 7.4+
- Laravel 8 – 13

---

## Installation

```bash
composer require c0dem1ner/laravel-telegram-logger
```

The service provider is registered automatically.

Add your bot token and chat id to `.env`. You can get the token from [@BotFather](https://t.me/BotFather).

```
TELEGRAM_BOT_TOKEN=your-telegram-bot-token
TELEGRAM_CHAT_ID=your-telegram-chat-id
```

To find the chat id, set the token first, send your bot a message (or add it to your group or channel), then run:

```bash
php artisan telegram-log:get-updates
```

It lists the chats that messaged the bot in the last 24 hours:

```
+------------+------------+--------+--------------+
| Chat ID    | Type       | Name   | Last message |
+------------+------------+--------+--------------+
| 111222333  | private    | John   | hi           |
| -100222333 | supergroup | Alerts |              |
+------------+------------+--------+--------------+
```

In groups, a bot only sees commands and mentions by default, so send `/start@YourBot` there if the group doesn't show up. The command doesn't work while the bot has a webhook set.

Optionally publish the config file:

```bash
php artisan vendor:publish --tag=telegram-logger-config
```

---

## Configuration

`config/telegram-logger.php`:

| Key | Default | Description |
|---|---|---|
| `token` | `env('TELEGRAM_BOT_TOKEN')` | Bot token. |
| `chat_id` | `env('TELEGRAM_CHAT_ID')` | Chat, group or channel id to send to. |
| `log_errors` | `[500]` | HTTP status codes that are reported automatically. Exceptions without a status code count as `500`. Use `[]` to turn automatic reporting off. |
| `log_failed_jobs` | `true` | Send a message when a queued job fails. See [Failed queue jobs](#failed-queue-jobs). |
| `ignore` | `[]` | Exceptions that are never sent. See [Ignoring Exceptions](#ignoring-exceptions). |
| `max_attempts` | `1` | How many times the same message can be sent within `decay_seconds`. |
| `decay_seconds` | `86400` | Window for `max_attempts` (1 day). |

Messages are always sent with Telegram's HTML parse mode.

---

## Usage

### Automatic exception reporting

When an exception is reported during a web request and its status code is in `log_errors`, an error message is sent with:

- the request method and URL
- the exception class, message, file and line
- the user agent
- the request parameters, without `_token`, `password`, `password_confirmation` and `current_password` (left out if longer than 600 characters)
- the first 3 lines of the trace

Exceptions thrown in console commands are not sent automatically. Queue jobs are covered below.

> **Note:** other sensitive fields (card numbers, API keys, personal data) are sent as they are. Publish the views and edit `types/exception.blade.php` if your forms post such fields.

### Failed queue jobs

When a queued job fails after its last attempt, an error message is sent with:

- the job class, queue, connection and number of attempts
- the exception class, message, file and line
- the first 3 lines of the trace

Retries that are still pending don't send anything. Jobs on the `sync` connection run inside the request, so they are reported as request errors instead. `ignore` rules apply to failed jobs too; use `'request' => '*'` or leave it out, because jobs have no request path. Set `log_failed_jobs` to `false` to turn this off.

### The `telegramLog()` helper

```php
telegramLog()->info('Your message here');
telegramLog()->alert('Your message here');
telegramLog()->error('Your message here');
```

Messages are sent as HTML, so you can use [Telegram's HTML tags](https://core.telegram.org/bots/api#html-style). Escape any user input with `e()`; an unescaped `<` or `&` makes Telegram reject the message.

Each method returns `true` when Telegram accepted the message, and `false` when it wasn't sent: no token or chat id, rate limited, or a Telegram error (which is written to your logs, with the token hidden).

To send to a different bot or chat:

```php
telegramLog()->setToken('other-token')->setChatId('-100123456')->error('Payment failed');
```

### Log channel

Add a channel to `config/logging.php`:

```php
'telegram' => [
    'driver' => 'custom',
    'via' => \C0deM1ner\LaravelTelegramLogger\Logger\TelegramLogger::class,
],
```

That's enough: the channel uses the token and chat id from `config/telegram-logger.php`. Optional keys:

| Key | Default | Description |
|---|---|---|
| `level` | `'debug'` | Lowest log level that is sent. |
| `token` | package `token` | Send with a different bot. |
| `chat_id` | package `chat_id` | Send to a different chat. |
| `type` | `'error'` | Template: `info`, `alert` or `error`. Any other value is an error. |

You can add several Telegram channels with different chats or templates.

```php
Log::channel('telegram')->warning('Disk almost full');
```

Records at or above `level` are sent, HTML-escaped. You can also add the channel to your `stack` channel: an exception that automatic reporting or failed-job reporting already sent is not sent a second time by the channel.

### Rate limiting

- The same message is sent at most `max_attempts` times per `decay_seconds`, which by default means once a day.
- No more than 5 messages are sent per minute in total.

Messages over the limit are dropped. Rate limiting uses your default cache store.

---

## Ignoring Exceptions

Exceptions matching a rule in `ignore` are not sent to Telegram, but still go to your other log channels. Rules apply to automatic reporting and to the log channel (when the record has an `exception` in its context).

```php
// config/telegram-logger.php
'ignore' => [
    [
        'exception' => TypeError::class,   // subclasses match too
        'request' => 'livewire/update',     // request path, "*" wildcards allowed
        'contains' => 'Cannot assign',      // text anywhere in the message, "*" wildcards allowed
    ],
],
```

A rule matches when all its keys match. A missing key means `*`. A plain class name is short for a rule with only `exception`:

```php
'ignore' => [
    \Illuminate\Database\QueryException::class,
],
```

---

## Customizing Messages

Publish the views:

```bash
php artisan vendor:publish --tag=telegram-logger-views
```

Then edit them in `resources/views/vendor/telegram-logger`:

- `messages/info.blade.php`, `messages/alert.blade.php`, `messages/error.blade.php`: the frame around every message (`$appName`, `$message`)
- `types/exception.blade.php`: the body of exception and failed-job reports (`$exception`, `$params`, `$trace`, and `$event` for failed jobs, otherwise `null`)

---

## Testing

Check your bot token and chat id:

```bash
php artisan telegram-log:get-updates      # token works, and which chats the bot can see
php artisan telegram-log:send-test-message
```

The test command exits with an error when the message wasn't sent.

Run the package's own tests and code style check:

```bash
composer test
composer lint
```

---

## License

This package is open-sourced software licensed under the [MIT license](license.md).

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
