<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LocationHistory extends Model
{
    protected $table = 'location_history';

    protected $fillable = [
        'archived_chart_id', 'from_box_id', 'to_box_id',
        'reason', 'notes', 'moved_by', 'moved_at'
    ];

    protected $casts = ['moved_at' => 'datetime'];

    public function archivedChart()
    {
        return $this->belongsTo(ArchivedChart::class);
    }

    public function fromBox()
    {
        return $this->belongsTo(FolderBox::class, 'from_box_id');
    }

    public function toBox()
    {
        return $this->belongsTo(FolderBox::class, 'to_box_id');
    }

    public function movedBy()
    {
        return $this->belongsTo(User::class, 'moved_by');
    }
}