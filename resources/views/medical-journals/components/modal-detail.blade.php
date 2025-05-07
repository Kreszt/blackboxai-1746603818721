<div x-data="journalDetail()"
     @show-journal-detail.window="showJournal($event.detail)">
    
    <x-modal title="Detail Catatan Medis" size="xl">
        <div class="space-y-6">
            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div x-show="!loading">
                <!-- Header Information -->
                <div class="border-b border-gray-200 pb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900" x-text="'No. Journal: ' + journal.journal_number"></h3>
                            <p class="mt-1 text-sm text-gray-500" x-text="'Tanggal: ' + formatDate(journal.date)"></p>
                        </div>
                        <div>
                            <template x-if="journal.status">
                                <x-medical-journals.components.status-badge x-bind:status="journal.status" />
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Patient Information -->
                <div class="border-b border-gray-200 pb-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Data Pasien</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                                <dd class="mt-1 text-sm text-gray-900" x-text="journal.medical_record?.patient?.nama_lengkap"></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Nomor RM</dt>
                                <dd class="mt-1 text-sm text-gray-900" x-text="journal.medical_record?.nomor_rm"></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">NIK</dt>
                                <dd class="mt-1 text-sm text-gray-900" x-text="journal.medical_record?.patient?.nik"></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Tanggal Lahir</dt>
                                <dd class="mt-1 text-sm text-gray-900" x-text="formatDate(journal.medical_record?.patient?.tanggal_lahir)"></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Visit Information -->
                <div class="border-b border-gray-200 pb-4">
                    <h4 class="text-sm font-medium text-gray-900 mb-3">Data Kunjungan</h4>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Klinik</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="journal.clinic?.name"></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Dokter</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="journal.doctor?.name"></dd>
                        </div>
                    </dl>
                </div>

                <!-- Medical Information -->
                <div class="space-y-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-1">Keluhan</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line" x-text="journal.complaint"></p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-1">Diagnosis</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line" x-text="journal.diagnosis"></p>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-1">Tindakan</h4>
                        <p class="text-sm text-gray-700 whitespace-pre-line" x-text="journal.treatment"></p>
                    </div>

                    <template x-if="journal.prescription">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Resep</h4>
                            <p class="text-sm text-gray-700 whitespace-pre-line" x-text="journal.prescription"></p>
                        </div>
                    </template>

                    <template x-if="journal.referral">
                        <div>
                            <h4 class="text-sm font-medium text-gray-900 mb-1">Rujukan</h4>
                            <p class="text-sm text-gray-700 whitespace-pre-line" x-text="journal.referral"></p>
                        </div>
                    </template>
                </div>

                <!-- Audit Information -->
                <div class="mt-6 border-t border-gray-200 pt-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-gray-500">Dibuat Oleh</dt>
                            <dd class="mt-1 text-gray-900">
                                <div x-text="journal.creator?.name || 'System'"></div>
                                <div class="text-gray-500" x-text="formatDateTime(journal.created_at)"></div>
                            </dd>
                        </div>
                        <template x-if="journal.updated_by">
                            <div>
                                <dt class="font-medium text-gray-500">Terakhir Diubah</dt>
                                <dd class="mt-1 text-gray-900">
                                    <div x-text="journal.updater?.name"></div>
                                    <div class="text-gray-500" x-text="formatDateTime(journal.updated_at)"></div>
                                </dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 flex justify-end">
            <button type="button"
                    @click="$dispatch('close-modal')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Tutup
            </button>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function journalDetail() {
    return {
        journal: {},
        loading: false,

        async showJournal(id) {
            this.loading = true;
            try {
                const response = await fetch(`/api/v1/medical-journals/${id}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.journal = data.data;
                }
            } catch (error) {
                console.error('Error loading journal:', error);
            } finally {
                this.loading = false;
            }
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatDateTime(datetime) {
            if (!datetime) return '';
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
