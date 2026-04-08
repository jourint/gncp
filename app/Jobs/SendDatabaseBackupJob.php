<?php

namespace App\Jobs;

use App\Models\MessengerAccount;
use App\Services\Messenger\MessengerService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendDatabaseBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        protected string $filePath,
        protected int $chatId,
        protected string $caption
    ) {}

    public function handle(MessengerService $messenger): void
    {
        $account = MessengerAccount::where('chat_id', $this->chatId)
            ->where('is_active', true)
            ->first();
        // Проверяем существование аккаунта и самого файла
        if ($account && file_exists($this->filePath)) {
            // Твой MessengerService разрулит отправку через нужный драйвер
            $messenger->driver($account->driver)->sendDocument(
                $account->chat_id,
                $this->filePath,
                $this->caption
            );
        }
    }
}
