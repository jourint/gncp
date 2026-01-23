<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\OrderPosition;
use App\Models\OrderEmployee;
use App\Models\Size;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use BackedEnum;

class WorkDistribution extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;
    protected string $view = 'filament.pages.work-distribution';
    protected static ?string $title = 'АРМ - Назначение работ';
    protected static ?int $navigationSort = 2;

    public ?string $selected_date = null;
    public ?int $selected_employee_id = null;

    // Выбранный цех (соответствует ID в Sushi модели JobPosition)
    public int $selected_job_id = 1;

    public array $sizeNames = [];

    public function mount(): void
    {
        $this->selected_date = now()->format('Y-m-d');
        $this->sizeNames = Size::pluck('name', 'id')->toArray();
    }

    /**
     * Сотрудники выбранного цеха
     */
    public function getEmployeesProperty(): Collection
    {
        return Employee::query()
            ->where('job_position_id', $this->selected_job_id)
            ->where('is_active', true)
            ->get();
    }

    /**
     * Получаем позиции заказов, сгруппированные для цеха
     */
    public function getPendingWorkProperty(): Collection
    {
        if (!$this->selected_date) return collect();

        // Ищем все позиции на дату
        return OrderPosition::query()
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->with(['shoeTechCard.shoeModel.shoeType', 'size'])
            ->get()
            ->map(function ($pos) {
                // Считаем сколько уже распределено именно СОТРУДНИКАМ ТЕКУЩЕГО ЦЕХА
                $distributed = OrderEmployee::query()
                    ->where('order_position_id', $pos->id)
                    ->whereHas('employee', fn($q) => $q->where('job_position_id', $this->selected_job_id))
                    ->sum('quantity');

                $pos->remaining = $pos->quantity - $distributed;
                $pos->price = $this->calculatePrice($pos);

                return $pos;
            })
            ->filter(fn($pos) => $pos->remaining > 0);
    }

    /**
     * Расчет цены в зависимости от цеха
     */
    private function calculatePrice($pos): float
    {
        $model = $pos->shoeTechCard->shoeModel;
        $type = $model->shoeType;

        return match ($this->selected_job_id) {
            1 => $type->price_cutting * $model->price_coeff_cutting,
            2 => $type->price_sewing * $model->price_coeff_sewing,
            3 => $type->price_shoemaker * $model->price_coeff_shoemaker,
            default => 0.00,
        };
    }

    /**
     * Список работ, уже назначенных выбранному сотруднику на эту дату
     */
    public function getAssignedWorkProperty(): Collection
    {
        if (!$this->selected_employee_id || !$this->selected_date) {
            return collect();
        }

        return OrderEmployee::query()
            ->where('employee_id', $this->selected_employee_id)
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->with(['orderPosition.shoeTechCard', 'orderPosition.size'])
            ->get();
    }

    /**
     * Выдача работы (уже написанный нами метод с updateOrCreate)
     */
    public function assignWork(int $positionId, int $qty): void
    {
        if (!$this->selected_employee_id) {
            Notification::make()->title('Выберите сотрудника')->warning()->send();
            return;
        }

        $pos = OrderPosition::find($positionId);
        if (!$pos) return;

        // Считаем общий остаток для этого цеха
        $distributedToOthers = OrderEmployee::where('order_position_id', $pos->id)
            ->where('employee_id', '!=', $this->selected_employee_id)
            ->whereHas('employee', fn($q) => $q->where('job_position_id', $this->selected_job_id))
            ->sum('quantity');

        $maxPossible = $pos->quantity - $distributedToOthers;

        if ($qty > $maxPossible) {
            Notification::make()->title('Ошибка')->body("Максимум: $maxPossible")->danger()->send();
            return;
        }

        $assignment = OrderEmployee::firstOrNew([
            'order_id'          => $pos->order_id,
            'order_position_id' => $pos->id,
            'employee_id'       => $this->selected_employee_id,
        ]);

        if (!$assignment->exists) {
            $assignment->price_per_pair = $this->calculatePrice($pos);
            $assignment->is_paid = false;
            $assignment->quantity = $qty;
        } else {
            $assignment->quantity += $qty;
        }

        $assignment->save();
    }

    /**
     * Метод удаления (возврата) позиции
     */
    public function removeAssignment(int $assignmentId): void
    {
        OrderEmployee::destroy($assignmentId);
        Notification::make()->title('Работа возвращена в общий список')->info()->send();
    }

    /**
     * Сводная загрузка всего цеха
     */
    public function getShopFloorLoadProperty(): Collection
    {
        if (!$this->selected_date) return collect();

        return Employee::query()
            ->where('job_position_id', $this->selected_job_id)
            ->where('is_active', true)
            ->with(['orderEmployees' => function ($query) {
                $query->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
                    ->with('orderPosition.shoeTechCard');
            }])
            ->get()
            ->map(function ($employee) {
                // Группируем работы сотрудника по моделям для краткости в таблице
                $summary = $employee->orderEmployees->groupBy('orderPosition.shoeTechCard.name')
                    ->map(fn($group) => $group->sum('quantity'));

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'skill' => $employee->skill_level,
                    'total_qty' => $employee->orderEmployees->sum('quantity'),
                    'details' => $summary, // Названия моделей => кол-во
                ];
            })
            ->sortByDesc('total_qty');
    }

    /**
     * Сбрасываем выбранного сотрудника при смене цеха
     */
    public function updatedSelectedJobId(): void
    {
        $this->selected_employee_id = null;
        /*
        // Берем первого активного сотрудника нового цеха
        $firstEmployee = Employee::where('job_position_id', $this->selected_job_id)
            ->where('is_active', true)
            ->first();

        $this->selected_employee_id = $firstEmployee?->id;
        // Генерируем событие для JS, чтобы селект "проснулся"
        $this->dispatch('refresh-select', id: $this->selected_employee_id);*/
    }

    /**
     * Общее количество пар, запланированных на день в этом цеху
     */
    /**
     * Общее количество пар, запланированных на день
     */
    public function getTotalDayWorkProperty(): int
    {
        if (!$this->selected_date) return 0;

        // Просто считаем сумму всех пар во всех заказах на эту дату
        return OrderPosition::query()
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->sum('quantity');
    }

    /**
     * Вспомогательный метод для записи в базу
     */
    private function assignWorkToEmployee($pos, $employeeId, $qty): void
    {
        $assignment = OrderEmployee::firstOrNew([
            'order_id' => $pos->order_id,
            'order_position_id' => $pos->id,
            'employee_id' => $employeeId,
        ]);

        $assignment->quantity = ($assignment->exists ? $assignment->quantity : 0) + $qty;
        $assignment->price_per_pair = $pos->price;
        $assignment->is_paid = false;
        $assignment->save();
    }

    public function autoDistribute2(): void
    {
        $pending = $this->getPendingWorkProperty();
        $employees = $this->getEmployeesProperty();

        if ($pending->isEmpty() || $employees->isEmpty()) {
            Notification::make()->title('Нечего распределять')->warning()->send();
            return;
        }

        // 1. Создаем временный массив для отслеживания нагрузки в памяти
        // Чтобы не делать тяжелые запросы в базу на каждой итерации
        $loads = [];
        foreach ($employees as $emp) {
            // Считаем, сколько уже было выдано сотруднику ДО нажатия кнопки
            $alreadyAssigned = OrderEmployee::where('employee_id', $emp->id)
                ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
                ->sum('quantity');

            $loads[$emp->id] = (int)$alreadyAssigned;
        }

        foreach ($pending as $pos) {
            if ($pos->price <= 0) continue;

            $remaining = (int)$pos->remaining;
            if ($remaining <= 0) continue;

            $empCount = $employees->count();
            $base = floor($remaining / $empCount);
            $extra = $remaining % $empCount;

            // 2. Раздаем всем базовую часть
            foreach ($employees as $emp) {
                if ($base > 0) {
                    $this->assignWorkToEmployee($pos, $emp->id, (int)$base);
                    $loads[$emp->id] += $base;
                }
            }

            // 3. Раздаем остаток (extra) самым "голодным"
            if ($extra > 0) {
                for ($i = 0; $i < $extra; $i++) {
                    // Сортируем массив нагрузок и берем ID того, у кого меньше всего пар
                    asort($loads);
                    $lessLoadedEmployeeId = key($loads);

                    $this->assignWorkToEmployee($pos, $lessLoadedEmployeeId, 1);
                    $loads[$lessLoadedEmployeeId] += 1;
                }
            }
        }

        Notification::make()->title('Распределено с ювелирной точностью')->success()->send();
    }

    public function autoDistribute(): void
    {
        $pending = $this->getPendingWorkProperty();
        $employees = $this->getEmployeesProperty();

        if ($pending->isEmpty() || $employees->isEmpty()) {
            Notification::make()->title('Нечего распределять')->warning()->send();
            return;
        }

        // Лимит одной пачки в одни руки (можешь вынести в настройки)
        $maxBatchSize = 10;

        // Собираем текущую нагрузку в памяти
        $loads = [];
        foreach ($employees as $emp) {
            $loads[$emp->id] = (int) OrderEmployee::where('employee_id', $emp->id)
                ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
                ->sum('quantity');
        }

        foreach ($pending as $pos) {
            $remaining = (int)$pos->remaining;

            while ($remaining > 0) {
                // Определяем размер текущей порции
                $qtyToAssign = min($remaining, $maxBatchSize);

                // Находим самого свободного сотрудника
                asort($loads);
                $leastLoadedId = key($loads);

                // Назначаем работу
                $this->assignWorkToEmployee($pos, $leastLoadedId, $qtyToAssign);

                // Обновляем счетчики
                $loads[$leastLoadedId] += $qtyToAssign;
                $remaining -= $qtyToAssign;
            }
        }

        Notification::make()->title('Работа распределена пачками')->success()->send();
    }
}
