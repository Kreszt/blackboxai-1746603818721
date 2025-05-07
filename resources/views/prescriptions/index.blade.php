@props(['journal'])

<div x-data="prescriptionList({{ $journal->id }})"
     @prescription-saved.window="handlePrescriptionSaved"
     @prescription-revised.window="handlePrescriptionRevised"
     class="space-y-6">

    <!-- Header -->
    <div class="flex justify-between items-center">
        <h3 class="text-lg font-medium text-gray-900">Resep</h3>
        <button type="button"
                @click="showCreateForm"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Resep
        </button>
    </div>

    <!-- Prescriptions List -->
    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Resep</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Item</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !prescriptions.length">
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                Belum ada resep untuk kunjungan ini
                            </td>
                        </tr>
                    </template>
                    <template x-for="prescription in prescriptions" :key="prescription.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="prescription.prescription_number"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(prescription.created_at)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-prescriptions.components.status-badge x-bind:status="prescription.status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="prescription.items.length + ' item'"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatPrice(calculateTotal(prescription))"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <button @click="showDetail(prescription)"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <template x-if="prescription.status === 'draft'">
                                        <button @click="showEditForm(prescription)"
                                                class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Sliding Panels -->
    <div x-show="activePanel === 'create'" x-cloak>
        <x-sliding-panel title="Tambah Resep Baru">
            <x-prescriptions.components.prescription-form :mode="'create'" :journal="$journal" />
        </x-sliding-panel>
    </div>

    <div x-show="activePanel === 'edit'" x-cloak>
        <x-sliding-panel title="Edit Resep">
            <x-prescriptions.components.prescription-form :mode="'edit'" :journal="$journal" />
        </x-sliding-panel>
    </div>

    <div x-show="activePanel === 'detail'" x-cloak>
        <x-sliding-panel title="Detail Resep">
            <x-prescriptions.components.prescription-detail />
        </x-sliding-panel>
    </div>

    <!-- Revise Modal -->
    <x-prescriptions.components.revise-modal />
</div>

@push('scripts')
<script>
function prescriptionList(journalId) {
    return {
        journalId: journalId,
        prescriptions: [],
        loading: true,
        activePanel: null,

        async init() {
            await this.fetchPrescriptions();
        },

        async fetchPrescriptions() {
            try {
                const response = await fetch(`/api/v1/prescriptions?journal_id=${this.journalId}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.prescriptions = data.data;
                }
            } catch (error) {
                console.error('Error fetching prescriptions:', error);
                notify('Gagal memuat data resep', 'error');
            } finally {
                this.loading = false;
            }
        },

        showCreateForm() {
            this.activePanel = 'create';
        },

        showEditForm(prescription) {
            this.$dispatch('prescription-loaded', prescription);
            this.activePanel = 'edit';
        },

        showDetail(prescription) {
            this.$dispatch('prescription-loaded', prescription);
            this.activePanel = 'detail';
        },

        handlePrescriptionSaved(event) {
            this.fetchPrescriptions();
            this.activePanel = null;
        },

        handlePrescriptionRevised(event) {
            this.fetchPrescriptions();
            this.activePanel = null;
        },

        calculateTotal(prescription) {
            return prescription.items.reduce((sum, item) => {
                return sum + (item.price * item.quantity);
            }, 0);
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
        }
    }
}
</script>
@endpush
