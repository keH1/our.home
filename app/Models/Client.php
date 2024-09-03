<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

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
}
