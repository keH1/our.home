<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountPersonalNumber extends Model
{
    use HasFactory;

    public $timestamps;
    protected $fillable = ['number', 'apartment_id', 'els_id', 'gis_id'];

    public function apartment(): BelongsTo
    {
        return $this->BelongsTo(Apartment::class);
    }

    public function user(): BelongsTo
    {
        return $this->BelongsTo(User::class);
    }

    public function counters(): HasMany
    {
        return $this->hasMany(CounterData::class,'account_id');
    }
}
