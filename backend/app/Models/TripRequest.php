<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TripRequest extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'description',
        'traveler_id',
        'destination_id',
        'departure_datetime',
        'return_datetime',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'departure_datetime' => 'datetime',
            'return_datetime' => 'datetime',
        ];
    }

    /**
     * Get the traveler that owns this trip request.
     */
    public function traveler(): BelongsTo
    {
        return $this->belongsTo(Traveler::class);
    }

    /**
     * Get the destination for this trip request.
     */
    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Get the user through the traveler relationship.
     */
    public function user()
    {
        return $this->traveler?->user;
    }

    /**
     * Scope a query to only include requests with a specific status.
     */
    public function scopeStatus($query, $status)
    {
        if (is_array($status)) {
            return $query->whereIn('status', $status);
        }

        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('departure_datetime', [$startDate, $endDate]);
    }

    /**
     * Scope a query to filter by destination.
     */
    public function scopeByDestination($query, $destinationId)
    {
        return $query->where('destination_id', $destinationId);
    }

    /**
     * Scope a query to filter by traveler.
     */
    public function scopeByTraveler($query, $travelerId)
    {
        return $query->where('traveler_id', $travelerId);
    }

    /**
     * Scope a query to filter by user (through traveler).
     */
    public function scopeByUser($query, $userId)
    {
        return $query->whereHas('traveler', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    public function scopeByTravelerName($query, $travelerName)
    {
        return $query->whereHas('traveler', function ($q) use ($travelerName) {
            $q->where('name', 'like', '%' . $travelerName . '%');
        });
    }

    public function scopeByDestinationName($query, $destinationName)
    {
        return $query->whereHas('destination', function ($q) use ($destinationName) {
            $q->where('name', 'like', '%' . $destinationName . '%');
        });
    }
}
