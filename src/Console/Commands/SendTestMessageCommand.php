<?php

namespace C0deM1ner\LaravelTelegramLogger\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Throwable;

class SendTestMessageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telegram-log:send-test-message';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test message to the Telegram channel';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     * @throws Throwable
     */
    public function handle(): int
    {
        try {
            telegramLog()->info('This is a test message from the console command');

            return self::SUCCESS;
        }catch (Exception $e){
            info('Error sending test message to Telegram: ' . $e->getMessage());
            $this->error($e->getMessage());
        }

        return self::FAILURE;
    }
}
