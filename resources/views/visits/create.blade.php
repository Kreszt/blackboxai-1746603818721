@extends('layouts.app')

@section('title', 'Tambah Kunjungan Baru')
@section('header', 'Tambah Kunjungan Baru')

@section('content')
<div x-data="visitForm()" class="max-w-3xl mx-auto">
    <form @submit.prevent="submitForm" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <!-- Patient Search -->
                <div class="col-span-full" x-data="{ open: false }">
                    <label for="patient_search" class="block text-sm font-medium leading-6 text-gray-900">
                        Cari Pasien <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-2">
                        <input
                            type="text"
                            id="patient_search"
                            x-model="patientSearch"
                            @input.debounce.300ms="searchPatients"
                            @click="open = true"
                            @click.away="open = false"
                            placeholder="Cari berdasarkan nama atau nomor RM..."
                            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
                        >
                        <!-- Search Results Dropdown -->
                        <div x-show="open && patients.length > 0" 
                             class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 overflow-hidden">
                            <ul class="divide-y divide-gray-200 max-h-60 overflow-auto">
                                <template x-for="patient in patients" :key="patient.id">
                                    <li>
                                        <button type="button"
                                                @click="selectPatient(patient)"
                                                class="w-full px-4 py-2 text-left hover:bg-gray-50">
                                            <div class="font-medium" x-text="patient.nama_lengkap"></div>
                                            <div class="text-sm text-gray-500" x-text="'No. RM: ' + patient.nomor_rm"></div>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div x-show="selectedPatient" class="mt-2 p-3 bg-gray-50 rounded-md">
                        <div class="text-sm">
                            <div class="font-medium" x-text="selectedPatient?.nama_lengkap"></div>
                            <div class="text-gray-500" x-text="'No. RM: ' + selectedPatient?.nomor_rm"></div>
                            <div class="text-gray-500" x-text="'NIK: ' + selectedPatient?.nik"></div>
                        </div>
                    </div>
                    <div x-show="errors.patient_id" class="mt-2 text-sm text-red-600" x-text="errors.patient_id"></div>
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
function visitForm() {
    return {
        form: {
            patient_id: '',
            visit_date: new Date().toISOString().split('T')[0],
            clinic_id: '',
            doctor_id: '',
            visit_type: '',
            remarks: ''
        },
        patientSearch: '',
        patients: [],
        selectedPatient: null,
        doctors: [],
        errors: {},
        isSubmitting: false,

        async searchPatients() {
            if (this.patientSearch.length < 3) return;

            try {
                const response = await fetch(`/api/v1/patients/search?nama=${this.patientSearch}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.patients = data.data.data;
                }
            } catch (error) {
                console.error('Error searching patients:', error);
            }
        },

        selectPatient(patient) {
            this.selectedPatient = patient;
            this.form.patient_id = patient.id;
            this.patientSearch = patient.nama_lengkap;
            this.patients = [];
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
                this.form.doctor_id = '';
            } catch (error) {
                console.error('Error loading doctors:', error);
                notify('Gagal memuat daftar dokter', 'error');
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};

            try {
                const response = await fetch('/api/v1/visits', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Kunjungan berhasil ditambahkan');
                    window.location.href = '/visits';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error creating visit:', error);
                notify(error.message || 'Gagal menambahkan kunjungan', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
