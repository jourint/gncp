<?php

namespace App\Filament\Pages\ModelBuilder\Traits;

use App\Models\Color;
use App\Models\ShoeSole;
use App\Models\Material;
use App\Models\ShoeTechCard;
use Illuminate\Support\Collection;

trait HasSearchLogic
{
    public function getFilteredColorsProperty(): Collection
    {
        if (!$this->activeModelId) return collect();
        $usedIds = ShoeTechCard::where('shoe_model_id', $this->activeModelId)->pluck('color_id');
        return Color::whereNotIn('id', $usedIds)
            ->when($this->colorSearch, fn($q) => $q->where('name', 'ilike', "%{$this->colorSearch}%"))
            ->limit(10)->get();
    }

    public function getFilteredSolesProperty(): Collection
    {
        return ShoeSole::where('is_active', true)
            ->when($this->soleSearch, fn($q) => $q->where('name', 'ilike', "%{$this->soleSearch}%"))
            ->limit(20)->get();
    }

    public function getFilteredMaterials1Property(): Collection
    {
        return $this->getMaterialSearch($this->mat1Search);
    }
    public function getFilteredMaterials2Property(): Collection
    {
        return $this->getMaterialSearch($this->mat2Search);
    }
    public function getFilteredCompositionMaterialsProperty(): Collection
    {
        return $this->getMaterialSearch($this->compositionSearch);
    }

    private function getMaterialSearch(string $query): Collection
    {
        if (strlen($query) < 2) return collect();
        return Material::where('is_active', true)->where('name', 'ilike', "%{$query}%")->limit(20)->get();
    }
}
