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

    protected $fillable = ['number', 'house_id', 'personal_number', 'gku_id'];

    public function house(): BelongsTo
    {
        return $this->belongsTo(House::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_apartment');
    }

    public function counterData(): HasMany
    {
        return $this->hasMany(CounterData::class);
    }

    public function account(): belongsTo
    {
        return $this->belongsTo(AccountPersonalNumber::class);
    }
}
