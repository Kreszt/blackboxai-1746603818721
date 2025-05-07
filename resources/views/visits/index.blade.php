@extends('layouts.app')

@section('title', 'Daftar Kunjungan')
@section('header', 'Daftar Kunjungan')

@section('content')
<div x-data="visitsIndex()" x-init="fetchVisits">
    <!-- Filters Section -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Date Filter -->
            <x-form-input
                label="Tanggal"
                name="date"
                type="date"
                x-model="filters.date"
                @change="fetchVisits"
            />

            <!-- Clinic Filter -->
            <x-form-input
                label="Klinik"
                name="clinic_id"
                type="select"
                x-model="filters.clinic_id"
                @change="fetchVisits"
            >
                <option value="">Semua Klinik</option>
                @foreach($clinics as $clinic)
                    <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
            </x-form-input>

            <!-- Doctor Filter -->
            <x-form-input
                label="Dokter"
                name="doctor_id"
                type="select"
                x-model="filters.doctor_id"
                @change="fetchVisits"
            >
                <option value="">Semua Dokter</option>
                @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}">{{ $doctor->name }}</option>
                @endforeach
            </x-form-input>

            <!-- Status Filter -->
            <x-form-input
                label="Status"
                name="status"
                type="select"
                x-model="filters.status"
                @change="fetchVisits"
            >
                <option value="">Semua Status</option>
                @foreach($statuses as $status)
                    <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                @endforeach
            </x-form-input>
        </div>

        <!-- Search Bar -->
        <div class="mt-4">
            <x-form-input
                label="Cari"
                name="search"
                type="text"
                x-model="filters.search"
                @input.debounce.300ms="fetchVisits"
                placeholder="Cari berdasarkan nama pasien atau nomor RM..."
            />
        </div>
    </div>

    <!-- Actions -->
    <div class="mb-6 flex justify-between items-center">
        <a href="{{ route('visits.queue-summary') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-list-ol mr-2"></i>
            Ringkasan Antrian
        </a>
        
        <a href="{{ route('visits.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-plus mr-2"></i>
            Tambah Kunjungan
        </a>
    </div>

    <!-- Visits Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Antrian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pasien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Klinik</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokter</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="!visits.length">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data kunjungan
                            </td>
                        </tr>
                    </template>
                    <template x-for="visit in visits" :key="visit.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="visit.queue_number"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900" x-text="visit.patient.nama_lengkap"></div>
                                <div class="text-sm text-gray-500" x-text="visit.patient.nomor_rm"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="visit.clinic.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="visit.doctor.name"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-visit-type-badge x-bind:type="visit.visit_type" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-status-badge x-bind:status="visit.status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <a :href="`/visits/${visit.id}`" 
                                       class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <template x-if="visit.status !== 'completed' && visit.status !== 'cancelled'">
                                        <a :href="`/visits/${visit.id}/edit`" 
                                           class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </template>
                                    <template x-if="visit.status === 'waiting'">
                                        <button @click="confirmDelete(visit)" 
                                                class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
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
                            :disabled="!pagination.prev_page_url"
                            class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': !pagination.prev_page_url }">
                        Previous
                    </button>
                    <button @click="nextPage"
                            :disabled="!pagination.next_page_url"
                            class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"
                            :class="{ 'opacity-50 cursor-not-allowed': !pagination.next_page_url }">
                        Next
                    </button>
                </div>
                <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                    <div>
                        <p class="text-sm text-gray-700">
                            Showing
                            <span class="font-medium" x-text="pagination.from"></span>
                            to
                            <span class="font-medium" x-text="pagination.to"></span>
                            of
                            <span class="font-medium" x-text="pagination.total"></span>
                            results
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         class="fixed z-10 inset-0 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
                 aria-hidden="true"
                 @click="showDeleteModal = false"></div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                            <i class="fas fa-exclamation-triangle text-red-600"></i>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Batalkan Kunjungan
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Apakah Anda yakin ingin membatalkan kunjungan ini? Tindakan ini tidak dapat dibatalkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deleteVisit" 
                            type="button"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Batalkan
                    </button>
                    <button @click="showDeleteModal = false" 
                            type="button"
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function visitsIndex() {
    return {
        visits: [],
        filters: {
            date: new Date().toISOString().split('T')[0],
            clinic_id: '',
            doctor_id: '',
            status: '',
            search: ''
        },
        pagination: {
            current_page: 1,
            from: 1,
            to: 1,
            total: 0,
            prev_page_url: null,
            next_page_url: null
        },
        showDeleteModal: false,
        visitToDelete: null,

        async fetchVisits() {
            try {
                const queryParams = new URLSearchParams({
                    ...this.filters,
                    page: this.pagination.current_page
                });

                const response = await fetch(`/api/v1/visits?${queryParams}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.visits = data.data.data;
                    this.pagination = {
                        current_page: data.data.current_page,
                        from: data.data.from,
                        to: data.data.to,
                        total: data.data.total,
                        prev_page_url: data.data.prev_page_url,
                        next_page_url: data.data.next_page_url
                    };
                }
            } catch (error) {
                console.error('Error fetching visits:', error);
                notify('Gagal mengambil data kunjungan', 'error');
            }
        },

        previousPage() {
            if (this.pagination.prev_page_url) {
                this.pagination.current_page--;
                this.fetchVisits();
            }
        },

        nextPage() {
            if (this.pagination.next_page_url) {
                this.pagination.current_page++;
                this.fetchVisits();
            }
        },

        confirmDelete(visit) {
            this.visitToDelete = visit;
            this.showDeleteModal = true;
        },

        async deleteVisit() {
            if (!this.visitToDelete) return;

            try {
                const response = await fetch(`/api/v1/visits/${this.visitToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    notify('Kunjungan berhasil dibatalkan');
                    this.fetchVisits();
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error deleting visit:', error);
                notify('Gagal membatalkan kunjungan', 'error');
            }

            this.showDeleteModal = false;
            this.visitToDelete = null;
        }
    }
}
</script>
@endpush
