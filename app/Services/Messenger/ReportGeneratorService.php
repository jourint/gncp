<?php

namespace App\Services\Messenger;

use App\Models\OrderPosition;
use App\Models\OrderEmployee;
use App\Models\Employee;
use App\Models\Customer;
use App\Enums\JobPosition;
use App\Enums\Unit;


class ReportGeneratorService
{
    public function generateForCustomer(int $customerId, string $date): string
    {
        // Получаем позиции заказов клиента на конкретную дату
        $positions = OrderPosition::query()
            ->whereHas('order', function ($q) use ($customerId, $date) {
                $q->where('customer_id', $customerId)
                    ->whereDate('started_at', $date);
            })
            ->with(['shoeTechCard.shoeModel', 'shoeTechCard.color', 'materialLining.color'])
            ->get();

        if ($positions->isEmpty()) {
            return "На дату $date заказов в производстве не найдено.";
        }

        $report = "<b>📊 Отчет по заказу на $date</b>\n\n";

        // Группируем по техкарте и подкладке, чтобы отчет был компактным
        $grouped = $positions->groupBy(function ($item) {
            return $item->shoe_tech_card_id . '_' . $item->material_lining_id;
        });
        $totalPairs = 0;

        // Генерируем отчет
        foreach ($grouped as $group) {
            $first = $group->first();
            $techCard = $first->shoeTechCard;
            $lining = $first->materialLining;

            // Формируем имя подкладки (эмулируем fullName)
            $liningName = $lining
                ? " ({$lining->name} {$lining->color?->name})"
                : "";

            $report .= "👞 <b>{$techCard->name}</b>{$liningName}\n";

            // Собираем размеры в этой группе
            $sizes = $group->map(fn($p) => "р.{$p->size_id}: <b>{$p->quantity}</b>")->implode(', ');
            $totalQty = $group->sum('quantity');

            $report .= "└ $sizes\n";
            $report .= "  <b>Итого: " . declension_pairs($totalQty) . "</b>\n\n";
            $totalPairs += $totalQty;
        }
        $report .= str_repeat("—", 12) . "\n";
        $report .= "📊 Итого: <b>" . declension_pairs($totalPairs) . "</b>\n";

        return $report;
    }

    // Генерация отчета по выработке сотрудника за день
    public function generateForEmployee(int $employeeId, string $date): string
    {
        $work = OrderEmployee::query()
            ->where('employee_id', $employeeId)
            ->whereHas('order', function ($q) use ($date) {
                $q->whereDate('started_at', $date);
            })
            ->with([
                'orderPosition.shoeTechCard', // Здесь наше название и цвет
            ])
            ->get();

        if ($work->isEmpty()) {
            return "На дату $date работ не найдено.";
        }

        $employee = Employee::find($employeeId);
        $report = "📄 <b>Наряд на заказ от: {$date}</b>\n";
        $report .= "👤 <b>{$employee->full_name}</b>\n\n";

        $totalSum = 0;
        $totalPairs = 0;

        // Группируем по техкарте, чтобы не дублировать названия
        $groupedWork = $work->groupBy('orderPosition.shoe_tech_card_id');

        foreach ($groupedWork as $techCardId => $items) {
            $firstItem = $items->first();
            $techCard = $firstItem->orderPosition->shoeTechCard;
            $price = $firstItem->price_per_pair;

            // Собираем размеры: группируем внутри техкарты по size_id и суммируем quantity
            $sizeSummary = $items->groupBy('orderPosition.size_id')
                ->map(fn($group) => "{$group->first()->orderPosition->size_id}: " . $group->sum('quantity'))
                ->implode(', ');

            $groupQty = $items->sum('quantity');
            $groupSubTotal = $groupQty * $price;

            $report .= "👞 {$techCard->name}\n";
            $report .= "└ {$sizeSummary} = <b>" . declension_pairs($groupQty) . "</b> × {$price} ₽ = <b>" . number_format($groupSubTotal, 0, '.', ' ') . " ₽</b>\n\n";

            $totalSum += $groupSubTotal;
            $totalPairs += $groupQty;
        }

        $report .= str_repeat("—", 12) . "\n";
        $report .= "📊 Итого: <b>" . declension_pairs($totalPairs) . "</b>\n";
        $report .= "💰 Сумма: <b>" . number_format($totalSum, 2, '.', ' ') . " ₽</b>";

        return $report;
    }

    public function generateFullExpeditionReport(string $date): string
    {
        $customers = Customer::whereHas('orders', function ($q) use ($date) {
            $q->whereDate('started_at', $date);
        })->get();

        if ($customers->isEmpty()) return "На $date заказов нет.";

        $report = "📦 <b>УПАКОВКА ЗАКАЗОВ ОТ: {$date}</b>\n";
        $report .= str_repeat("=", 15) . "\n\n";

        $grandTotal = 0;

        foreach ($customers as $customer) {
            $positions = OrderPosition::whereHas('order', function ($q) use ($customer, $date) {
                $q->where('customer_id', $customer->id)
                    ->whereDate('started_at', $date);
            })->with('shoeTechCard')->get();

            $report .= "👤 <b>Клиент: {$customer->name}</b>\n";

            $customerTotal = 0;
            $grouped = $positions->groupBy('shoe_tech_card_id');

            foreach ($grouped as $items) {
                $techCard = $items->first()->shoeTechCard;
                $sizeSummary = $items->groupBy('size_id')
                    ->map(fn($group) => "{$group->first()->size_id}: " . $group->sum('quantity'))
                    ->sortKeys()
                    ->implode(', ');

                $qty = $items->sum('quantity');
                $customerTotal += $qty;

                $report .= "👞 {$techCard->name}\n";
                $report .= "└ {$sizeSummary} = <b>" . declension_pairs($qty) . "</b>\n";
            }

            $report .= "💰 Итого по клиенту: <b>" . declension_pairs($customerTotal) . "</b>\n";
            $report .= str_repeat("-", 10) . "\n\n";
            $grandTotal += $customerTotal;
        }

        $report .= "🚀 <b>ВСЕГО НА ОТГРУЗКУ: " . declension_pairs($grandTotal) . "</b>";

        return $report;
    }

    public function generateAccountingReport(string $date): string
    {
        $work = OrderEmployee::whereHas('order', function ($q) use ($date) {
            $q->whereDate('started_at', $date);
        })->with(['employee'])->get();

        if ($work->isEmpty()) {
            return "📊 <b>Финансовый отчет: {$date}</b>\nДанных за этот день не найдено.";
        }

        $report = "💰 <b>ФИНАНСОВЫЙ ОТЧЕТ: {$date}</b>\n";
        $report .= str_repeat("=", 15) . "\n\n";

        $grandTotalSum = 0;

        // 1. Группируем по должностям (цехам)
        $byPosition = $work->groupBy(fn($item) => $item->employee->job_position_id);

        foreach ($byPosition as $posId => $items) {
            $positionLabel = JobPosition::from($posId)->getLabel();

            $report .= "🏢 <b>{$positionLabel}</b>\n";

            // 2. Группируем внутри цеха по сотрудникам
            $byEmployee = $items->groupBy('employee_id');
            $posSum = 0;
            $posPairs = 0;

            foreach ($byEmployee as $empId => $empWork) {
                $employeeName = $empWork->first()->employee->name;
                $empPairs = $empWork->sum('quantity');
                $empSum = $empWork->sum(fn($i) => $i->quantity * $i->price_per_pair);

                $report .= "👤 {$employeeName}: " . declension_pairs($empPairs) . ", " . number_format($empSum, 0, '.', ' ') . " ₽\n";

                $posSum += $empSum;
                $posPairs += $empPairs;
            }

            // Итог по цеху
            $report .= "├ <b>Всего: " . declension_pairs($posPairs) . "</b>\n";
            $report .= "└ <b>Сумма: " . number_format($posSum, 0, '.', ' ') . " ₽</b>\n\n";

            $grandTotalSum += $posSum;
        }

        $report .= str_repeat("—", 12) . "\n";
        $report .= "💵 Общий фонд: <b>" . number_format($grandTotalSum, 2, '.', ' ') . " ₽</b>";

        return $report;
    }

    public function generateWarehouseMaterialsReport(string $date): string
    {
        // 1. Получаем позиции заказов с глубокой загрузкой связей
        $positions = OrderPosition::whereHas('order', function ($q) use ($date) {
            $q->whereDate('started_at', $date);
        })->with([
            'shoeTechCard.techCardMaterials.material.materialType',
            'shoeTechCard.techCardMaterials.material.color'
        ])->get();

        if ($positions->isEmpty()) {
            return "📦 <b>Склад материалов: {$date}</b>\nЗаказов на эту дату не найдено.";
        }

        $report = "📦 <b>МАТЕРИАЛЫ НА ВЫДАЧУ: {$date}</b>\n";
        $report .= str_repeat("=", 15) . "\n\n";

        $materialSummary = [];

        foreach ($positions as $position) {
            $qtyPairs = $position->quantity;

            // 2. Проходим по материалам техкарты через связь techCardMaterials
            foreach ($position->shoeTechCard->techCardMaterials as $tcm) {
                $material = $tcm->material;

                if (!$material) continue;

                $matId = $material->id;

                if (!isset($materialSummary[$matId])) {
                    $unit = $material->materialType->unit_id;
                    $unitLabel = ($unit instanceof Unit) ? $unit->getLabel() : Unit::from($unit ?? 0)->getLabel();

                    $materialSummary[$matId] = [
                        'full_name' => $material->full_name, // Используем ваш Accessor
                        'unit'      => $unitLabel,
                        'total'     => 0,
                        'stock'     => $material->stock_quantity
                    ];
                }

                // 3. Расход (из промежуточной таблицы) * количество пар
                $materialSummary[$matId]['total'] += ($tcm->quantity * $qtyPairs);
            }
        }

        // 4. Сортировка по алфавиту для удобства кладовщика
        uasort($materialSummary, fn($a, $b) => strcmp($a['full_name'], $b['full_name']));

        // 5. Формирование текста отчета
        foreach ($materialSummary as $data) {
            $total = number_format($data['total'], 2, '.', ' ');
            $stock = number_format($data['stock'], 2, '.', ' ');

            $report .= "🔹 <b>{$data['full_name']}</b>\n";
            $report .= "└ Нужно: <b>{$total} {$data['unit']}</b> (На складе: {$stock})\n\n";
        }

        $report .= str_repeat("—", 12) . "\n";
        $report .= "📝 Всего наименований: <b>" . count($materialSummary) . "</b>";

        return $report;
    }
}
