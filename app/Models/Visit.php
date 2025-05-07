<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Visit extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'visit_date',
        'patient_id',
        'clinic_id',
        'doctor_id',
        'visit_type',
        'status',
        'remarks',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'visit_date' => 'date',
    ];

    /**
     * Valid visit types.
     */
    const VISIT_TYPES = ['new', 'returning', 'control'];

    /**
     * Valid visit statuses.
     */
    const VISIT_STATUSES = ['waiting', 'in_progress', 'completed', 'cancelled'];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($visit) {
            // Set default visit date if not provided
            if (!$visit->visit_date) {
                $visit->visit_date = now()->toDateString();
            }

            // Set created_by
            $visit->created_by = auth()->id();

            // Determine visit type based on patient's visit history
            if (!$visit->visit_type) {
                $visit->visit_type = static::determineVisitType($visit->patient_id);
            }

            // Generate queue number
            $queueData = static::generateQueueNumber($visit->visit_date, $visit->clinic_id);
            $visit->queue_number = $queueData['queue_number'];
            $visit->queue_order = $queueData['queue_order'];
        });

        static::updating(function ($visit) {
            $visit->updated_by = auth()->id();
        });
    }

    /**
     * Get the patient that owns the visit.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the clinic that owns the visit.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Get the doctor that owns the visit.
     */
    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    /**
     * Get the user that created the visit.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the visit.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Determine visit type based on patient's visit history.
     */
    protected static function determineVisitType(int $patientId): string
    {
        $previousVisit = static::where('patient_id', $patientId)
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('visit_date', 'desc')
            ->first();

        if (!$previousVisit) {
            return 'new';
        }

        // If last visit was within 30 days, it's a control visit
        if ($previousVisit->visit_date->diffInDays(now()) <= 30) {
            return 'control';
        }

        return 'returning';
    }

    /**
     * Generate queue number for the visit.
     */
    protected static function generateQueueNumber(string $visitDate, int $clinicId): array
    {
        return DB::transaction(function () use ($visitDate, $clinicId) {
            // Get clinic code
            $clinic = Clinic::findOrFail($clinicId);
            
            // Get the last queue number for this clinic and date
            $lastQueue = static::where('visit_date', $visitDate)
                ->where('clinic_id', $clinicId)
                ->orderBy('queue_order', 'desc')
                ->first();

            $queueOrder = $lastQueue ? $lastQueue->queue_order + 1 : 1;
            
            // Format: P-[clinic_code]-[yyMMdd]-[increment]
            $formattedDate = Carbon::parse($visitDate)->format('ymd');
            $queueNumber = sprintf(
                'P-%s-%s-%03d',
                $clinic->code,
                $formattedDate,
                $queueOrder
            );

            return [
                'queue_number' => $queueNumber,
                'queue_order' => $queueOrder,
            ];
        });
    }

    /**
     * Scope a query to filter visits by date range.
     */
    public function scopeDateBetween($query, $start, $end)
    {
        return $query->whereBetween('visit_date', [$start, $end]);
    }

    /**
     * Scope a query to filter visits by status.
     */
    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if the visit can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['waiting', 'in_progress']);
    }

    /**
     * Check if the visit can be completed.
     */
    public function canBeCompleted(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Update visit status.
     */
    public function updateStatus(string $status): bool
    {
        if (!in_array($status, self::VISIT_STATUSES)) {
            return false;
        }

        return $this->update(['status' => $status]);
    }
}
