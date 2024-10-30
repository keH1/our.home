<?php

namespace App\Models;

use App\Enums\ClaimPriority;
use App\Enums\ClaimStatus;
use App\Enums\ClaimType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Claim extends Model
{
    use Searchable;

    protected $fillable = [
        'title',
        'type',
        'status',
        'client_id',
        'category_id',
        'paid_service_id',
        'is_paid',
        'expectation_date',
        'text',
        'is_active',
        'finished_at',
        'rating',
        'comment',
        'priority',
        'worker_id',
    ];

    protected $casts = [
        'type' => ClaimType::class,
        'status' => ClaimStatus::class,
        'priority' => ClaimPriority::class,
        'is_active' => 'boolean',
        'is_paid' => 'boolean',
        'finished_at' => 'datetime',
        'expectation_date' => 'date',
    ];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    #[SearchUsingFullText(['title', 'text'])]
    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'text' => $this->text,
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ClaimCategory::class, 'category_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
