<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class MedicalJournal extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'medical_record_id',
        'visit_id',
        'date',
        'doctor_id',
        'clinic_id',
        'complaint',
        'diagnosis',
        'treatment',
        'prescription',
        'referral',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Valid journal statuses.
     */
    const STATUSES = ['ongoing', 'completed', 'referred'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($journal) {
            // Set default date if not provided
            if (!$journal->date) {
                $journal->date = now()->toDateString();
            }

            // Generate journal number
            $journal->journal_number = static::generateJournalNumber($journal->date);

            // Set created_by
            $journal->created_by = auth()->id();
        });

        static::updating(function ($journal) {
            $journal->updated_by = auth()->id();
        });
    }

    /**
     * Generate a unique journal number.
     */
    protected static function generateJournalNumber(string $date): string
    {
        return DB::transaction(function () use ($date) {
            $dateFormat = Carbon::parse($date)->format('Ymd');
            $prefix = "MR{$dateFormat}-";
            
            // Get the last number for this date
            $lastNumber = static::where('journal_number', 'like', $prefix . '%')
                ->orderBy('journal_number', 'desc')
                ->first();

            if (!$lastNumber) {
                $nextNumber = '001';
            } else {
                // Extract the numeric part and increment
                $lastNumeric = intval(substr($lastNumber->journal_number, -3));
                $nextNumber = str_pad($lastNumeric + 1, 3, '0', STR_PAD_LEFT);
            }

            return $prefix . $nextNumber;
        });
    }

    /**
     * Get the medical record that owns the journal.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the visit associated with the journal.
     */
    public function visit(): BelongsTo
    {
        return $this->belongsTo(Visit::class);
    }

    /**
     * Get the doctor associated with the journal.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the clinic associated with the journal.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the user that created the journal.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the journal.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Check if the journal can be updated.
     */
    public function canBeUpdated(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Check if the journal can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Check if the journal can be referred.
     */
    public function canBeReferred(): bool
    {
        return $this->status === 'ongoing';
    }

    /**
     * Update journal status.
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, self::STATUSES)) {
            return false;
        }

        // Validate status transitions
        if ($this->status === 'completed' || $this->status === 'referred') {
            return false;
        }

        return $this->update(['status' => $status]);
    }

    /**
     * Scope a query to filter by date range.
     */
    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('date', [$start, $end]);
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
        return $query->where('doctor_id', $doctorId);
    }

    /**
     * Scope a query to filter by clinic.
     */
    public function scopeByClinic($query, $clinicId)
    {
        return $query->where('clinic_id', $clinicId);
    }

    /**
     * Get the patient through medical record.
     */
    public function getPatientAttribute()
    {
        return $this->medicalRecord->patient;
    }

    /**
     * Get formatted date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->date->format('d F Y');
    }
}
