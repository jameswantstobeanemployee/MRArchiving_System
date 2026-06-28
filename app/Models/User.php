<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = ['name', 'email', 'password', 'role', 'is_active', 'last_login_at'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    public function archivedCharts()
    {
        return $this->hasMany(ArchivedChart::class, 'archived_by');
    }

    public function checkouts()
    {
        return $this->hasMany(CheckoutHistory::class, 'checked_out_by');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function getPreference(string $key, $default = null)
    {
        $pref = $this->preferences()->where('preference_key', $key)->first();
        return $pref ? $pref->preference_value : $default;
    }

    public function setPreference(string $key, $value): void
    {
        $this->preferences()->updateOrCreate(
            ['preference_key' => $key],
            ['preference_value' => $value]
        );
    }

    public function unreadNotificationsCount(): int
    {
        return $this->notifications()->where('is_read', false)->count();
    }
}
