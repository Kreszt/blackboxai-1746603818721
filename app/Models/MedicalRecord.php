<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'patient_id',
        'nomor_rm',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($medicalRecord) {
            $medicalRecord->created_by = auth()->id();
        });

        static::updating(function ($medicalRecord) {
            $medicalRecord->updated_by = auth()->id();
        });
    }

    /**
     * Get the patient that owns the medical record.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the medical journals for the medical record.
     */
    public function journals(): HasMany
    {
        return $this->hasMany(MedicalJournal::class);
    }

    /**
     * Get the user that created the medical record.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the medical record.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the latest medical journal.
     */
    public function latestJournal()
    {
        return $this->journals()->latest('date')->first();
    }

    /**
     * Get all journals between dates.
     */
    public function journalsBetween($startDate, $endDate)
    {
        return $this->journals()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get journals by status.
     */
    public function journalsByStatus($status)
    {
        return $this->journals()
            ->where('status', $status)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get journals by doctor.
     */
    public function journalsByDoctor($doctorId)
    {
        return $this->journals()
            ->where('doctor_id', $doctorId)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Get journals by clinic.
     */
    public function journalsByClinic($clinicId)
    {
        return $this->journals()
            ->where('clinic_id', $clinicId)
            ->orderBy('date', 'desc')
            ->get();
    }

    /**
     * Check if patient has any ongoing journals.
     */
    public function hasOngoingJournals(): bool
    {
        return $this->journals()
            ->where('status', 'ongoing')
            ->exists();
    }

    /**
     * Get the total number of journals.
     */
    public function getTotalJournalsAttribute(): int
    {
        return $this->journals()->count();
    }

    /**
     * Get the date of the first journal.
     */
    public function getFirstJournalDateAttribute()
    {
        return $this->journals()->oldest('date')->value('date');
    }

    /**
     * Get the date of the latest journal.
     */
    public function getLatestJournalDateAttribute()
    {
        return $this->journals()->latest('date')->value('date');
    }
}
