<?php

namespace App\Filament\Pages\WorkDistribution;

use App\Filament\Pages\WorkDistribution\Strategies\ProportionalDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\FixedBatchDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\FairBatchDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\JobBasedBoxDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\ModelPriorityDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\BoxIntegrityDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\BalancedLPTDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\ModelShareDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\SmartBoxPairDistributor;
use App\Filament\Pages\WorkDistribution\Strategies\HybridSmartDistributor;

class DistributeManager
{
    protected static array $registry = [
        'proportional'   => ProportionalDistributor::class,     // Поровну + остатки
        'fixed_batch'    => FixedBatchDistributor::class,       // Строго по 10 пар
        'fair_batch'     => FairBatchDistributor::class,        // Динамические пачки
        'job_box'        => JobBasedBoxDistributor::class,      // Зависит от цеха
        'model_priority' => ModelPriorityDistributor::class,    // Минимум переходов по моделям
        'box_integrity'  => BoxIntegrityDistributor::class,     // Целые ящики (адекватный)
        'balanced_lpt'   => BalancedLPTDistributor::class,      // Баланс нагрузки (LPT)
        'model_share'    => ModelShareDistributor::class,       // По долям моделей
        'smart_pair'     => SmartBoxPairDistributor::class,     // Умные пары 36-37
        'hybrid'         => HybridSmartDistributor::class,      // Гибридный умный
    ];


    public static function getList(): array
    {
        return collect(self::$registry)->mapWithKeys(fn($class, $key) => [
            $key => [
                'instance' => $instance = new $class(),
                'label'    => $instance->getLabel(),
                'icon'     => $instance->getIcon(),
                'confirm'  => $instance->getConfirmText(),
            ]
        ])->toArray();
    }

    public static function make(string $key): DistributionStrategy
    {
        $class = self::$registry[$key] ?? throw new \Exception("Алгоритм [$key] не найден");
        return new $class();
    }
}
