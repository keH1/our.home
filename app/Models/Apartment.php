<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Apartment extends Model
{
    protected $fillable = ['number', 'house_id', 'account_number', 'account_id', 'gku_id'];

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
}
