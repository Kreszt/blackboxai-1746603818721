<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'patient_id',
        'visit_date',
        'status',
        'payment_method',
        'total_amount',
        'discount',
        'final_amount',
        'is_completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_date' => 'date',
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'is_completed' => 'boolean',
    ];

    /**
     * Valid transaction statuses.
     */
    const STATUSES = ['unpaid', 'paid', 'canceled'];

    /**
     * Valid payment methods.
     */
    const PAYMENT_METHODS = ['cash', 'bpjs', 'insurance'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Generate transaction number
            $transaction->transaction_number = static::generateTransactionNumber();
            
            // Set created_by
            $transaction->created_by = auth()->id();
        });

        static::updating(function ($transaction) {
            $transaction->updated_by = auth()->id();

            // Prevent modification of completed transactions
            if ($transaction->getOriginal('is_completed') && 
                !$transaction->isDirty('status')) {
                return false;
            }
        });
    }

    /**
     * Generate a unique transaction number.
     */
    protected static function generateTransactionNumber(): string
    {
        return DB::transaction(function () {
            $today = Carbon::today();
            $prefix = 'TX' . $today->format('Ymd');
            
            // Get the last number for today
            $lastNumber = static::where('transaction_number', 'like', $prefix . '%')
                ->orderBy('transaction_number', 'desc')
                ->first();

            if (!$lastNumber) {
                $nextNumber = '001';
            } else {
                // Extract the numeric part and increment
                $lastNumeric = intval(substr($lastNumber->transaction_number, -3));
                $nextNumber = str_pad($lastNumeric + 1, 3, '0', STR_PAD_LEFT);
            }

            return $prefix . '-' . $nextNumber;
        });
    }

    /**
     * Get the patient that owns the transaction.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the items for the transaction.
     */
    public function items(): HasMany
    {
        return $this->hasMany(TransactionItem::class);
    }

    /**
     * Get the user that created the transaction.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the transaction.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Calculate totals based on items.
     */
    public function calculateTotals(): void
    {
        $this->total_amount = $this->items->sum('subtotal');
        $this->final_amount = $this->total_amount - $this->discount;
    }

    /**
     * Check if the transaction can be updated.
     */
    public function canBeUpdated(): bool
    {
        return !$this->is_completed;
    }

    /**
     * Check if the transaction can be completed.
     */
    public function canBeCompleted(): bool
    {
        return !$this->is_completed && $this->items()->exists();
    }

    /**
     * Check if the transaction can be canceled.
     */
    public function canBeCanceled(): bool
    {
        return !$this->is_completed;
    }

    /**
     * Complete the transaction.
     */
    public function complete(): bool
    {
        if (!$this->canBeCompleted()) {
            return false;
        }

        return $this->update([
            'status' => 'paid',
            'is_completed' => true
        ]);
    }

    /**
     * Cancel the transaction.
     */
    public function cancel(): bool
    {
        if (!$this->canBeCanceled()) {
            return false;
        }

        return $this->update([
            'status' => 'canceled',
            'is_completed' => true
        ]);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('visit_date', [$start, $end]);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by payment method.
     */
    public function scopePaymentMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Scope a query to search by patient or transaction number.
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('transaction_number', 'like', "%{$search}%")
            ->orWhereHas('patient', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%");
            });
    }

    /**
     * Get the formatted total amount.
     */
    public function getFormattedTotalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->total_amount, 2, ',', '.');
    }

    /**
     * Get the formatted discount.
     */
    public function getFormattedDiscountAttribute(): string
    {
        return 'Rp ' . number_format($this->discount, 2, ',', '.');
    }

    /**
     * Get the formatted final amount.
     */
    public function getFormattedFinalAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->final_amount, 2, ',', '.');
    }
}
