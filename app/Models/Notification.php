<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Notification extends Model
{
    protected $fillable = [
        'type',
        'title',
        'text',
        'category',
        'is_read',
        'action_type',
        'action_value',
        'user_id',
        'date_to'
    ];

    protected $casts = [
        'type' => NotificationType::class,
        'category' => NotificationCategory::class,
        'date_to' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function houses(): BelongsToMany
    {
        return $this->belongsToMany(House::class, 'house_notification');
    }
}
