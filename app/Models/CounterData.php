<?php

namespace App\Models;

use App\Enums\CounterType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CounterData extends Model
{
    protected $fillable = [
        'name', 'number', 'apartment_id', 'verification_to', 'counter_type', 'counter_seal', 'factory_number','personal_number'
    ];

    protected $casts = [
        'counter_type' => CounterType::class,
    ];

    public function apartment(): BelongsTo
    {
        return $this->belongsTo(Apartment::class);
    }

    public function histories(): HasMany
    {
        return $this->hasMany(CounterHistory::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(AccountPersonalNumber::class, 'counter_data_account_id');
    }

    public function accounts():BelongsTo
    {
        return $this->belongsTo(AccountPersonalNumber::class);
    }
}
