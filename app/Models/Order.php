<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\OrderStatus;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'customer_id',
        'started_at',
        'status',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'date',
            'status' => OrderStatus::class,
            'comment' => 'string',
        ];
    }

    public function syncStatus(): void
    {
        // Если заказ уже завершен или отменен, автоматика не вмешивается
        if (in_array($this->status, [OrderStatus::Completed, OrderStatus::Cancelled])) {
            return;
        }

        // Если уже в процессе, и мы только добавляем сотрудников, то нет смысла перепроверять существование — оно и так true.
        if ($this->status === OrderStatus::Processing) {
            return;
        }

        // Проверяем наличие записей в OrderEmployee через связи
        $hasAssignments = $this->positions()->whereHas('orderEmployees')->exists();
        $newStatus = $hasAssignments ? OrderStatus::Processing : OrderStatus::Pending;
        if ($this->status !== $newStatus) {
            $this->update(['status' => $newStatus]);
        }
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function positions(): HasMany
    {
        return $this->hasMany(OrderPosition::class);
    }

    public function getFullNameAttribute()
    {
        return "№{$this->id} {$this->customer?->name}";
    }
}
