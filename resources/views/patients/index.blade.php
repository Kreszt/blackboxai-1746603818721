@extends('layouts.app')

@section('title', 'Patients List')
@section('header', 'Patients Management')

@section('content')
<div x-data="patientsIndex()" x-init="fetchPatients">
    <!-- Top Section: Search and Add Button -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4">
        <!-- Search Bar -->
        <div class="w-full sm:w-96">
            <input 
                type="text" 
                x-model="searchQuery" 
                @input="handleSearch"
                placeholder="Search by RM number, name, or NIK..." 
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500"
            >
        </div>
        
        <!-- Add New Patient Button -->
        <a href="{{ route('patients.create') }}" 
           class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
            <svg class="-ml-1 mr-2 h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Add New Patient
        </a>
    </div>

    <!-- Patients Table -->
    <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nomor RM</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Lengkap</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Lahir</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No HP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <template x-if="!patients.length">
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                            No patients found
                        </td>
                    </tr>
                </template>
                <template x-for="patient in patients" :key="patient.id">
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="patient.nomor_rm"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="patient.nama_lengkap"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="patient.nik"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="formatDate(patient.tanggal_lahir)"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="patient.jenis_kelamin"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500" x-text="patient.no_hp"></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a :href="'/patients/' + patient.id + '/edit'" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <button @click="confirmDelete(patient)" 
                                        class="text-red-600 hover:text-red-900">Delete</button>
                            </div>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="mt-4 flex justify-between items-center">
        <div class="text-sm text-gray-700">
            Showing <span x-text="pagination.from"></span> to <span x-text="pagination.to"></span> of <span x-text="pagination.total"></span> results
        </div>
        <div class="flex space-x-2">
            <button @click="changePage(pagination.current_page - 1)"
                    :disabled="!pagination.prev_page_url"
                    :class="{'opacity-50 cursor-not-allowed': !pagination.prev_page_url}"
                    class="px-3 py-1 border rounded text-sm">
                Previous
            </button>
            <button @click="changePage(pagination.current_page + 1)"
                    :disabled="!pagination.next_page_url"
                    :class="{'opacity-50 cursor-not-allowed': !pagination.next_page_url}"
                    class="px-3 py-1 border rounded text-sm">
                Next
            </button>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div x-show="showDeleteModal" 
         x-cloak
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
                            <svg class="h-6 w-6 text-red-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Delete Patient
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Are you sure you want to delete this patient? This action cannot be undone.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button @click="deletePatient" 
                            type="button" 
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:ml-3 sm:w-auto sm:text-sm">
                        Delete
                    </button>
                    <button @click="showDeleteModal = false" 
                            type="button" 
                            class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function patientsIndex() {
    return {
        patients: [],
        pagination: {
            current_page: 1,
            from: 1,
            to: 1,
            total: 0,
            prev_page_url: null,
            next_page_url: null
        },
        searchQuery: '',
        searchTimeout: null,
        showDeleteModal: false,
        patientToDelete: null,

        async fetchPatients(page = 1) {
            try {
                const response = await fetch(`/api/v1/patients?page=${page}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.patients = data.data.data;
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
                console.error('Error fetching patients:', error);
                notify('Failed to load patients', 'error');
            }
        },

        handleSearch() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(async () => {
                if (this.searchQuery.length > 0) {
                    try {
                        const response = await fetch(`/api/v1/patients/search?nama=${this.searchQuery}`);
                        const data = await response.json();
                        
                        if (data.status === 'success') {
                            this.patients = data.data.data;
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
                        console.error('Error searching patients:', error);
                        notify('Failed to search patients', 'error');
                    }
                } else {
                    this.fetchPatients();
                }
            }, 300);
        },

        changePage(page) {
            if (page > 0) {
                this.fetchPatients(page);
            }
        },

        confirmDelete(patient) {
            this.patientToDelete = patient;
            this.showDeleteModal = true;
        },

        async deletePatient() {
            if (!this.patientToDelete) return;

            try {
                const response = await fetch(`/api/v1/patients/${this.patientToDelete.id}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();
                
                if (data.status === 'success') {
                    notify('Patient deleted successfully');
                    this.fetchPatients(this.pagination.current_page);
                } else {
                    throw new Error(data.message);
                }
            } catch (error) {
                console.error('Error deleting patient:', error);
                notify('Failed to delete patient', 'error');
            }

            this.showDeleteModal = false;
            this.patientToDelete = null;
        },

        formatDate(date) {
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
