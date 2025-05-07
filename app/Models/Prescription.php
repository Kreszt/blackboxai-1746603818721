<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Prescription extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'journal_id',
        'notes',
        'status',
        'revised_reason',
    ];

    /**
     * Valid prescription statuses.
     */
    const STATUSES = ['draft', 'revised', 'final'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($prescription) {
            // Generate prescription number
            $prescription->prescription_number = static::generatePrescriptionNumber();
            
            // Set created_by
            $prescription->created_by = auth()->id();
        });

        static::updating(function ($prescription) {
            $prescription->updated_by = auth()->id();

            // If status is being changed to 'revised', set revised_by
            if ($prescription->isDirty('status') && $prescription->status === 'revised') {
                $prescription->revised_by = auth()->id();
            }
        });
    }

    /**
     * Generate a unique prescription number.
     */
    protected static function generatePrescriptionNumber(): string
    {
        return DB::transaction(function () {
            $today = Carbon::today();
            $prefix = 'RX-' . $today->format('Ymd');
            
            // Get the last number for today
            $lastNumber = static::where('prescription_number', 'like', $prefix . '%')
                ->orderBy('prescription_number', 'desc')
                ->first();

            if (!$lastNumber) {
                $nextNumber = '001';
            } else {
                // Extract the numeric part and increment
                $lastNumeric = intval(substr($lastNumber->prescription_number, -3));
                $nextNumber = str_pad($lastNumeric + 1, 3, '0', STR_PAD_LEFT);
            }

            return $prefix . '-' . $nextNumber;
        });
    }

    /**
     * Get the medical journal that owns the prescription.
     */
    public function journal(): BelongsTo
    {
        return $this->belongsTo(MedicalJournal::class, 'journal_id');
    }

    /**
     * Get the prescription items for the prescription.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }

    /**
     * Get the user that created the prescription.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the prescription.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that revised the prescription.
     */
    public function reviser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revised_by');
    }

    /**
     * Check if the prescription can be updated.
     */
    public function canBeUpdated(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the prescription can be revised.
     */
    public function canBeRevised(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the prescription can be finalized.
     */
    public function canBeFinalized(): bool
    {
        return $this->status === 'draft' && $this->items()->exists();
    }

    /**
     * Get the total amount of the prescription.
     */
    public function getTotalAmountAttribute(): float
    {
        return $this->items->sum(function ($item) {
            return $item->price * $item->quantity;
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
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    /**
     * Scope a query to filter by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to filter by doctor.
     */
    public function scopeByDoctor($query, $doctorId)
    {
        return $query->whereHas('journal', function ($q) use ($doctorId) {
            $q->where('doctor_id', $doctorId);
        });
    }

    /**
     * Scope a query to filter by clinic.
     */
    public function scopeByClinic($query, $clinicId)
    {
        return $query->whereHas('journal', function ($q) use ($clinicId) {
            $q->where('clinic_id', $clinicId);
        });
    }

    /**
     * Scope a query to filter by patient.
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->whereHas('journal.medicalRecord', function ($q) use ($patientId) {
            $q->where('patient_id', $patientId);
        });
    }

    /**
     * Scope a query to search by patient name or medical record number.
     */
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('journal.medicalRecord.patient', function ($q) use ($search) {
            $q->where('nama_lengkap', 'like', "%{$search}%")
              ->orWhere('nomor_rm', 'like', "%{$search}%");
        });
    }
}
