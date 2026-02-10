<?php

namespace App\Services\Messenger\DTO;

class ReplyKeyboard
{
    protected array $rows = [];

    /**
     * Добавить строку кнопок (в одной строке может быть несколько кнопок)
     * @param array $buttons ['Текст кнопки', 'Другой текст']
     */
    public function addRow(array $buttons): self
    {
        $this->rows[] = $buttons;
        return $this;
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
