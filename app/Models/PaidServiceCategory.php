<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PaidServiceCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function file()
    {
        return $this->morphOne(File::class, 'fileable');
    }

    public function services(): HasMany
    {
        return $this->hasMany(PaidService::class, 'category_id');
    }
}
