<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shelf extends Model
{
    protected $fillable = ['room_id', 'name', 'code', 'section', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function room()
    {
        return $this->belongsTo(Room::class);
    }

    public function folderBoxes()
    {
        return $this->hasMany(FolderBox::class);
    }

    public function activeFolderBoxes()
    {
        return $this->hasMany(FolderBox::class)->where('is_active', true);
    }
}
