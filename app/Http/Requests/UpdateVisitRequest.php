<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVisitRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Adjust based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'visit_date' => ['sometimes', 'required', 'date', 'before_or_equal:today'],
            'patient_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at')
            ],
            'clinic_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('clinics', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'doctor_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('doctors', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'visit_type' => [
                'sometimes',
                'required',
                Rule::in(Visit::VISIT_TYPES)
            ],
            'status' => [
                'sometimes',
                'required',
                Rule::in(Visit::VISIT_STATUSES)
            ],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'visit_date' => 'Tanggal Kunjungan',
            'patient_id' => 'Pasien',
            'clinic_id' => 'Klinik/Poli',
            'doctor_id' => 'Dokter',
            'visit_type' => 'Jenis Kunjungan',
            'status' => 'Status',
            'remarks' => 'Catatan',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'visit_date.required' => 'Tanggal kunjungan wajib diisi',
            'visit_date.date' => 'Format tanggal kunjungan tidak valid',
            'visit_date.before_or_equal' => 'Tanggal kunjungan tidak boleh lebih dari hari ini',
            
            'patient_id.required' => 'Pasien wajib dipilih',
            'patient_id.exists' => 'Pasien tidak ditemukan atau tidak aktif',
            
            'clinic_id.required' => 'Klinik/Poli wajib dipilih',
            'clinic_id.exists' => 'Klinik/Poli tidak ditemukan atau tidak aktif',
            
            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan atau tidak aktif',
            
            'visit_type.required' => 'Jenis kunjungan wajib dipilih',
            'visit_type.in' => 'Jenis kunjungan tidak valid',
            
            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
            
            'remarks.max' => 'Catatan tidak boleh lebih dari 1000 karakter',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $visit = $this->route('visit');
            
            // Prevent changing completed or cancelled visits
            if ($visit && in_array($visit->status, ['completed', 'cancelled'])) {
                if ($this->has('status') && $this->status !== $visit->status) {
                    $validator->errors()->add('status', 'Status kunjungan yang sudah selesai atau dibatalkan tidak dapat diubah');
                }
            }

            // Validate status transitions
            if ($this->has('status')) {
                $currentStatus = $visit->status;
                $newStatus = $this->status;

                if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
                    $validator->errors()->add('status', 'Perubahan status tidak valid');
                }
            }
        });
    }

    /**
     * Check if the status transition is valid.
     */
    private function isValidStatusTransition(string $currentStatus, string $newStatus): bool
    {
        $allowedTransitions = [
            'waiting' => ['in_progress', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => ['completed'], // Cannot change once completed
            'cancelled' => ['cancelled'], // Cannot change once cancelled
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
