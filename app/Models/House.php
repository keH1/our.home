<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    protected $fillable = ['city', 'street', 'number', 'building'];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }
}
