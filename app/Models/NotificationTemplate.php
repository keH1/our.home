<?php

namespace App\Models;

use App\Enums\NotificationCategory;
use App\Enums\NotificationType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NotificationTemplate extends Model
{
    protected $fillable = [
        'type',
        'title',
        'text',
        'is_active',
    ];

    protected $casts = [
        'type' => NotificationType::class,
    ];

    public function notifications(): BelongsToMany
    {
        return $this->BelongsToMany(Notification::class);
    }
}
