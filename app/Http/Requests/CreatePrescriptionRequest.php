<?php

namespace App\Http\Requests;

use App\Models\Prescription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreatePrescriptionRequest extends FormRequest
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
            'journal_id' => [
                'required',
                'integer',
                Rule::exists('medical_journals', 'id')
                    ->whereNull('deleted_at'),
                Rule::unique('prescriptions', 'journal_id')
                    ->whereNull('deleted_at')
            ],
            'notes' => ['nullable', 'string'],
            'status' => [
                'sometimes',
                'required',
                Rule::in(Prescription::STATUSES)
            ],
            
            // Prescription Items
            'items' => ['required', 'array', 'min:1'],
            'items.*.medication_id' => [
                'required',
                'integer',
                Rule::exists('medications', 'id')
                    ->where('is_active', true)
                    ->whereNull('deleted_at')
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.dosage_instruction' => ['required', 'string', 'max:255'],
            'items.*.price' => ['sometimes', 'required', 'numeric', 'min:0'],
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
            'journal_id' => 'Catatan Medis',
            'notes' => 'Catatan',
            'status' => 'Status',
            'items' => 'Item Resep',
            'items.*.medication_id' => 'Obat',
            'items.*.quantity' => 'Jumlah',
            'items.*.dosage_instruction' => 'Aturan Pakai',
            'items.*.price' => 'Harga',
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
            'journal_id.required' => 'Catatan medis wajib dipilih',
            'journal_id.exists' => 'Catatan medis tidak ditemukan',
            'journal_id.unique' => 'Resep untuk catatan medis ini sudah ada',

            'items.required' => 'Minimal satu item obat harus diisi',
            'items.min' => 'Minimal satu item obat harus diisi',

            'items.*.medication_id.required' => 'Obat wajib dipilih',
            'items.*.medication_id.exists' => 'Obat tidak ditemukan atau tidak aktif',

            'items.*.quantity.required' => 'Jumlah wajib diisi',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka bulat',
            'items.*.quantity.min' => 'Jumlah minimal 1',

            'items.*.dosage_instruction.required' => 'Aturan pakai wajib diisi',
            'items.*.dosage_instruction.max' => 'Aturan pakai maksimal 255 karakter',

            'items.*.price.required' => 'Harga wajib diisi',
            'items.*.price.numeric' => 'Harga harus berupa angka',
            'items.*.price.min' => 'Harga tidak boleh negatif',

            'status.required' => 'Status wajib dipilih',
            'status.in' => 'Status tidak valid',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->status === null) {
            $this->merge([
                'status' => 'draft'
            ]);
        }
    }
}
