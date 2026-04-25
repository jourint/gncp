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
        $targetChatIds = [
            5672036602, // 
            503919209
        ];
        $filename = "backup-" . now()->format('Y-m-d_H-i') . ".sql.gz";
        $path = storage_path("app/{$filename}");

        $this->info("Запуск дампа базы...");

        $db = config('database.connections.pgsql');

        // 1. Используем массив для команды, чтобы Laravel сам обработал экранирование
        // 2. Добавляем флаг -w (no-password), так как пароль передаем через переменную окружения
        // 3. Добавляем путь к дампу, если он не в $PATH (опционально)
        $command = sprintf(
            'pg_dump -U %s -h %s -p %s -w %s | gzip > %s',
            $db['username'],
            $db['host'],
            $db['port'] ?? '5432',
            $db['database'],
            escapeshellarg($path)
        );

        $result = Process::env([
            'PGPASSWORD' => $db['password']
        ])->run($command);

        // Проверяем результат
        if (!$result->successful()) {
            $this->error("Ошибка дампа (код {$result->exitCode()}):");
            $this->error($result->errorOutput());
            return;
        }

        // Дополнительная проверка на пустой файл
        if (!file_exists($path) || filesize($path) <= 20) {
            $this->error("Ошибка: Файл создан, но он пустой. Проверьте права пользователя БД.");
            return;
        }

        $this->info("Дамп успешно создан. Размер: " . filesize($path) . " байт.");

        foreach ($targetChatIds as $id) {
            SendDatabaseBackupJob::dispatch(
                $path,
                $id,
                "📅 <b>Копия базы ERP</b>\nДата: " . now()->format('d.m.Y H:i')
            );
        }

        dispatch(function () use ($path) {
            if (file_exists($path)) {
                unlink($path);
            }
        })->delay(now()->addMinutes(10));

        $this->info("Задачи на отправку добавлены в очередь.");
    }
}
