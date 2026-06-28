<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'is_read', 'sent_at', 'delivered_via', 'related_id', 'related_type'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public static function send(int $userId, string $type, string $title, string $message, string $via = 'dashboard', ?int $relatedId = null, ?string $relatedType = null): self
    {
        return self::create([
            'user_id'      => $userId,
            'type'         => $type,
            'title'        => $title,
            'message'      => $message,
            'is_read'      => false,
            'sent_at'      => now(),
            'delivered_via'=> $via,
            'related_id'   => $relatedId,
            'related_type' => $relatedType,
        ]);
    }

    public static function sendToAll(string $type, string $title, string $message, string $via = 'dashboard'): void
    {
        User::where('is_active', true)->each(function ($user) use ($type, $title, $message, $via) {
            self::send($user->id, $type, $title, $message, $via);
        });
    }

    public static function sendToAdmins(string $type, string $title, string $message, string $via = 'both'): void
    {
        User::where('role', 'admin')->where('is_active', true)->each(function ($user) use ($type, $title, $message, $via) {
            self::send($user->id, $type, $title, $message, $via);
        });
    }
}
