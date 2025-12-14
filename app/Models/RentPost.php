<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'contact_number',
        'rent',
        'location',
        'address_detail',
        'bedrooms',
        'washrooms',
        'available_from',
        'is_available',
    ];

    protected $casts = [
        'rent' => 'integer',
        'bedrooms' => 'integer',
        'washrooms' => 'integer',
        'available_from' => 'date',
        'is_available' => 'boolean',
    ];

    protected $appends = [
        'formatted_rent',
        'category_label',
        'available_from_formatted',
    ];

    /**
     * Get the user that owns the rent post.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted rent with currency.
     */
    public function getFormattedRentAttribute(): string
    {
        return '৳ ' . number_format($this->rent);
    }

    /**
     * Get category label.
     */
    public function getCategoryLabelAttribute(): string
    {
        return ucfirst(str_replace('_', ' ', $this->category));
    }

    /**
     * Get formatted available date.
     */
    public function getAvailableFromFormattedAttribute(): ?string
    {
        return $this->available_from?->format('M d, Y');
    }

    /**
     * Scope a query to only include available posts.
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope a query to filter by category.
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to filter by location.
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    /**
     * Scope a query to filter by rent range.
     */
    public function scopeRentBetween($query, $min, $max)
    {
        return $query->whereBetween('rent', [$min, $max]);
    }

    /**
     * Scope a query to filter by bedrooms.
     */
    public function scopeBedrooms($query, $bedrooms)
    {
        return $query->where('bedrooms', $bedrooms);
    }
}