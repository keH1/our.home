<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ClaimMessage extends Model
{
    protected $fillable = ['text', 'claim_id'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
    public function files(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable');
    }
}
