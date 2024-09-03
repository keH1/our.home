<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounterHistory extends Model
{
    protected $fillable = ['counter_name_id', 'created_at', 'from_1c', 'daily_consumption', 'night_consumption', 'peak_consumption'];

    public function counter(): BelongsTo
    {
        return $this->belongsTo(CounterData::class, 'counter_name_id');
    }
}
