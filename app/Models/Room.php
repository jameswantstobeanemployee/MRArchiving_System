<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    protected $fillable = ['name', 'code', 'building', 'floor', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function shelves()
    {
        return $this->hasMany(Shelf::class);
    }

    public function activeShelves()
    {
        return $this->hasMany(Shelf::class)->where('is_active', true);
    }
}
