<?php

namespace App\Models;

use App\Enums\CounterType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CounterData extends Model
{
    use HasFactory;

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
        return $this->hasMany(CounterHistory::class,'counter_name_id');
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(AccountPersonalNumber::class, 'counter_data_account_id');
    }

    public function accounts():BelongsTo
    {
        return $this->belongsTo(AccountPersonalNumber::class);
    }

    public function latestConfirmedHistory(): HasOne
    {
        $currentMonthStart = now()->startOfMonth();
        $currentMonthEnd = now()->endOfMonth();
        $previousMonthStart = now()->subMonth()->startOfMonth();
        $previousMonthEnd = now()->subMonth()->endOfMonth();

        return $this->hasOne(CounterHistory::class, 'counter_name_id')->where(
                function ($query) use ($currentMonthStart, $currentMonthEnd, $previousMonthStart, $previousMonthEnd) {
                    $query->where(function ($subQuery) use ($currentMonthStart, $currentMonthEnd) {
                        $subQuery->where('from_1c', false)->where('approved', true)->whereBetween(
                                'last_checked_date',
                                [$currentMonthStart, $currentMonthEnd]
                            );
                    })->orWhere(function ($subQuery) use ($previousMonthStart, $previousMonthEnd) {
                            $subQuery->where('from_1c', true)->whereBetween(
                                    'last_checked_date',
                                    [$previousMonthStart, $previousMonthEnd]
                                );
                        });
                }
            )->orderByRaw(
                "
                    CASE
                        WHEN from_1c = false AND approved = true THEN 0
                        WHEN from_1c = true THEN 1
                        ELSE 2
                    END
                "
            )->latest('last_checked_date');
    }

    public function latestUnconfirmedHistory()
    {
        return $this->hasOne(CounterHistory::class, 'counter_name_id')
                    ->where('approved', false)
                    ->whereBetween('last_checked_date', [
                        now()->subMonth()->startOfMonth(),
                        now()->subMonth()->endOfMonth()
                    ])
                    ->latest('last_checked_date');
    }
}
