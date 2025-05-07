@extends('layouts.app')

@section('title', 'Catatan Medis')
@section('header', 'Catatan Medis')

@section('content')
<div x-data="journalsIndex">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Filter -->
            <div>
                <label for="date" class="block text-sm font-medium text-gray-700">Tanggal</label>
                <input type="date"
                       id="date"
                       x-model="filters.date"
                       @change="fetchJournals"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Clinic Filter -->
            <div>
                <label for="clinic" class="block text-sm font-medium text-gray-700">Klinik</label>
                <select id="clinic"
                        x-model="filters.clinic_id"
                        @change="fetchJournals"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Klinik</option>
                    <template x-for="clinic in clinics" :key="clinic.id">
                        <option :value="clinic.id" x-text="clinic.name"></option>
                    </template>
                </select>
            </div>

            <!-- Doctor Filter -->
            <div>
                <label for="doctor" class="block text-sm font-medium text-gray-700">Dokter</label>
                <select id="doctor"
                        x-model="filters.doctor_id"
                        @change="fetchJournals"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Dokter</option>
                    <template x-for="doctor in doctors" :key="doctor.id">
                        <option :value="doctor.id" x-text="doctor.name"></option>
                    </template>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status"
                        x-model="filters.status"
                        @change="fetchJournals"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="ongoing">Sedang Berlangsung</option>
                    <option value="completed">Selesai</option>
                    <option value="referred">Dirujuk</option>
                </select>
            </div>
        </div>

        <!-- Search -->
        <div class="mt-4">
            <div class="relative">
                <input type="text"
                       x-model="filters.search"
                       @input.debounce.300ms="fetchJournals"
                       placeholder="Cari berdasarkan nama pasien atau nomor RM..."
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm pl-10">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-6">
        <button @click="$dispatch('show-journal-form')"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Catatan Medis
        </button>
    </div>

    <!-- Journals Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. RM</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klinik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && journals.data?.length === 0">
                        <tr>
                            <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data catatan medis
                            </td>
                        </tr>
                    </template>
                    <template x-for="(journal, index) in journals.data" :key="journal.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="journals.from + index"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDate(journal.date)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" x-text="journal.medical_record.patient.nama_lengkap"></div>
                                <button @click="showPatientHistory(journal.medical_record.nomor_rm)"
                                        class="text-xs text-blue-600 hover:text-blue-800">
                                    Lihat Riwayat
                                </button>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="journal.medical_record.nomor_rm"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="journal.clinic.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="journal.doctor.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-medical-journals.components.status-badge x-bind:status="journal.status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <button @click="showJournalDetail(journal.id)"
                                            class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <template x-if="journal.status === 'ongoing'">
                                        <button @click="editJournal(journal)"
                                                class="text-indigo-600 hover:text-indigo-900">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </template>
                                    <template x-if="journal.status === 'ongoing'">
                                        <button @click="updateJournalStatus(journal)"
                                                class="text-green-600 hover:text-green-900">
                                            <i class="fas fa-check-circle"></i>
                                        </button>
                                    </template>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            <div class="flex items-center justify-between">
                <div class="flex-1 flex justify-between sm:hidden">
                    <button @click="previousPage"
                            :disabled="!journals.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{'opacity-50 cursor-not-allowed': !journals.prev_page_url}">
                        Previous
                    </button>
                    <button @click="nextPage"
                            :disabled="!journals.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{'opacity-50 cursor-not-allowed': !journals.next_page_url}">
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
        </div>
    </div>

    <!-- Modals -->
    @include('medical-journals.components.modal-form')
    @include('medical-journals.components.modal-detail')
    @include('medical-journals.components.modal-history')
    @include('medical-journals.components.modal-status')
</div>

@push('scripts')
<script>
function journalsIndex() {
    return {
        journals: {
            data: [],
            current_page: 1,
            from: 1,
            to: 1,
            total: 0,
            prev_page_url: null,
            next_page_url: null
        },
        clinics: [],
        doctors: [],
        filters: {
            date: new Date().toISOString().split('T')[0],
            clinic_id: '',
            doctor_id: '',
            status: '',
            search: ''
        },
        loading: false,

        async init() {
            await this.loadFilters();
            await this.fetchJournals();

            this.$watch('filters', () => {
                this.fetchJournals();
            });
        },

        async loadFilters() {
            try {
                const [clinicsResponse, doctorsResponse] = await Promise.all([
                    fetch('/api/v1/clinics'),
                    fetch('/api/v1/doctors')
                ]);

                const clinicsData = await clinicsResponse.json();
                const doctorsData = await doctorsResponse.json();

                if (clinicsData.status === 'success') {
                    this.clinics = clinicsData.data;
                }

                if (doctorsData.status === 'success') {
                    this.doctors = doctorsData.data;
                }
            } catch (error) {
                console.error('Error loading filters:', error);
            }
        },

        async fetchJournals() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/v1/medical-journals?${params}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.journals = data.data;
                }
            } catch (error) {
                console.error('Error fetching journals:', error);
            } finally {
                this.loading = false;
            }
        },

        showJournalDetail(id) {
            this.$dispatch('show-journal-detail', id);
        },

        editJournal(journal) {
            this.$dispatch('journal-edit', journal);
        },

        updateJournalStatus(journal) {
            this.$dispatch('update-journal-status', journal);
        },

        showPatientHistory(nomor_rm) {
            this.$dispatch('show-patient-history', nomor_rm);
        },

        async previousPage() {
            if (this.journals.prev_page_url) {
                await this.changePage(this.journals.current_page - 1);
            }
        },

        async nextPage() {
            if (this.journals.next_page_url) {
                await this.changePage(this.journals.current_page + 1);
            }
        },

        async changePage(page) {
            this.loading = true;
            try {
                const params = new URLSearchParams({
                    ...this.filters,
                    page
                });

                const response = await fetch(`/api/v1/medical-journals?${params}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.journals = data.data;
                }
            } catch (error) {
                console.error('Error changing page:', error);
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
        }
    }
}
</script>
@endpush
