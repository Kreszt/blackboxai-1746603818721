<?php

namespace App\Http\Requests;

use App\Models\MedicalJournal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateMedicalJournalRequest extends FormRequest
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
            'medical_record_id' => [
                'required',
                'integer',
                Rule::exists('medical_records', 'id')
            ],
            'visit_id' => [
                'required',
                'integer',
                Rule::exists('visits', 'id')
                    ->whereNull('deleted_at'),
                Rule::unique('medical_journals', 'visit_id')
                    ->whereNull('deleted_at')
            ],
            'date' => [
                'required',
                'date',
                'before_or_equal:today'
            ],
            'doctor_id' => [
                'required',
                'integer',
                Rule::exists('doctors', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'clinic_id' => [
                'required',
                'integer',
                Rule::exists('clinics', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'complaint' => ['required', 'string'],
            'diagnosis' => ['required', 'string'],
            'treatment' => ['required', 'string'],
            'prescription' => ['nullable', 'string'],
            'referral' => ['nullable', 'string'],
            'status' => [
                'required',
                Rule::in(MedicalJournal::STATUSES)
            ],
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
            'medical_record_id' => 'Rekam Medis',
            'visit_id' => 'Kunjungan',
            'date' => 'Tanggal',
            'doctor_id' => 'Dokter',
            'clinic_id' => 'Klinik',
            'complaint' => 'Keluhan',
            'diagnosis' => 'Diagnosis',
            'treatment' => 'Tindakan',
            'prescription' => 'Resep',
            'referral' => 'Rujukan',
            'status' => 'Status',
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
            'medical_record_id.required' => 'Rekam medis wajib diisi',
            'medical_record_id.exists' => 'Rekam medis tidak ditemukan',

            'visit_id.required' => 'Kunjungan wajib diisi',
            'visit_id.exists' => 'Kunjungan tidak ditemukan atau tidak aktif',
            'visit_id.unique' => 'Catatan medis untuk kunjungan ini sudah ada',

            'date.required' => 'Tanggal wajib diisi',
            'date.date' => 'Format tanggal tidak valid',
            'date.before_or_equal' => 'Tanggal tidak boleh lebih dari hari ini',

            'doctor_id.required' => 'Dokter wajib dipilih',
            'doctor_id.exists' => 'Dokter tidak ditemukan atau tidak aktif',

            'clinic_id.required' => 'Klinik wajib dipilih',
            'clinic_id.exists' => 'Klinik tidak ditemukan atau tidak aktif',

            'complaint.required' => 'Keluhan wajib diisi',
            'diagnosis.required' => 'Diagnosis wajib diisi',
            'treatment.required' => 'Tindakan wajib diisi',

            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->date === null) {
            $this->merge([
                'date' => now()->toDateString()
            ]);
        }

        if ($this->status === null) {
            $this->merge([
                'status' => 'ongoing'
            ]);
        }
    }
}
