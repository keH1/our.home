<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClaimMessage extends Model
{
    protected $fillable = ['text', 'claim_id'];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(Claim::class);
    }
}
