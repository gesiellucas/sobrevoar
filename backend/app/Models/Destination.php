<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'city',
        'state',
        'country',
    ];

    /**
     * Get the trip requests for this destination.
     */
    public function tripRequests(): HasMany
    {
        return $this->hasMany(TripRequest::class);
    }

    /**
     * Get the full location string.
     */
    public function getFullLocationAttribute(): string
    {
        $parts = [$this->city];

        if ($this->state) {
            $parts[] = $this->state;
        }

        $parts[] = $this->country;

        return implode(', ', $parts);
    }
}
