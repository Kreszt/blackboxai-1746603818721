@extends('layouts.app')

@section('title', 'Daftar Transaksi')
@section('header', 'Daftar Transaksi')

@section('content')
<div x-data="transactionsIndex()" x-init="fetchTransactions" class="space-y-6">
    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Patient Name Search -->
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Nama Pasien</label>
                <input type="text" id="search" x-model="filters.search" @input.debounce.300ms="fetchTransactions"
                       placeholder="Cari nama pasien..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <!-- Payment Status -->
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status Pembayaran</label>
                <select id="status" x-model="filters.status" @change="fetchTransactions"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Status</option>
                    <option value="unpaid">Belum Dibayar</option>
                    <option value="paid">Lunas</option>
                    <option value="canceled">Dibatalkan</option>
                </select>
            </div>

            <!-- Payment Method -->
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                <select id="payment_method" x-model="filters.payment_method" @change="fetchTransactions"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Semua Metode</option>
                    <option value="cash">Tunai</option>
                    <option value="bpjs">BPJS</option>
                    <option value="insurance">Asuransi</option>
                </select>
            </div>

            <!-- Date Range -->
            <div class="flex space-x-2">
                <div class="flex-1">
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                    <input type="date" id="start_date" x-model="filters.start_date" @change="fetchTransactions"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex-1">
                    <label for="end_date" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" id="end_date" x-model="filters.end_date" @change="fetchTransactions"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>
        </div>
    </div>

    <!-- New Transaction Button -->
    <div class="flex justify-end">
        <button @click="showCreateForm"
                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            + Transaksi Baru
        </button>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metode Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <template x-if="loading">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center">
                                <svg class="animate-spin h-5 w-5 text-blue-600 mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </td>
                        </tr>
                    </template>
                    <template x-if="!loading && !transactions.length">
                        <tr>
                            <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                Tidak ada data transaksi
                            </td>
                        </tr>
                    </template>
                    <template x-for="transaction in transactions" :key="transaction.id">
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="transaction.transaction_number"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="transaction.patient.nama_lengkap"></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatDate(transaction.visit_date)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-transactions.components.status-badge x-bind:status="transaction.status" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="formatPrice(transaction.final_amount)"></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <x-transactions.components.payment-badge x-bind:method="transaction.payment_method" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-3">
                                    <button @click="showDetail(transaction)"
                                            class="text-indigo-600 hover:text-indigo-900">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button @click="printReceipt(transaction)"
                                            class="text-gray-600 hover:text-gray-900">
                                        <i class="fas fa-print"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @include('transactions.components.admin-verify-modal')
    @include('transactions.components.payment-modal')
    @include('transactions.components.transaction-item-row')

    <!-- Sliding Panels for Create/Edit/Detail -->
    <div x-show="activePanel === 'create'" x-cloak>
        <x-sliding-panel title="Buat Transaksi Baru">
            <livewire:transaction-form />
        </x-sliding-panel>
    </div>

    <div x-show="activePanel === 'detail'" x-cloak>
        <x-sliding-panel title="Detail Transaksi">
            <livewire:transaction-detail />
        </x-sliding-panel>
    </div>
</div>

@push('scripts')
<script>
function transactionsIndex() {
    return {
        transactions: [],
        filters: {
            search: '',
            status: '',
            payment_method: '',
            start_date: '',
            end_date: ''
        },
        loading: false,
        activePanel: null,

        async fetchTransactions() {
            this.loading = true;
            try {
                const params = new URLSearchParams(this.filters);
                const response = await fetch(`/api/v1/transactions?${params}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.transactions = data.data.data;
                }
            } catch (error) {
                console.error('Error fetching transactions:', error);
                notify('Gagal memuat data transaksi', 'error');
            } finally {
                this.loading = false;
            }
        },

        showCreateForm() {
            this.activePanel = 'create';
        },

        showDetail(transaction) {
            this.$dispatch('transaction-loaded', transaction);
            this.activePanel = 'detail';
        },

        printReceipt(transaction) {
            window.open(`/api/v1/transactions/${transaction.id}/receipt`, '_blank');
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('id-ID', {
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }
    }
}
</script>
@endpush
