<?php

namespace App\Models;

use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Claim extends Model
{
    protected $fillable = [
        'category_id',
        'text',
        'is_active',
        'finished_at',
        'is_positive',
        'comment',
        'user_id',
        'type',
        'status',
        'paid_service_id',
    ];

    protected $casts = [
        'type' => ClaimType::class,
        'status' => ClaimStatus::class,
        'is_active' => 'boolean',
        'is_positive' => 'boolean',
        'finished_at' => 'datetime',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClaimCategory::class, 'category_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ClaimMessage::class);
    }

    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }

    public function paidService(): BelongsTo
    {
        return $this->belongsTo(PaidService::class, 'paid_service_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }

    public function reviews(): HasOne
    {
        return $this->hasOne(ClaimReview::class);
    }

}
