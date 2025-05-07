@extends('layouts.app')

@section('title', 'Buat Transaksi Baru')
@section('header', 'Buat Transaksi Baru')

@section('content')
<div x-data="transactionForm({{ json_encode(auth()->user()->role) }})" class="max-w-4xl mx-auto space-y-6">
    <form @submit.prevent="submitForm" class="bg-white shadow rounded-lg p-6">
        <!-- Patient Selection -->
        <div>
            <label for="patient" class="block text-sm font-medium text-gray-700">Pilih Pasien <span class="text-red-500">*</span></label>
            <input type="text" id="patient_search" x-model="patientSearch" @input.debounce.300ms="searchPatients" placeholder="Cari pasien..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" autocomplete="off">
            <div x-show="patientResults.length > 0" class="border border-gray-300 rounded-md mt-1 max-h-48 overflow-auto bg-white z-10 relative">
                <template x-for="patient in patientResults" :key="patient.id">
                    <div @click="selectPatient(patient)" class="cursor-pointer px-4 py-2 hover:bg-indigo-100" x-text="patient.nama_lengkap + ' - ' + patient.nomor_rm"></div>
                </template>
            </div>
            <div x-show="errors.patient_id" class="text-red-600 text-sm mt-1" x-text="errors.patient_id"></div>
        </div>

        <!-- Visit Date -->
        <div class="mt-4">
            <label for="visit_date" class="block text-sm font-medium text-gray-700">Tanggal Kunjungan <span class="text-red-500">*</span></label>
            <input type="date" id="visit_date" x-model="form.visit_date" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
            <div x-show="errors.visit_date" class="text-red-600 text-sm mt-1" x-text="errors.visit_date"></div>
        </div>

        <!-- Transaction Items -->
        <div class="mt-6">
            <div class="flex justify-between items-center mb-2">
                <h3 class="text-lg font-medium text-gray-900">Item Transaksi</h3>
                <button type="button" @click="addItem" class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-0.5 mr-2 h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Item
                </button>
            </div>

            <template x-for="(item, index) in form.items" :key="index">
                <div class="mb-4 p-4 border border-gray-300 rounded-md">
                    <div class="grid grid-cols-12 gap-4 items-center">
                        <div class="col-span-3">
                            <label :for="'items[' + index + '][type]'" class="block text-sm font-medium text-gray-700">Jenis Item <span class="text-red-500">*</span></label>
                            <select :id="'items[' + index + '][type]'" :name="'items[' + index + '][type]'" x-model="item.type" @change="onTypeChange(index)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                <option value="">Pilih Jenis</option>
                                <option value="consultation">Konsultasi</option>
                                <option value="prescription">Resep</option>
                                <option value="manual" x-show="isAdmin">Manual</option>
                            </select>
                            <div x-show="errors['items.' + index + '.type']" class="text-red-600 text-sm mt-1" x-text="errors['items.' + index + '.type']"></div>
                        </div>

                        <div class="col-span-4" x-show="item.type && item.type !== 'manual'">
                            <label :for="'items[' + index + '][reference_search]'" class="block text-sm font-medium text-gray-700">Pilih Referensi <span class="text-red-500">*</span></label>
                            <input type="text" :id="'items[' + index + '][reference_search]'" x-model="item.reference_search" @input.debounce.300ms="searchReferences(index)" placeholder="Cari referensi..." class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" autocomplete="off">
                            <div x-show="referenceResults[index] && referenceResults[index].length > 0" class="border border-gray-300 rounded-md mt-1 max-h-40 overflow-auto bg-white z-10 relative">
                                <template x-for="ref in referenceResults[index]" :key="ref.id">
                                    <div @click="selectReference(index, ref)" class="cursor-pointer px-4 py-2 hover:bg-indigo-100" x-text="getRefTitle(ref, item.type)"></div>
                                </template>
                            </div>
                            <div x-show="errors['items.' + index + '.reference_id']" class="text-red-600 text-sm mt-1" x-text="errors['items.' + index + '.reference_id']"></div>
                        </div>

                        <div class="col-span-4" x-show="item.type === 'manual'">
                            <label :for="'items[' + index + '][description]'" class="block text-sm font-medium text-gray-700">Deskripsi <span class="text-red-500">*</span></label>
                            <input type="text" :id="'items[' + index + '][description]'" :name="'items[' + index + '][description]'" x-model="item.description" placeholder="Masukkan deskripsi item" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <div x-show="errors['items.' + index + '.description']" class="text-red-600 text-sm mt-1" x-text="errors['items.' + index + '.description']"></div>
                        </div>

                        <div class="col-span-2">
                            <label :for="'items[' + index + '][price]'" class="block text-sm font-medium text-gray-700">Harga <span class="text-red-500">*</span></label>
                            <input type="number" :id="'items[' + index + '][price]'" :name="'items[' + index + '][price]'" x-model.number="item.price" :readonly="item.type !== 'manual'" @input="calculateSubtotal(index)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" :class="{'bg-gray-50': item.type !== 'manual'}">
                            <div x-show="errors['items.' + index + '.price']" class="text-red-600 text-sm mt-1" x-text="errors['items.' + index + '.price']"></div>
                        </div>

                        <div class="col-span-2">
                            <label :for="'items[' + index + '][quantity]'" class="block text-sm font-medium text-gray-700">Jumlah <span class="text-red-500">*</span></label>
                            <input type="number" :id="'items[' + index + '][quantity]'" :name="'items[' + index + '][quantity]'" x-model.number="item.quantity" min="1" @input="calculateSubtotal(index)" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                            <div x-show="errors['items.' + index + '.quantity']" class="text-red-600 text-sm mt-1" x-text="errors['items.' + index + '.quantity']"></div>
                        </div>

                        <div class="col-span-1 pt-6">
                            <button type="button" @click="removeItem(index)" class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                                <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <!-- Summary -->
        <div class="mt-6 max-w-md ml-auto">
            <div class="flex justify-between text-sm font-medium text-gray-900">
                <div>Total</div>
                <div x-text="formatPrice(total)"></div>
            </div>
            <div class="flex justify-between text-sm font-medium text-gray-900 mt-1">
                <div>Diskon</div>
                <input type="number" x-model.number="form.discount" min="0" class="w-24 rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" />
            </div>
        </div>

        <!-- Actions -->
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button" @click="cancel" class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Batal
            </button>
            <button type="submit" :disabled="isSubmitting || !form.items.length" class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                <span x-show="!isSubmitting">Simpan Transaksi</span>
                <span x-show="isSubmitting" class="flex items-center">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke-width="4" stroke="currentColor"></circle>
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
function transactionForm(userRole) {
    return {
        isAdmin: userRole === 'admin',
        form: {
            patient_id: '',
            visit_date: new Date().toISOString().split('T')[0],
            items: [],
            discount: 0
        },
        patientSearch: '',
        patientResults: [],
        referenceResults: {},
        errors: {},
        total: 0,
        isSubmitting: false,

        async searchPatients() {
            if (this.patientSearch.length < 3) {
                this.patientResults = [];
                return;
            }
            try {
                const response = await fetch(`/api/v1/patients/search?nama=${this.patientSearch}`);
                const data = await response.json();
                if (data.status === 'success') {
                    this.patientResults = data.data.data;
                }
            } catch (error) {
                console.error('Error searching patients:', error);
            }
        },

        selectPatient(patient) {
            this.form.patient_id = patient.id;
            this.patientSearch = patient.nama_lengkap + ' - ' + patient.nomor_rm;
            this.patientResults = [];
        },

        addItem() {
            this.form.items.push({
                type: '',
                reference_id: '',
                reference_type: '',
                reference_search: '',
                description: '',
                price: 0,
                quantity: 1,
                notes: ''
            });
        },

        removeItem(index) {
            this.form.items.splice(index, 1);
            this.calculateTotal();
        },

        onTypeChange(index) {
            const item = this.form.items[index];
            item.reference_id = '';
            item.reference_type = '';
            item.reference_search = '';
            item.description = '';
            item.price = 0;
            this.$set(this.referenceResults, index, []);
        },

        async searchReferences(index) {
            const item = this.form.items[index];
            if (!item.reference_search || item.reference_search.length < 2) {
                this.$set(this.referenceResults, index, []);
                return;
            }
            try {
                let endpoint = '';
                if (item.type === 'consultation') {
                    endpoint = `/api/v1/medical-journals/search?query=${item.reference_search}&status=completed`;
                } else if (item.type === 'prescription') {
                    endpoint = `/api/v1/prescriptions/search?query=${item.reference_search}&status=final`;
                }
                const response = await fetch(endpoint);
                const data = await response.json();
                if (data.status === 'success') {
                    this.$set(this.referenceResults, index, data.data);
                }
            } catch (error) {
                console.error('Error searching references:', error);
            }
        },

        selectReference(index, ref) {
            const item = this.form.items[index];
            item.reference_id = ref.id;
            item.reference_type = item.type === 'consultation' ? 'MedicalJournal' : 'Prescription';
            item.description = item.type === 'consultation' ? `Konsultasi - ${ref.clinic.name}` : `Resep #${ref.prescription_number}`;
            item.price = item.type === 'consultation' ? (ref.consultation_fee || 0) : (ref.total_amount || 0);
            item.reference_search = item.description;
            this.$set(this.referenceResults, index, []);
            this.calculateSubtotal(index);
        },

        calculateSubtotal(index) {
            const item = this.form.items[index];
            item.subtotal = item.price * item.quantity;
            this.calculateTotal();
        },

        calculateTotal() {
            this.total = this.form.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
        },

        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};
            try {
                const response = await fetch('/api/v1/transactions', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });
                const data = await response.json();
                if (response.ok) {
                    notify('Transaksi berhasil disimpan');
                    this.resetForm();
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error submitting transaction:', error);
                notify(error.message || 'Gagal menyimpan transaksi', 'error');
            } finally {
                this.isSubmitting = false;
            }
        },

        resetForm() {
            this.form = {
                patient_id: '',
                visit_date: new Date().toISOString().split('T')[0],
                items: [],
                discount: 0
            };
            this.patientSearch = '';
            this.patientResults = [];
            this.referenceResults = {};
            this.errors = {};
            this.total = 0;
        }
    }
}
</script>
@endpush
