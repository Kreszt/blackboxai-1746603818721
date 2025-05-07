<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTransactionRequest extends FormRequest
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
            'payment_method' => [
                'sometimes',
                'nullable',
                Rule::in(Transaction::PAYMENT_METHODS)
            ],
            'status' => [
                'sometimes',
                Rule::in(Transaction::STATUSES)
            ],
            'discount' => ['nullable', 'numeric', 'min:0'],
            
            // Transaction Items
            'items' => ['sometimes', 'required', 'array', 'min:1'],
            'items.*.id' => [
                'sometimes',
                'integer',
                Rule::exists('transaction_items', 'id')
                    ->where('transaction_id', $this->transaction->id)
            ],
            'items.*.type' => [
                'required',
                Rule::in(TransactionItem::TYPES)
            ],
            'items.*.reference_id' => [
                'required_unless:items.*.type,manual',
                'nullable',
                'integer'
            ],
            'items.*.reference_type' => [
                'required_unless:items.*.type,manual',
                'nullable',
                'string'
            ],
            'items.*.description' => [
                'required_if:items.*.type,manual',
                'nullable',
                'string'
            ],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.notes' => ['nullable', 'string'],

            // Items to remove
            'items_to_remove' => ['sometimes', 'array'],
            'items_to_remove.*' => [
                'integer',
                Rule::exists('transaction_items', 'id')
                    ->where('transaction_id', $this->transaction->id)
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
            'payment_method' => 'Metode Pembayaran',
            'status' => 'Status',
            'discount' => 'Diskon',
            'items' => 'Item Transaksi',
            'items.*.type' => 'Jenis Item',
            'items.*.reference_id' => 'Referensi',
            'items.*.description' => 'Deskripsi',
            'items.*.price' => 'Harga',
            'items.*.quantity' => 'Jumlah',
            'items.*.notes' => 'Catatan',
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
            'payment_method.in' => 'Metode pembayaran tidak valid',
            'status.in' => 'Status tidak valid',

            'discount.numeric' => 'Diskon harus berupa angka',
            'discount.min' => 'Diskon tidak boleh negatif',

            'items.required' => 'Minimal satu item transaksi harus diisi',
            'items.min' => 'Minimal satu item transaksi harus diisi',

            'items.*.type.required' => 'Jenis item wajib dipilih',
            'items.*.type.in' => 'Jenis item tidak valid',

            'items.*.reference_id.required_unless' => 'Referensi wajib diisi untuk item non-manual',
            'items.*.description.required_if' => 'Deskripsi wajib diisi untuk item manual',

            'items.*.price.required' => 'Harga wajib diisi',
            'items.*.price.numeric' => 'Harga harus berupa angka',
            'items.*.price.min' => 'Harga tidak boleh negatif',

            'items.*.quantity.required' => 'Jumlah wajib diisi',
            'items.*.quantity.integer' => 'Jumlah harus berupa angka bulat',
            'items.*.quantity.min' => 'Jumlah minimal 1',
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
            // Check if transaction can be updated
            if (!$this->transaction->canBeUpdated()) {
                $validator->errors()->add('general', 'Transaksi yang sudah selesai tidak dapat diubah');
                return;
            }

            // Validate status transitions
            if ($this->has('status')) {
                if (!$this->isValidStatusTransition($this->transaction->status, $this->status)) {
                    $validator->errors()->add('status', 'Perubahan status tidak valid');
                }

                // If completing transaction, ensure payment method is set
                if ($this->status === 'paid' && !$this->payment_method) {
                    $validator->errors()->add('payment_method', 'Metode pembayaran wajib diisi untuk transaksi yang dibayar');
                }
            }

            // Validate references exist and are valid
            foreach ($this->input('items', []) as $index => $item) {
                if ($item['type'] === 'manual') {
                    continue;
                }

                $referenceExists = match ($item['type']) {
                    'consultation' => MedicalJournal::where('id', $item['reference_id'])
                        ->where('status', 'completed')
                        ->exists(),
                    'prescription' => Prescription::where('id', $item['reference_id'])
                        ->where('status', 'final')
                        ->exists(),
                    default => false
                };

                if (!$referenceExists) {
                    $validator->errors()->add(
                        "items.{$index}.reference_id",
                        'Referensi tidak ditemukan atau tidak valid'
                    );
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
            'unpaid' => ['paid', 'canceled'],
            'paid' => ['paid'], // Cannot change once paid
            'canceled' => ['canceled'], // Cannot change once canceled
        ];

        return in_array($newStatus, $allowedTransitions[$currentStatus] ?? []);
    }
}
