<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Apartment extends Model
{
    use HasFactory;

    protected $fillable = ['number', 'house_id', 'gis_id', 'gis_id', 'apartment_code', 'address'];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

//    public function clients(): BelongsToMany
//    {
//        return $this->belongsToMany(Client::class, 'client_apartment');
//    }

    public function counterData(): HasMany
    {
        return $this->hasMany(CounterData::class);
    }

    public function accounts(): HasMany
    {
        return $this->HasMany(AccountPersonalNumber::class);
    }
}
