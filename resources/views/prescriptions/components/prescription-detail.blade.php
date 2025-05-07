<div x-data="prescriptionDetail()" 
     @prescription-loaded.window="loadPrescription($event.detail)">
    
    <!-- Loading State -->
    <div x-show="loading" class="flex justify-center py-8">
        <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    </div>

    <div x-show="!loading" class="space-y-6">
        <!-- Header Information -->
        <div class="border-b border-gray-200 pb-4">
            <div class="flex justify-between items-start">
                <div>
                    <h3 class="text-lg font-medium text-gray-900" x-text="'No. Resep: ' + prescription.prescription_number"></h3>
                    <p class="mt-1 text-sm text-gray-500" x-text="formatDate(prescription.created_at)"></p>
                </div>
                <x-prescriptions.components.status-badge x-bind:status="prescription.status" />
            </div>
        </div>

        <!-- Patient Information -->
        <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Data Pasien</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="prescription.journal?.medical_record?.patient?.nama_lengkap"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Nomor RM</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="prescription.journal?.medical_record?.nomor_rm"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Klinik</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="prescription.journal?.clinic?.name"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dokter</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="prescription.journal?.doctor?.name"></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Medication Items -->
        <div>
            <h4 class="text-sm font-medium text-gray-900 mb-3">Daftar Obat</h4>
            <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Obat</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aturan Pakai</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        <template x-for="item in prescription.items" :key="item.id">
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900" x-text="item.medication?.name"></td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-center" x-text="item.quantity"></td>
                                <td class="px-4 py-3 text-sm text-gray-900" x-text="item.dosage_instruction"></td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right" x-text="formatPrice(item.price)"></td>
                                <td class="px-4 py-3 text-sm text-gray-900 text-right" x-text="formatPrice(item.price * item.quantity)"></td>
                            </tr>
                        </template>
                    </tbody>
                    <tfoot class="bg-gray-50">
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm font-medium text-gray-900 text-right">Total</td>
                            <td class="px-4 py-3 text-sm font-medium text-indigo-600 text-right" x-text="formatPrice(total)"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Notes -->
        <div x-show="prescription.notes">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Catatan</h4>
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-sm text-gray-700" x-text="prescription.notes"></p>
            </div>
        </div>

        <!-- Revision Info -->
        <div x-show="prescription.revised_by">
            <h4 class="text-sm font-medium text-gray-900 mb-2">Informasi Revisi</h4>
            <div class="bg-yellow-50 rounded-lg p-4">
                <dl class="grid grid-cols-1 gap-y-2">
                    <div>
                        <dt class="text-sm font-medium text-yellow-800">Alasan Revisi</dt>
                        <dd class="mt-1 text-sm text-yellow-700" x-text="prescription.revised_reason"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-yellow-800">Direvisi Oleh</dt>
                        <dd class="mt-1 text-sm text-yellow-700">
                            <span x-text="prescription.reviser?.name"></span>
                            <span class="text-yellow-600" x-text="' pada ' + formatDateTime(prescription.updated_at)"></span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Audit Trail -->
        <div class="border-t border-gray-200 pt-4">
            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                <div>
                    <dt class="font-medium text-gray-500">Dibuat Oleh</dt>
                    <dd class="mt-1 text-gray-900">
                        <div x-text="prescription.creator?.name || 'System'"></div>
                        <div class="text-gray-500" x-text="formatDateTime(prescription.created_at)"></div>
                    </dd>
                </div>
                <template x-if="prescription.updated_by">
                    <div>
                        <dt class="font-medium text-gray-500">Terakhir Diubah</dt>
                        <dd class="mt-1 text-gray-900">
                            <div x-text="prescription.updater?.name"></div>
                            <div class="text-gray-500" x-text="formatDateTime(prescription.updated_at)"></div>
                        </dd>
                    </div>
                </template>
            </dl>
        </div>

        <!-- Actions -->
        <div x-show="prescription.status === 'draft'" class="border-t border-gray-200 pt-4">
            <div class="flex justify-end space-x-3">
                <button type="button"
                        @click="$dispatch('edit-prescription', prescription)"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Edit
                </button>
                <button type="button"
                        @click="$dispatch('revise-prescription', prescription)"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Finalisasi
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function prescriptionDetail() {
    return {
        prescription: {},
        loading: true,
        total: 0,

        loadPrescription(prescription) {
            this.prescription = prescription;
            this.calculateTotal();
            this.loading = false;
        },

        calculateTotal() {
            this.total = this.prescription.items?.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0) || 0;
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(price);
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatDateTime(datetime) {
            return new Date(datetime).toLocaleString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        }
    }
}
</script>
@endpush
