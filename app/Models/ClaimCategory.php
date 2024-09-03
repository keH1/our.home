<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClaimCategory extends Model
{
    protected $fillable = ['name'];

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
