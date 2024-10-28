<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class WorkerCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function workers(): HasMany
    {
        return $this->hasMany(Worker::class, 'category_id');
    }
}
