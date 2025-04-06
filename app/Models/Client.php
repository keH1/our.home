<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;

class Client extends Model
{
    use Searchable;

    protected $fillable = ['name', 'phone', 'user_id', 'debt'];

    /**
     * Get the indexable data array for the model.
     *
     * @return array<string, mixed>
     */
    #[SearchUsingFullText(['title', 'text'])]
    public function toSearchableArray(): array
    {
        $this->loadMissing('user');

        return [
            'name' => $this->title,
            'phone' => $this->text,
            'email' => $this->user?->email,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function apartments(): BelongsToMany
    {
        return $this->belongsToMany(Apartment::class, 'client_apartment');
    }

    public function accounts(): belongsToMany
    {
        return $this->belongsToMany(AccountPersonalNumber::class,'client_account_personal_numbers');
    }

    public function claims(): HasMany
    {
        return $this->hasMany(Claim::class);
    }
}
