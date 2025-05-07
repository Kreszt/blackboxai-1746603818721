<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TransactionItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'transaction_id',
        'type',
        'reference_id',
        'reference_type',
        'description',
        'price',
        'quantity',
        'subtotal',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
    ];

    /**
     * Valid item types.
     */
    const TYPES = ['consultation', 'prescription', 'manual'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            // Calculate subtotal if not set
            if (!$item->subtotal) {
                $item->subtotal = $item->price * $item->quantity;
            }

            // Set description from reference if not provided
            if (!$item->description && $item->reference) {
                $item->description = static::generateDescription($item);
            }
        });

        static::updating(function ($item) {
            // Recalculate subtotal if price or quantity changed
            if ($item->isDirty(['price', 'quantity'])) {
                $item->subtotal = $item->price * $item->quantity;
            }
        });
    }

    /**
     * Get the transaction that owns the item.
     */
    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    /**
     * Get the reference model (polymorphic).
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Generate description based on reference type.
     */
    protected static function generateDescription($item): string
    {
        if (!$item->reference) {
            return $item->description ?? '';
        }

        return match ($item->type) {
            'consultation' => "Biaya Konsultasi - " . $item->reference->clinic->name,
            'prescription' => "Resep #" . $item->reference->prescription_number,
            default => $item->description ?? ''
        };
    }

    /**
     * Get the formatted price.
     */
    public function getFormattedPriceAttribute(): string
    {
        return 'Rp ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Get the formatted subtotal.
     */
    public function getFormattedSubtotalAttribute(): string
    {
        return 'Rp ' . number_format($this->subtotal, 2, ',', '.');
    }

    /**
     * Get consultation details if type is consultation.
     */
    public function getConsultationDetailsAttribute(): ?array
    {
        if ($this->type !== 'consultation' || !$this->reference) {
            return null;
        }

        $journal = $this->reference;
        return [
            'clinic' => $journal->clinic->name,
            'doctor' => $journal->doctor->name,
            'date' => $journal->visit_date->format('d/m/Y'),
        ];
    }

    /**
     * Get prescription details if type is prescription.
     */
    public function getPrescriptionDetailsAttribute(): ?array
    {
        if ($this->type !== 'prescription' || !$this->reference) {
            return null;
        }

        $prescription = $this->reference;
        return [
            'number' => $prescription->prescription_number,
            'items_count' => $prescription->items->count(),
            'date' => $prescription->created_at->format('d/m/Y'),
        ];
    }

    /**
     * Scope a query to filter by type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope a query to get consultation items.
     */
    public function scopeConsultations($query)
    {
        return $query->where('type', 'consultation');
    }

    /**
     * Scope a query to get prescription items.
     */
    public function scopePrescriptions($query)
    {
        return $query->where('type', 'prescription');
    }

    /**
     * Scope a query to get manual items.
     */
    public function scopeManual($query)
    {
        return $query->where('type', 'manual');
    }
}
