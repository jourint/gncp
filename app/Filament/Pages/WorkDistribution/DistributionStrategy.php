<?php

namespace App\Filament\Pages\WorkDistribution;

interface DistributionStrategy
{
    public function getLabel(): string;
    public function getIcon(): string;
    public function getConfirmText(): string;
    public function distribute($pending, $employees, $selectedDate): void;
}
