<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserNotification extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'notifications';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'is_checked',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_checked' => 'boolean',
    ];

    /**
     * Get the user that owns this notification.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include unchecked notifications.
     */
    public function scopeUnchecked($query)
    {
        return $query->where('is_checked', false);
    }

    /**
     * Scope a query to only include checked notifications.
     */
    public function scopeChecked($query)
    {
        return $query->where('is_checked', true);
    }

    /**
     * Mark the notification as checked.
     */
    public function markAsChecked(): bool
    {
        return $this->update(['is_checked' => true]);
    }
}
