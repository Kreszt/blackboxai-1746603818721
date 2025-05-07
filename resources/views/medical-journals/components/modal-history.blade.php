<div x-data="patientHistory()"
     @show-patient-history.window="showHistory($event.detail)">
    
    <x-modal title="Riwayat Medis Pasien" size="xl">
        <!-- Loading State -->
        <div x-show="loading" class="flex justify-center py-8">
            <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <div x-show="!loading">
            <!-- Patient Information -->
            <div class="mb-6 border-b border-gray-200 pb-4">
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="patient?.nama_lengkap"></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nomor RM</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="patient?.nomor_rm"></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">NIK</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="patient?.nik"></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-gray-900" x-text="formatDate(patient?.tanggal_lahir)"></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Filter -->
            <div class="mb-4 flex space-x-4">
                <select x-model="filter.status"
                        @change="filterJournals"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="ongoing">Sedang Berlangsung</option>
                    <option value="completed">Selesai</option>
                    <option value="referred">Dirujuk</option>
                </select>
            </div>

            <!-- Journals Timeline -->
            <div class="flow-root">
                <ul role="list" class="-mb-8">
                    <template x-for="(journal, index) in journals.data" :key="journal.id">
                        <li>
                            <div class="relative pb-8">
                                <template x-if="index !== journals.data.length - 1">
                                    <span class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200" aria-hidden="true"></span>
                                </template>
                                <div class="relative flex space-x-3">
                                    <div>
                                        <span class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                            <svg class="h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                            </svg>
                                        </span>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-shadow duration-200">
                                            <div class="px-4 py-3">
                                                <div class="flex items-center justify-between mb-2">
                                                    <div class="text-sm font-medium text-gray-900" x-text="formatDate(journal.date)"></div>
                                                    <x-medical-journals.components.status-badge x-bind:status="journal.status" />
                                                </div>
                                                <div class="text-sm text-gray-500 mb-2">
                                                    <span x-text="journal.clinic?.name"></span>
                                                    <span class="mx-1">•</span>
                                                    <span x-text="journal.doctor?.name"></span>
                                                </div>
                                                <div class="text-sm text-gray-700 mb-2">
                                                    <div class="font-medium">Keluhan:</div>
                                                    <p class="line-clamp-2" x-text="journal.complaint"></p>
                                                </div>
                                                <div class="text-sm text-gray-700 mb-2">
                                                    <div class="font-medium">Diagnosis:</div>
                                                    <p class="line-clamp-2" x-text="journal.diagnosis"></p>
                                                </div>
                                                <div class="mt-2 flex justify-end">
                                                    <button @click="$dispatch('show-journal-detail', journal.id)"
                                                            class="text-sm text-blue-600 hover:text-blue-800">
                                                        Lihat Detail
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Pagination -->
            <div x-show="journals.data?.length > 0" class="mt-4 flex items-center justify-between border-t border-gray-200 pt-3">
                <div class="flex justify-between flex-1 sm:hidden">
                    <button @click="previousPage"
                            :disabled="!journals.prev_page_url"
                            :class="{'opacity-50 cursor-not-allowed': !journals.prev_page_url}"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Previous
                    </button>
                    <button @click="nextPage"
                            :disabled="!journals.next_page_url"
                            :class="{'opacity-50 cursor-not-allowed': !journals.next_page_url}"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium" x-text="journals.from"></span>
                            to
                            <span class="font-medium" x-text="journals.to"></span>
                            of
                            <span class="font-medium" x-text="journals.total"></span>
                            results
                        </p>
                    </div>
                </div>
            </div>

            <!-- No Results -->
            <div x-show="journals.data?.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada riwayat medis</h3>
                <p class="mt-1 text-sm text-gray-500">Belum ada catatan medis untuk pasien ini.</p>
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
function patientHistory() {
    return {
        patient: null,
        journals: {
            data: [],
            current_page: 1,
            from: 1,
            to: 1,
            total: 0,
            prev_page_url: null,
            next_page_url: null
        },
        filter: {
            status: ''
        },
        loading: false,

        async showHistory(nomor_rm) {
            this.loading = true;
            try {
                const response = await fetch(`/api/v1/medical-journals/by-patient?nomor_rm=${nomor_rm}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.patient = data.data.medical_record.patient;
                    this.journals = data.data.journals;
                }
            } catch (error) {
                console.error('Error loading patient history:', error);
            } finally {
                this.loading = false;
            }
        },

        async filterJournals() {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    nomor_rm: this.patient.nomor_rm,
                    ...this.filter
                });

                const response = await fetch(`/api/v1/medical-journals/by-patient?${params}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.journals = data.data.journals;
                }
            } catch (error) {
                console.error('Error filtering journals:', error);
            } finally {
                this.loading = false;
            }
        },

        async changePage(page) {
            if (page < 1) return;
            
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    nomor_rm: this.patient.nomor_rm,
                    ...this.filter,
                    page
                });

                const response = await fetch(`/api/v1/medical-journals/by-patient?${params}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.journals = data.data.journals;
                }
            } catch (error) {
                console.error('Error changing page:', error);
            } finally {
                this.loading = false;
            }
        },

        previousPage() {
            if (this.journals.prev_page_url) {
                this.changePage(this.journals.current_page - 1);
            }
        },

        nextPage() {
            if (this.journals.next_page_url) {
                this.changePage(this.journals.current_page + 1);
            }
        },

        formatDate(date) {
            if (!date) return '';
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
