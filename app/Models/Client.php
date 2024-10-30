<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    protected $fillable = ['name', 'phone', 'user_id', 'debt'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class, 'client_apartment');
    }

    public function accounts(): belongsToMany
    {
        return $this->belongsToMany(AccountPersonalNumber::class,'client_account_personal_number');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
