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
    protected $fillable = ['number', 'apartment_id', 'union_number', 'gku_id'];

    public function apartment(): hasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function clients(): BelongsToMany
    {
        return $this->belongsToMany(Client::class, 'client_account_personal_number');
    }

    public function counters(): HasMany
    {
        return $this->hasMany(CounterData::class,'union_number');
    }
}
