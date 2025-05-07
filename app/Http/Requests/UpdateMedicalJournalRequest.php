<?php

namespace App\Http\Requests;

use App\Models\MedicalJournal;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMedicalJournalRequest extends FormRequest
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
            'doctor_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('doctors', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'clinic_id' => [
                'sometimes',
                'required',
                'integer',
                Rule::exists('clinics', 'id')
                    ->whereNull('deleted_at')
                    ->where('is_active', true)
            ],
            'complaint' => ['sometimes', 'required', 'string'],
            'diagnosis' => ['sometimes', 'required', 'string'],
            'treatment' => ['sometimes', 'required', 'string'],
            'prescription' => ['nullable', 'string'],
            'referral' => ['nullable', 'string'],
            'status' => [
                'sometimes',
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
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $journal = $this->route('medical_journal');
            
            // Prevent updating completed or referred journals
            if (!$journal->canBeUpdated()) {
                $validator->errors()->add('status', 'Catatan medis yang sudah selesai atau dirujuk tidak dapat diubah');
                return;
            }

            // Validate status transitions
            if ($this->has('status')) {
                $currentStatus = $journal->status;
                $newStatus = $this->status;

                if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
                    $validator->errors()->add('status', 'Perubahan status tidak valid');
                }

                // If changing to 'referred' status, referral must be provided
                if ($newStatus === 'referred' && empty($this->referral)) {
                    $validator->errors()->add('referral', 'Rujukan wajib diisi saat mengubah status menjadi dirujuk');
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
            'ongoing' => ['completed', 'referred'],
            'completed' => ['completed'], // Cannot change once completed
            'referred' => ['referred'], // Cannot change once referred
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
