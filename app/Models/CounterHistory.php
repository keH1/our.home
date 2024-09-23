<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CounterHistory extends Model
{
    use HasFactory;

    protected $fillable = ['counter_name_id', 'created_at', 'from_1c', 'daily_consumption', 'night_consumption', 'peak_consumption', 'approved'];

    public function counter(): BelongsTo
    {
        return $this->belongsTo(CounterData::class, 'counter_name_id');
    }
}
