<?php

namespace App\Services\Messenger\DTO;

enum MessageType
{
    case TEXT;     // Обычное текстовое сообщение
    case COMMAND;  // Команды, начинающиеся с /
    case CALLBACK; // Данные от нажатия кнопок (Inline buttons)
    case UNKNOWN;  // Прочие типы (сервисные сообщения и т.д.)
}
