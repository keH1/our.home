<?php

namespace App\Models;

use App\Enums\PriceType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaidService extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'price_type',
        'category_id',
    ];

    protected $casts = [
        'price_type' => PriceType::class,
        'price' => 'decimal:2',
    ];

    /**
     * Связь с категорией платных услуг.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PaidServiceCategory::class, 'category_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'paid_service_id');
    }
}
