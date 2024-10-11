<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class File extends Model
{
    use HasFactory;

    protected $hidden = [
        'fileable_type',
        'fileable_id',
    ];

    protected $fillable = [
        'original_name',
        'encoded_name',
        'mime_type',
        'size',
        'path',
        'fileable_type',
        'fileable_id',
    ];

    /**
     * Полиморфная связь с моделями.
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }
}
