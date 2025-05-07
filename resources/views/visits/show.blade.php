@extends('layouts.app')

@section('title', 'Detail Kunjungan')
@section('header', 'Detail Kunjungan')

@section('content')
<div class="max-w-3xl mx-auto">
    <!-- Actions -->
    <div class="mb-6 flex justify-end space-x-4">
        <a href="{{ route('visits.index') }}" 
           class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali
        </a>
        @if(!in_array($visit->status, ['completed', 'cancelled']))
            <a href="{{ route('visits.edit', $visit) }}" 
               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <i class="fas fa-edit mr-2"></i>
                Edit
            </a>
        @endif
    </div>

    <div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl">
        <!-- Queue and Status Information -->
        <div class="px-4 py-6 sm:px-6 flex items-center justify-between border-b border-gray-200">
            <div>
                <h3 class="text-lg font-medium leading-6 text-gray-900">
                    Nomor Antrian: {{ $visit->queue_number }}
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    Tanggal: {{ \Carbon\Carbon::parse($visit->visit_date)->format('d F Y') }}
                </p>
            </div>
            <x-status-badge :status="$visit->status" class="text-base" />
        </div>

        <div class="px-4 py-6 sm:px-6">
            <!-- Patient Information -->
            <div class="mb-8">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Data Pasien</h4>
                <div class="bg-gray-50 rounded-lg p-4">
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->patient->nama_lengkap }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nomor RM</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->patient->nomor_rm }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">NIK</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->patient->nik }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Lahir</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($visit->patient->tanggal_lahir)->format('d F Y') }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Jenis Kelamin</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->patient->jenis_kelamin }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">No HP</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->patient->no_hp }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Visit Information -->
            <div class="mb-8">
                <h4 class="text-sm font-medium text-gray-900 mb-4">Data Kunjungan</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Klinik</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $visit->clinic->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dokter</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $visit->doctor->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Jenis Kunjungan</dt>
                        <dd class="mt-1">
                            <x-visit-type-badge :type="$visit->visit_type" />
                        </dd>
                    </div>
                    @if($visit->remarks)
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Catatan</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $visit->remarks }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <!-- Audit Information -->
            <div>
                <h4 class="text-sm font-medium text-gray-900 mb-4">Informasi Audit</h4>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="font-medium text-gray-500">Dibuat Oleh</dt>
                        <dd class="mt-1 text-gray-900">
                            {{ $visit->creator->name ?? 'System' }}
                            <div class="text-gray-500">
                                {{ $visit->created_at->format('d M Y H:i') }}
                            </div>
                        </dd>
                    </div>
                    @if($visit->updated_by)
                        <div>
                            <dt class="font-medium text-gray-500">Terakhir Diubah</dt>
                            <dd class="mt-1 text-gray-900">
                                {{ $visit->updater->name }}
                                <div class="text-gray-500">
                                    {{ $visit->updated_at->format('d M Y H:i') }}
                                </div>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
