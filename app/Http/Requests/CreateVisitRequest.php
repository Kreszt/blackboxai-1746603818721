<?php

namespace App\Http\Requests;

use App\Models\Visit;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateVisitRequest extends FormRequest
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
            'visit_date' => ['required', 'date', 'before_or_equal:today'],
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->whereNull('deleted_at')
            ],
            'clinic_id' => [
                'required',
                'integer',
                Rule::exists('clinics', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('doctors', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'visit_type' => [
                'sometimes',
                Rule::in(Visit::VISIT_TYPES)
            ],
            'status' => [
                'sometimes',
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
            
            'visit_type.in' => 'Jenis kunjungan tidak valid',
            'status.in' => 'Status tidak valid',
            
            'remarks.max' => 'Catatan tidak boleh lebih dari 1000 karakter',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->visit_date === null) {
            $this->merge([
                'visit_date' => now()->toDateString()
            ]);
        }
    }
}
