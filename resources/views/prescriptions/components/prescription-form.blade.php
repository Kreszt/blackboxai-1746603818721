@props(['mode' => 'create', 'journal'])

<div x-data="prescriptionForm('{{ $mode }}', {{ $journal->id ?? 'null' }})"
     @medication-selected.window="handleMedicationSelected($event.detail)"
     @update-subtotal.window="handleSubtotalUpdate($event.detail)"
     @remove-item.window="removeItem($event.detail)">
    
    <form @submit.prevent="submitForm">
        <!-- Prescription Info -->
        <div class="space-y-6">
            <!-- Prescription Number (Read-only) -->
            <div x-show="form.prescription_number">
                <x-form-input
                    label="Nomor Resep"
                    name="prescription_number"
                    x-model="form.prescription_number"
                    disabled
                />
            </div>

            <!-- Journal Reference -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h4 class="text-sm font-medium text-gray-900 mb-2">Referensi Kunjungan</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Pasien</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $journal->medicalRecord->patient->nama_lengkap }}</dd>
                        <dd class="text-xs text-gray-500">{{ $journal->medicalRecord->nomor_rm }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dokter</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $journal->doctor->name }}</dd>
                        <dd class="text-xs text-gray-500">{{ $journal->clinic->name }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Medication Items -->
            <div class="space-y-4">
                <div class="flex justify-between items-center">
                    <h4 class="text-sm font-medium text-gray-900">Daftar Obat</h4>
                    <button type="button"
                            @click="addItem"
                            class="inline-flex items-center rounded-md border border-transparent bg-indigo-600 px-3 py-2 text-sm font-medium leading-4 text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <svg class="-ml-0.5 mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Tambah Obat
                    </button>
                </div>

                <!-- Item Headers -->
                <div class="grid grid-cols-12 gap-4 text-xs font-medium text-gray-500 px-2">
                    <div class="col-span-5">Nama Obat</div>
                    <div class="col-span-2">Jumlah</div>
                    <div class="col-span-4">Aturan Pakai</div>
                    <div class="col-span-1">Subtotal</div>
                </div>

                <!-- Item Rows -->
                <div class="space-y-3">
                    <template x-for="(item, index) in form.items" :key="index">
                        <x-prescriptions.components.medication-item-row :index="index" :errors="$errors" />
                    </template>
                </div>

                <!-- No Items Message -->
                <div x-show="!form.items.length" class="text-center py-4 text-sm text-gray-500">
                    Belum ada item obat. Klik tombol "Tambah Obat" untuk menambahkan.
                </div>

                <!-- Total -->
                <div class="flex justify-end pt-4 border-t">
                    <dl class="text-sm">
                        <div class="flex justify-between font-medium">
                            <dt class="text-gray-900">Total</dt>
                            <dd class="text-indigo-600" x-text="formatPrice(total)"></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Notes -->
            <div>
                <x-form-input
                    label="Catatan"
                    name="notes"
                    type="textarea"
                    x-model="form.notes"
                    :error="$errors->first('notes')"
                    placeholder="Tambahkan catatan jika diperlukan..."
                />
            </div>
        </div>

        <!-- Form Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button"
                    @click="$dispatch('close-panel')"
                    class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Batal
            </button>
            <button type="submit"
                    :disabled="isSubmitting || !form.items.length"
                    class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                <span x-show="!isSubmitting" x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                <span x-show="isSubmitting">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan...
                </span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function prescriptionForm(mode = 'create', journalId = null) {
    return {
        mode: mode,
        form: {
            journal_id: journalId,
            prescription_number: '',
            notes: '',
            items: []
        },
        total: 0,
        isSubmitting: false,

        async init() {
            if (this.mode === 'edit' && this.prescriptionId) {
                await this.loadPrescription();
            }
        },

        async loadPrescription() {
            try {
                const response = await fetch(`/api/v1/prescriptions/${this.prescriptionId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    const prescription = data.data;
                    this.form = {
                        journal_id: prescription.journal_id,
                        prescription_number: prescription.prescription_number,
                        notes: prescription.notes || '',
                        items: prescription.items.map(item => ({
                            id: item.id,
                            medication_id: item.medication_id,
                            medication_search: item.medication.name,
                            quantity: item.quantity,
                            dosage_instruction: item.dosage_instruction,
                            price: item.price,
                            subtotal: item.price * item.quantity
                        }))
                    };
                    this.calculateTotal();
                }
            } catch (error) {
                console.error('Error loading prescription:', error);
                notify('Gagal memuat data resep', 'error');
            }
        },

        addItem() {
            this.form.items.push({
                medication_id: '',
                medication_search: '',
                quantity: 1,
                dosage_instruction: '',
                price: 0,
                subtotal: 0
            });
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
            this.calculateTotal();
        },

        handleMedicationSelected({ index, medication }) {
            this.form.items[index].medication_id = medication.id;
            this.form.items[index].price = medication.price;
            this.calculateSubtotal(index);
        },

        handleSubtotalUpdate({ index, subtotal }) {
            this.form.items[index].subtotal = subtotal;
            this.calculateTotal();
        },

        calculateSubtotal(index) {
            const item = this.form.items[index];
            item.subtotal = item.price * item.quantity;
            this.calculateTotal();
        },

        calculateTotal() {
            this.total = this.form.items.reduce((sum, item) => sum + (item.subtotal || 0), 0);
        },

        async submitForm() {
            if (!this.form.items.length) return;

            this.isSubmitting = true;
            try {
                const url = this.mode === 'create' 
                    ? '/api/v1/prescriptions'
                    : `/api/v1/prescriptions/${this.prescriptionId}`;

                const method = this.mode === 'create' ? 'POST' : 'PUT';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Resep berhasil ' + (this.mode === 'create' ? 'dibuat' : 'diperbarui'));
                    this.$dispatch('prescription-saved', data.data);
                    this.$dispatch('close-panel');
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan');
                }
            } catch (error) {
                console.error('Error submitting prescription:', error);
                notify(error.message || 'Gagal menyimpan resep', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(price);
        }
    }
}
</script>
@endpush
