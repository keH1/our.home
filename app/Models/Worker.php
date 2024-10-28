<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category_id',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(WorkerCategory::class, 'category_id');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class, 'worker_id');
    }
}
