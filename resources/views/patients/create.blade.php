@extends('layouts.app')

@section('title', 'Add New Patient')
@section('header', 'Add New Patient')

@section('content')
<div x-data="patientForm()" class="max-w-3xl mx-auto">
    <form @submit.prevent="submitForm" class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl md:col-span-2">
        <div class="px-4 py-6 sm:p-8">
            <div class="grid max-w-2xl grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                <!-- Nama Lengkap -->
                <div class="sm:col-span-4">
                    <label for="nama_lengkap" class="block text-sm font-medium leading-6 text-gray-900">
                        Nama Lengkap
                    </label>
                    <div class="mt-2">
                        <input type="text" 
                               name="nama_lengkap" 
                               id="nama_lengkap" 
                               x-model="form.nama_lengkap"
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <div x-show="errors.nama_lengkap" 
                             x-text="errors.nama_lengkap" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- NIK -->
                <div class="sm:col-span-4">
                    <label for="nik" class="block text-sm font-medium leading-6 text-gray-900">
                        NIK
                    </label>
                    <div class="mt-2">
                        <input type="text" 
                               name="nik" 
                               id="nik" 
                               x-model="form.nik"
                               maxlength="16"
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <div x-show="errors.nik" 
                             x-text="errors.nik" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- Tanggal Lahir -->
                <div class="sm:col-span-3">
                    <label for="tanggal_lahir" class="block text-sm font-medium leading-6 text-gray-900">
                        Tanggal Lahir
                    </label>
                    <div class="mt-2">
                        <input type="date" 
                               name="tanggal_lahir" 
                               id="tanggal_lahir" 
                               x-model="form.tanggal_lahir"
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <div x-show="errors.tanggal_lahir" 
                             x-text="errors.tanggal_lahir" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div class="sm:col-span-3">
                    <label for="jenis_kelamin" class="block text-sm font-medium leading-6 text-gray-900">
                        Jenis Kelamin
                    </label>
                    <div class="mt-2">
                        <select name="jenis_kelamin" 
                                id="jenis_kelamin" 
                                x-model="form.jenis_kelamin"
                                class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki">Laki-laki</option>
                            <option value="Perempuan">Perempuan</option>
                        </select>
                        <div x-show="errors.jenis_kelamin" 
                             x-text="errors.jenis_kelamin" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- Alamat -->
                <div class="col-span-full">
                    <label for="alamat" class="block text-sm font-medium leading-6 text-gray-900">
                        Alamat
                    </label>
                    <div class="mt-2">
                        <textarea name="alamat" 
                                  id="alamat" 
                                  x-model="form.alamat"
                                  rows="3" 
                                  class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"></textarea>
                        <div x-show="errors.alamat" 
                             x-text="errors.alamat" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>

                <!-- No HP -->
                <div class="sm:col-span-4">
                    <label for="no_hp" class="block text-sm font-medium leading-6 text-gray-900">
                        No HP
                    </label>
                    <div class="mt-2">
                        <input type="text" 
                               name="no_hp" 
                               id="no_hp" 
                               x-model="form.no_hp"
                               class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        <div x-show="errors.no_hp" 
                             x-text="errors.no_hp" 
                             class="mt-1 text-sm text-red-600"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 px-4 py-4 sm:px-8">
            <a href="{{ route('patients.index') }}" 
               class="text-sm font-semibold leading-6 text-gray-900">
                Cancel
            </a>
            <button type="submit" 
                    :disabled="isSubmitting"
                    class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
                <span x-show="!isSubmitting">Save</span>
                <span x-show="isSubmitting">Saving...</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function patientForm() {
    return {
        form: {
            nama_lengkap: '',
            nik: '',
            tanggal_lahir: '',
            jenis_kelamin: '',
            alamat: '',
            no_hp: ''
        },
        errors: {},
        isSubmitting: false,

        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};

            try {
                const response = await fetch('/api/v1/patients', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Patient added successfully');
                    window.location.href = '/patients';
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Something went wrong');
                    }
                }
            } catch (error) {
                console.error('Error creating patient:', error);
                notify(error.message || 'Failed to create patient', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
