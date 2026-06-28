<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FolderBox extends Model
{
    protected $fillable = ['shelf_id', 'box_number', 'box_code', 'capacity', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function shelf()
    {
        return $this->belongsTo(Shelf::class);
    }

    public function charts()
    {
        return $this->hasMany(ArchivedChart::class, 'physical_location_id');
    }

    public function activeCharts()
    {
        return $this->hasMany(ArchivedChart::class, 'physical_location_id')
                    ->whereIn('status', ['archived', 'checked_out']);
    }

    public function getCurrentCountAttribute(): int
    {
        return $this->activeCharts()->count();
    }

    public function getFillPercentageAttribute(): float
    {
        if ($this->capacity == 0) return 0;
        return round(($this->current_count / $this->capacity) * 100, 1);
    }

    public function getStatusAttribute(): string
    {
        $pct = $this->fill_percentage;
        $warnThreshold = (int) SystemSetting::getValue('box_warning_threshold', 80);
        $blockThreshold = (int) SystemSetting::getValue('box_block_threshold', 95);

        if ($pct >= $blockThreshold) return 'full';
        if ($pct >= $warnThreshold) return 'warning';
        return 'ok';
    }

    public function canAcceptChart(): bool
    {
        $blockThreshold = (int) SystemSetting::getValue('box_block_threshold', 95);
        return $this->fill_percentage < $blockThreshold;
    }

    public function getLocationLabelAttribute(): string
    {
        $shelf = $this->shelf;
        $room = $shelf->room;
        return "{$room->name} > {$shelf->name} > Box {$this->box_number}";
    }
}
