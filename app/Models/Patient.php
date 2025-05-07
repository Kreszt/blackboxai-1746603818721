<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nik',
        'tanggal_lahir',
        'jenis_kelamin',
        'alamat',
        'no_hp',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($patient) {
            $patient->nomor_rm = static::generateMedicalRecordNumber();
            $patient->created_by = auth()->id();
        });

        static::updating(function ($patient) {
            $patient->updated_by = auth()->id();
        });
    }

    /**
     * Generate a unique medical record number.
     *
     * @return string
     */
    protected static function generateMedicalRecordNumber(): string
    {
        $year = Carbon::now()->year;
        $prefix = 'ASA-' . $year;
        
        // Get the last medical record number for the current year
        $lastNumber = static::where('nomor_rm', 'like', $prefix . '%')
            ->orderBy('nomor_rm', 'desc')
            ->first();

        if (!$lastNumber) {
            // If no record exists for this year, start with 000001
            $nextNumber = '000001';
        } else {
            // Extract the numeric part and increment
            $lastNumeric = intval(substr($lastNumber->nomor_rm, -6));
            $nextNumber = str_pad($lastNumeric + 1, 6, '0', STR_PAD_LEFT);
        }

        return $prefix . $nextNumber;
    }

    /**
     * Get the user that created the patient.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that last updated the patient.
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
