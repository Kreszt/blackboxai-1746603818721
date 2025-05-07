<?php

namespace App\Http\Requests;

use App\Models\Prescription;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrescriptionRequest extends FormRequest
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
            'notes' => ['nullable', 'string'],
            'status' => [
                'sometimes',
                'required',
                Rule::in(Prescription::STATUSES)
            ],
            'revised_reason' => [
                'required_if:status,revised',
                'nullable',
                'string'
            ],
            
            // Prescription Items
            'items' => [
                'sometimes',
                'required',
                'array',
                'min:1'
            ],
            'items.*.id' => [
                'sometimes',
                'integer',
                Rule::exists('prescription_items', 'id')
                    ->where('prescription_id', $this->prescription->id)
            ],
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
            
            // Items to remove
            'items_to_remove' => ['sometimes', 'array'],
            'items_to_remove.*' => [
                'integer',
                Rule::exists('prescription_items', 'id')
                    ->where('prescription_id', $this->prescription->id)
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
            'notes' => 'Catatan',
            'status' => 'Status',
            'revised_reason' => 'Alasan Revisi',
            'items' => 'Item Resep',
            'items.*.medication_id' => 'Obat',
            'items.*.quantity' => 'Jumlah',
            'items.*.dosage_instruction' => 'Aturan Pakai',
            'items.*.price' => 'Harga',
            'items_to_remove' => 'Item yang Dihapus',
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

            'revised_reason.required_if' => 'Alasan revisi wajib diisi saat mengubah status menjadi direvisi',
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
            // Check if prescription can be updated
            if (!$this->prescription->canBeUpdated()) {
                $validator->errors()->add('status', 'Resep yang sudah final atau direvisi tidak dapat diubah');
                return;
            }

            // Validate status transitions
            if ($this->has('status')) {
                $currentStatus = $this->prescription->status;
                $newStatus = $this->status;

                if (!$this->isValidStatusTransition($currentStatus, $newStatus)) {
                    $validator->errors()->add('status', 'Perubahan status tidak valid');
                }

                // If finalizing, ensure there are items
                if ($newStatus === 'final' && !$this->prescription->items()->exists()) {
                    $validator->errors()->add('status', 'Tidak dapat memfinalisasi resep tanpa item obat');
                }
            }

            // Prevent duplicate medications in same prescription
            if ($this->has('items')) {
                $medicationIds = collect($this->items)->pluck('medication_id');
                if ($medicationIds->count() !== $medicationIds->unique()->count()) {
                    $validator->errors()->add('items', 'Terdapat obat yang duplikat dalam resep');
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
            'draft' => ['revised', 'final'],
            'revised' => ['revised'], // Cannot change once revised
            'final' => ['final'], // Cannot change once finalized
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
