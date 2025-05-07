@extends('layouts.app')

@section('title', 'Detail Transaksi')
@section('header', 'Detail Transaksi')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Card -->
    <div class="bg-white shadow rounded-lg p-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">No. Transaksi: {{ $transaction->transaction_number }}</h2>
            <p class="text-gray-600">Pasien: {{ $transaction->patient->nama_lengkap }}</p>
            <p class="text-gray-600">Tanggal Kunjungan: {{ $transaction->visit_date->format('d-m-Y') }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-4">
            <x-transactions.components.status-badge :status="$transaction->status" />
            <x-transactions.components.payment-badge :method="$transaction->payment_method" />
        </div>
    </div>

    <!-- Items Table -->
    <div class="bg-white shadow rounded-lg p-6 overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Layanan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Item</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Satuan</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($transaction->items as $item)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ $item->type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item->description }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">{{ $item->quantity }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Summary Box -->
    <div class="bg-white shadow rounded-lg p-6 max-w-md ml-auto">
        <dl class="space-y-4">
            <div class="flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Total</dt>
                <dd class="text-sm font-semibold text-gray-900">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</dd>
            </div>
            @if($transaction->discount > 0)
            <div class="flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Diskon</dt>
                <dd class="text-sm font-semibold text-gray-900">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</dd>
            </div>
            @endif
            <div class="flex justify-between">
                <dt class="text-sm font-medium text-gray-500">Total Bayar</dt>
                <dd class="text-lg font-bold text-gray-900">Rp {{ number_format($transaction->final_amount, 0, ',', '.') }}</dd>
            </div>
        </dl>

        <!-- Action Buttons -->
        <div class="mt-6 flex space-x-4">
            @if($transaction->status === 'unpaid')
            <button @click="$dispatch('confirm-payment', {{ json_encode($transaction) }})"
                    class="flex-1 inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Ubah Status Pembayaran
            </button>
            @endif
            <a href="{{ url('/api/v1/transactions/' . $transaction->id . '/receipt') }}" target="_blank"
               class="flex-1 inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                Cetak Struk
            </a>
        </div>
    </div>
</div>
@endsection
