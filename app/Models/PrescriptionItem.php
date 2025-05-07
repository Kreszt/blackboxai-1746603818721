<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrescriptionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'prescription_id',
        'medication_id',
        'quantity',
        'dosage_instruction',
        'price',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'quantity' => 'integer',
        'price' => 'decimal:2',
    ];

    /**
     * Get the prescription that owns the item.
     */
    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    /**
     * Get the medication for this item.
     */
    public function medication(): BelongsTo
    {
        return $this->belongsTo(Medication::class);
    }

    /**
     * Get the subtotal for this item.
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price * $this->quantity;
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 2, ',', '.');
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Get the full medication info including dosage.
     */
    public function getFullInfoAttribute(): string
    {
        return "{$this->medication->name} ({$this->dosage_instruction})";
    }

    /**
     * Set the price attribute from the current medication price.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (!$item->price) {
                $item->price = $item->medication->price;
            }
        });
    }
}
