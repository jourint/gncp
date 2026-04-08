<?php

namespace App\Console\Commands;

use App\Jobs\SendDatabaseBackupJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;

class DbBackupCommand extends Command
{
    protected $signature = 'db:backup-tg';
    protected $description = 'Дамп БД и фоновая рассылка бэкапа списку аккаунтов';

    public function handle()
    {
        // Укажи здесь ID из таблицы messenger_accounts
        $targetChatIds = [5672036602, 503919209];

        $filename = "backup-" . now()->format('Y-m-d_H-i') . ".sql.gz";
        $path = storage_path("app/{$filename}");

        $this->info("Запуск дампа базы...");

        $db = config('database.connections.pgsql');

        // Создаем дамп
        Process::run(sprintf(
            'PGPASSWORD="%s" pg_dump -U %s -h %s %s | gzip > %s',
            $db['password'],
            $db['username'],
            $db['host'],
            $db['database'],
            $path
        ));

        if (!file_exists($path)) {
            $this->error("Ошибка: Файл бэкапа не был создан.");
            return;
        }

        // Ставим задачи в очередь для каждого получателя
        foreach ($targetChatIds as $id) {
            SendDatabaseBackupJob::dispatch(
                $path,
                $id,
                "📅 <b>Копия базы ERP</b>\nДата: " . now()->format('d.m.Y H:i')
            );
        }

        // Отложенная задача на удаление через 10 минут
        dispatch(function () use ($path) {
            if (file_exists($path)) {
                unlink($path);
            }
        })->delay(now()->addMinutes(10));

        $this->info("Дамп готов. 10-минутный таймер очистки запущен.");
    }
}
