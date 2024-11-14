<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class House extends Model
{
    use HasFactory;

    protected $fillable = ['city', 'street', 'number', 'building'];

    public function apartments(): HasMany
    {
        return $this->hasMany(Apartment::class);
    }

    public function notifications(): BelongsToMany
    {
        return $this->belongsToMany(Notification::class, 'house_notification');
    }
}
