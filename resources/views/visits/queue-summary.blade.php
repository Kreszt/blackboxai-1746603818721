@extends('layouts.app')

@section('title', 'Ringkasan Antrian')
@section('header', 'Ringkasan Antrian')

@section('content')
<div x-data="queueSummary()" x-init="fetchSummary" class="max-w-7xl mx-auto">
    <!-- Date and Clinic Filter -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form-input
                label="Tanggal"
                name="date"
                type="date"
                x-model="filters.date"
                @change="fetchSummary"
            />

            <x-form-input
                label="Klinik"
                name="clinic_id"
                type="select"
                x-model="filters.clinic_id"
                @change="fetchSummary"
            >
                <option value="">Semua Klinik</option>
                @foreach($clinics as $clinic)
                    <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                @endforeach
            </x-form-input>
        </div>
    </div>

    <!-- Print Button -->
    <div class="mb-6 flex justify-end">
        <button @click="printSummary"
                class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-print mr-2"></i>
            Cetak
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <!-- Total Visits -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="text-sm font-medium text-gray-500">Total Kunjungan</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900" x-text="totals.all || 0"></div>
        </div>

        <!-- Waiting -->
        <div class="bg-yellow-50 rounded-lg shadow p-6">
            <div class="text-sm font-medium text-yellow-800">Menunggu</div>
            <div class="mt-2 text-3xl font-semibold text-yellow-900" x-text="totals.waiting || 0"></div>
        </div>

        <!-- In Progress -->
        <div class="bg-blue-50 rounded-lg shadow p-6">
            <div class="text-sm font-medium text-blue-800">Sedang Dilayani</div>
            <div class="mt-2 text-3xl font-semibold text-blue-900" x-text="totals.in_progress || 0"></div>
        </div>

        <!-- Completed -->
        <div class="bg-green-50 rounded-lg shadow p-6">
            <div class="text-sm font-medium text-green-800">Selesai</div>
            <div class="mt-2 text-3xl font-semibold text-green-900" x-text="totals.completed || 0"></div>
        </div>
    </div>

    <!-- Queue List -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <template x-if="!clinicQueues.length">
            <div class="p-6 text-center text-gray-500">
                Tidak ada data antrian untuk ditampilkan
            </div>
        </template>

        <template x-for="clinic in clinicQueues" :key="clinic.id">
            <div class="border-b border-gray-200 last:border-0">
                <div class="px-6 py-4 bg-gray-50">
                    <h3 class="text-lg font-medium text-gray-900" x-text="clinic.name"></h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Waiting -->
                        <div class="rounded-lg bg-yellow-50 p-4">
                            <div class="text-sm font-medium text-yellow-800">Menunggu</div>
                            <div class="mt-1">
                                <div class="text-2xl font-semibold text-yellow-900" x-text="clinic.waiting || 0"></div>
                                <div class="text-sm text-yellow-700" x-show="clinic.waiting > 0">
                                    Antrian: <span x-text="clinic.waiting_range"></span>
                                </div>
                            </div>
                        </div>

                        <!-- In Progress -->
                        <div class="rounded-lg bg-blue-50 p-4">
                            <div class="text-sm font-medium text-blue-800">Sedang Dilayani</div>
                            <div class="mt-1">
                                <div class="text-2xl font-semibold text-blue-900" x-text="clinic.in_progress || 0"></div>
                                <div class="text-sm text-blue-700" x-show="clinic.in_progress > 0">
                                    Antrian: <span x-text="clinic.in_progress_range"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Completed -->
                        <div class="rounded-lg bg-green-50 p-4">
                            <div class="text-sm font-medium text-green-800">Selesai</div>
                            <div class="mt-1">
                                <div class="text-2xl font-semibold text-green-900" x-text="clinic.completed || 0"></div>
                                <div class="text-sm text-green-700" x-show="clinic.completed > 0">
                                    Antrian: <span x-text="clinic.completed_range"></span>
                                </div>
                            </div>
                        </div>

                        <!-- Total -->
                        <div class="rounded-lg bg-gray-50 p-4">
                            <div class="text-sm font-medium text-gray-800">Total</div>
                            <div class="mt-1">
                                <div class="text-2xl font-semibold text-gray-900" x-text="clinic.total || 0"></div>
                                <div class="text-sm text-gray-700">
                                    Terakhir: <span x-text="clinic.last_queue || '-'"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</div>

@push('scripts')
<script>
function queueSummary() {
    return {
        filters: {
            date: new Date().toISOString().split('T')[0],
            clinic_id: ''
        },
        clinicQueues: [],
        totals: {
            all: 0,
            waiting: 0,
            in_progress: 0,
            completed: 0
        },

        async fetchSummary() {
            try {
                const queryParams = new URLSearchParams(this.filters);
                const response = await fetch(`/api/v1/visits/queue-summary?${queryParams}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.processQueueData(data.data);
                }
            } catch (error) {
                console.error('Error fetching queue summary:', error);
                notify('Gagal memuat data antrian', 'error');
            }
        },

        processQueueData(data) {
            // Reset totals
            this.totals = {
                all: 0,
                waiting: 0,
                in_progress: 0,
                completed: 0
            };

            // Process clinic queues
            this.clinicQueues = data.map(clinic => {
                const summary = {
                    ...clinic,
                    total: 0,
                    waiting_range: '-',
                    in_progress_range: '-',
                    completed_range: '-',
                    last_queue: '-'
                };

                // Calculate ranges and totals
                if (clinic.queues) {
                    clinic.queues.forEach(q => {
                        summary.total += q.count;
                        this.totals.all += q.count;
                        
                        if (q.status === 'waiting') {
                            this.totals.waiting += q.count;
                            summary.waiting = q.count;
                            summary.waiting_range = `${q.min_number} - ${q.max_number}`;
                        }
                        if (q.status === 'in_progress') {
                            this.totals.in_progress += q.count;
                            summary.in_progress = q.count;
                            summary.in_progress_range = `${q.min_number} - ${q.max_number}`;
                        }
                        if (q.status === 'completed') {
                            this.totals.completed += q.count;
                            summary.completed = q.count;
                            summary.completed_range = `${q.min_number} - ${q.max_number}`;
                        }
                    });

                    if (summary.total > 0) {
                        summary.last_queue = clinic.queues[0].max_number;
                    }
                }

                return summary;
            });
        },

        printSummary() {
            window.print();
        }
    }
}
</script>

<style>
@media print {
    .no-print {
        display: none;
    }
}
</style>
@endpush
