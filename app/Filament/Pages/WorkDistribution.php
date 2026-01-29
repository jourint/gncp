<?php

namespace App\Filament\Pages;

use App\Models\Employee;
use App\Models\OrderPosition;
use App\Models\OrderEmployee;
use App\Models\Size;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use BackedEnum;
use App\Filament\Pages\WorkDistribution\DistributeManager;

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
            // 1. Жадная загрузка всего дерева связей
            ->with(['shoeTechCard.shoeModel.shoeType', 'size'])
            // 2. ГЕНИАЛЬНЫЙ ХОД: Считаем сумму распределенных пар для текущего цеха одним подзапросом
            ->withSum(['orderEmployees as distributed' => function ($query) {
                $query->whereHas('employee', fn($q) => $q->where('job_position_id', $this->selected_job_id));
            }], 'quantity')
            ->get()
            ->map(function ($pos) {
                // Теперь $pos->distributed уже содержит число из базы
                $pos->remaining = $pos->quantity - ($pos->distributed ?? 0);
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
     * Метод для частичного уменьшения выданной работы
     */
    public function reduceAssignment(int $assignmentId, int $quantity): void
    {
        $assignment = OrderEmployee::find($assignmentId);

        if (!$assignment) {
            Notification::make()->title('Ошибка')->body('Назначение не найдено.')->danger()->send();
            return;
        }

        if ($quantity <= 0) {
            Notification::make()->title('Ошибка')->body('Количество для списания должно быть больше нуля.')->danger()->send();
            return;
        }

        if ($quantity >= $assignment->quantity) {
            // Если списываем всё или больше, то просто удаляем
            $this->removeAssignment($assignmentId);
        } else {
            // Уменьшаем количество
            $assignment->quantity -= $quantity;
            $assignment->save();
            Notification::make()->title('Количество уменьшено')->info()->send();
        }
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
            // Считаем общую загрузку сотрудника на дату одним запросом
            ->withSum(['orderEmployees as total_qty' => function ($query) {
                $query->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date));
            }], 'quantity')
            // Для деталей (группировка по моделям) всё же придется подгрузить связи, 
            // но теперь только для summary
            ->with(['orderEmployees' => function ($query) {
                $query->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
                    ->with('orderPosition.shoeTechCard');
            }])
            ->get()
            ->map(function ($employee) {
                $summary = $employee->orderEmployees->groupBy('orderPosition.shoeTechCard.name')
                    ->map(fn($group) => $group->sum('quantity'));

                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'skill' => $employee->skill_level,
                    'total_qty' => $employee->total_qty ?? 0, // Поле из withSum
                    'details' => $summary,
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
     * Удалить все назначения выбранного сотрудника на текущую дату
     */
    public function clearEmployeeWork(): void
    {
        if (!$this->selected_employee_id || !$this->selected_date) {
            Notification::make()->title('Ошибка')->body('Сотрудник не выбран')->danger()->send();
            return;
        }

        $count = OrderEmployee::query()
            ->where('employee_id', $this->selected_employee_id)
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->delete();

        if ($count > 0) {
            Notification::make()
                ->title('Список очищен')
                ->body("Удалено позиций: $count")
                ->success()
                ->send();
        }
    }

    /**
     * Общее количество пар, запланированных на день в этом цеху
     */
    public function getTotalDayWorkProperty(): int
    {
        if (!$this->selected_date) return 0;

        // Просто считаем сумму всех пар во всех заказах на эту дату
        return OrderPosition::query()
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->sum('quantity');
    }

    public function autoDistribute(string $method): void
    {
        // 1. Проверка на наличие данных (базовая защита)
        $pending = $this->getPendingWorkProperty();
        $employees = $this->getEmployeesProperty();

        if ($pending->isEmpty() || $employees->isEmpty()) {
            Notification::make()
                ->title('Ошибка')
                ->body('Нет свободных позиций или сотрудников для распределения')
                ->danger()
                ->send();
            return;
        }

        // 2. Запуск стратегии через менеджер
        try {
            DistributeManager::make($method)->distribute(
                $pending,
                $employees,
                $this->selected_date
            );

            Notification::make()
                ->title('Успешно')
                ->body('Работа распределена выбранным методом')
                ->success()
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Ошибка алгоритма')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Сводная матрица распределения (План из заказов vs Факт по сотрудникам)
     */
    public function getShopFloorTableDataProperty(): array
    {
        // 1. Базовая проверка: если дата не выбрана, возвращаем пустые массивы
        if (!$this->selected_date) {
            return [
                'employees' => collect(),
                'models' => collect(),
                'orderTotals' => [],
            ];
        }

        // 2. ПОЛНЫЙ ПЛАН: Получаем все позиции заказов на текущую дату из БД.
        // Нам нужны абсолютно все позиции, чтобы видеть "чистый" объем заказа.
        $allPositions = OrderPosition::query()
            ->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
            ->with(['shoeTechCard', 'size'])
            ->get();

        $orderTotals = [];
        $allRowKeys = collect();

        foreach ($allPositions as $pos) {
            $modelName = $pos->shoeTechCard->name ?? '---';
            $sizeName = $pos->size->name ?? '??';
            $key = "{$modelName} | РАЗМЕР: {$sizeName}";

            // Суммируем план из заказа
            $orderTotals[$key] = ($orderTotals[$key] ?? 0) + $pos->quantity;
            $allRowKeys->push($key);
        }

        // Получаем уникальный отсортированный список всех комбинаций Модель+Размер
        $uniqueRows = $allRowKeys->unique()->sort()->values();

        // 3. ФАКТИЧЕСКОЕ РАСПРЕДЕЛЕНИЕ: Получаем сотрудников выбранного цеха
        // и подгружаем только те работы, которые были назначены на эту дату.
        $employeesWithWork = \App\Models\Employee::query()
            ->where('job_position_id', $this->selected_job_id)
            ->where('is_active', true)
            ->with(['orderEmployees' => function ($query) {
                $query->whereHas('order', fn($q) => $q->where('started_at', $this->selected_date))
                    ->with(['orderPosition.shoeTechCard', 'orderPosition.size']);
            }])
            ->get();

        // 4. ФОРМИРОВАНИЕ МАТРИЦЫ: Превращаем коллекцию сотрудников в массив данных для ячеек
        $matrix = $employeesWithWork->map(function ($emp) {
            // Группируем работу конкретного человека по ключу Модель+Размер
            $employeeDetails = $emp->orderEmployees->groupBy(function ($work) {
                $mName = $work->orderPosition->shoeTechCard->name ?? '---';
                $sName = $work->orderPosition->size->name ?? '??';
                return "{$mName} | РАЗМЕР: {$sName}";
            })->map(fn($group) => $group->sum('quantity'));

            return [
                'id' => $emp->id,
                'name' => $emp->name,
                'total_qty' => $emp->orderEmployees->sum('quantity'),
                'matrix_details' => $employeeDetails,
            ];
        });

        return [
            'employees' => $matrix,
            'models' => $uniqueRows,
            'orderTotals' => $orderTotals, // Чистый план из заказов для левой колонки
        ];
    }
}
