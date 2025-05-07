@extends('layouts.app')

@section('title', 'Edit Kunjungan')
@section('header', 'Edit Kunjungan')

@section('content')
<div x-data="visitForm({{ json_encode($visit) }})" 
     x-init="initForm"
     class="max-w-3xl mx-auto">
    <form @submit.prevent="submitForm" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <!-- Queue Number (Read-only) -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Nomor Antrian"
                        name="queue_number"
                        type="text"
                        :value="$visit->queue_number"
                        disabled
                    />
                </div>

                <!-- Patient Info (Read-only) -->
                <div class="col-span-full">
                    <label class="block text-sm font-medium leading-6 text-gray-900">
                        Data Pasien
                    </label>
                    <div class="mt-2 p-3 bg-gray-50 rounded-md">
                        <div class="text-sm">
                            <div class="font-medium">{{ $visit->patient->nama_lengkap }}</div>
                            <div class="text-gray-500">No. RM: {{ $visit->patient->nomor_rm }}</div>
                            <div class="text-gray-500">NIK: {{ $visit->patient->nik }}</div>
                        </div>
                    </div>
                </div>

                <!-- Visit Date -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Tanggal Kunjungan"
                        name="visit_date"
                        type="date"
                        x-model="form.visit_date"
                        :required="true"
                        :error="$errors->first('visit_date')"
                    />
                </div>

                <!-- Status -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Status"
                        name="status"
                        type="select"
                        x-model="form.status"
                        :required="true"
                        :error="$errors->first('status')"
                    >
                        <option value="">Pilih Status</option>
                        @foreach($statuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-form-input>
                </div>

                <!-- Clinic Selection -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Klinik"
                        name="clinic_id"
                        type="select"
                        x-model="form.clinic_id"
                        @change="loadDoctors"
                        :required="true"
                        :error="$errors->first('clinic_id')"
                    >
                        <option value="">Pilih Klinik</option>
                        @foreach($clinics as $clinic)
                            <option value="{{ $clinic->id }}">{{ $clinic->name }}</option>
                        @endforeach
                    </x-form-input>
                </div>

                <!-- Doctor Selection -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Dokter"
                        name="doctor_id"
                        type="select"
                        x-model="form.doctor_id"
                        :required="true"
                        :error="$errors->first('doctor_id')"
                        :disabled="!form.clinic_id"
                    >
                        <option value="">Pilih Dokter</option>
                        <template x-for="doctor in doctors" :key="doctor.id">
                            <option :value="doctor.id" x-text="doctor.name"></option>
                        </template>
                    </x-form-input>
                </div>

                <!-- Visit Type -->
                <div class="sm:col-span-3">
                    <x-form-input
                        label="Jenis Kunjungan"
                        name="visit_type"
                        type="select"
                        x-model="form.visit_type"
                        :required="true"
                        :error="$errors->first('visit_type')"
                    >
                        <option value="">Pilih Jenis Kunjungan</option>
                        @foreach($visitTypes as $type)
                            <option value="{{ $type }}">{{ ucfirst($type) }}</option>
                        @endforeach
                    </x-form-input>
                </div>

                <!-- Remarks -->
                <div class="col-span-full">
                    <x-form-input
                        label="Catatan"
                        name="remarks"
                        type="textarea"
                        x-model="form.remarks"
                        :error="$errors->first('remarks')"
                        placeholder="Tambahkan catatan jika diperlukan..."
                    />
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
            <a href="{{ route('visits.index') }}" 
               class="text-sm font-semibold leading-6 text-gray-900">
                Batal
            </a>
            <button type="submit" 
                    :disabled="isSubmitting"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                <span x-show="!isSubmitting">Simpan</span>
                <span x-show="isSubmitting">Menyimpan...</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function visitForm(visit) {
    return {
        form: {
            visit_date: '',
            clinic_id: '',
            doctor_id: '',
            visit_type: '',
            status: '',
            remarks: ''
        },
        doctors: [],
        errors: {},
        isSubmitting: false,

        initForm() {
            this.form = {
                visit_date: visit.visit_date,
                clinic_id: visit.clinic_id.toString(),
                doctor_id: visit.doctor_id.toString(),
                visit_type: visit.visit_type,
                status: visit.status,
                remarks: visit.remarks || ''
            };
            this.loadDoctors();
        },

        async loadDoctors() {
            if (!this.form.clinic_id) {
                this.doctors = [];
                this.form.doctor_id = '';
                return;
            }

            try {
                const response = await fetch(`/visits/doctors-by-clinic/${this.form.clinic_id}`);
                const data = await response.json();
                this.doctors = data;
            } catch (error) {
                console.error('Error loading doctors:', error);
                notify('Gagal memuat daftar dokter', 'error');
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};

            try {
                const response = await fetch(`/api/v1/visits/${visit.id}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Kunjungan berhasil diperbarui');
                    window.location.href = '/visits';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error updating visit:', error);
                notify(error.message || 'Gagal memperbarui kunjungan', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
