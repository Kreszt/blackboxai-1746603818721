@props(['mode' => 'create'])

<div x-data="journalForm('{{ $mode }}')"
     @journal-edit.window="editJournal($event.detail)"
     @journal-create.window="resetForm">
    
    <x-modal :title="mode === 'create' ? 'Tambah Catatan Medis' : 'Edit Catatan Medis'" size="xl">
        <form @submit.prevent="submitForm">
            <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                <!-- Patient Search (only for create mode) -->
                <div x-show="mode === 'create'" class="sm:col-span-6" x-data="{ open: false }">
                    <label for="patient_search" class="block text-sm font-medium text-gray-700">
                        Cari Pasien <span class="text-red-500">*</span>
                    </label>
                    <div class="relative mt-1">
                        <input type="text"
                               id="patient_search"
                               x-model="patientSearch"
                               @input.debounce.300ms="searchPatients"
                               @click="open = true"
                               @click.away="open = false"
                               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                               placeholder="Cari berdasarkan nama atau nomor RM...">
                        
                        <!-- Search Results Dropdown -->
                        <div x-show="open && patients.length > 0"
                             class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                            <ul class="max-h-60 overflow-auto py-1">
                                <template x-for="patient in patients" :key="patient.id">
                                    <li>
                                        <button type="button"
                                                @click="selectPatient(patient)"
                                                class="w-full px-4 py-2 text-left hover:bg-gray-100">
                                            <div class="font-medium" x-text="patient.nama_lengkap"></div>
                                            <div class="text-sm text-gray-500" x-text="'No. RM: ' + patient.nomor_rm"></div>
                                        </button>
                                    </li>
                                </template>
                            </ul>
                        </div>
                    </div>
                    <div x-show="errors.medical_record_id" class="mt-1 text-sm text-red-600" x-text="errors.medical_record_id"></div>
                </div>

                <!-- Selected Patient Info -->
                <div x-show="selectedPatient" class="sm:col-span-6">
                    <div class="rounded-md bg-gray-50 p-4">
                        <div class="font-medium" x-text="selectedPatient?.nama_lengkap"></div>
                        <div class="text-sm text-gray-500">
                            <span x-text="'No. RM: ' + selectedPatient?.nomor_rm"></span>
                            <span class="mx-2">|</span>
                            <span x-text="'NIK: ' + selectedPatient?.nik"></span>
                        </div>
                    </div>
                </div>

                <!-- Visit Selection (only for create mode) -->
                <div x-show="mode === 'create'" class="sm:col-span-6">
                    <label for="visit_id" class="block text-sm font-medium text-gray-700">
                        Kunjungan <span class="text-red-500">*</span>
                    </label>
                    <select id="visit_id"
                            x-model="form.visit_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Pilih Kunjungan</option>
                        <template x-for="visit in availableVisits" :key="visit.id">
                            <option :value="visit.id" 
                                    x-text="visit.date + ' - ' + visit.clinic.name + ' - ' + visit.doctor.name">
                            </option>
                        </template>
                    </select>
                    <div x-show="errors.visit_id" class="mt-1 text-sm text-red-600" x-text="errors.visit_id"></div>
                </div>

                <!-- Complaint -->
                <div class="sm:col-span-6">
                    <label for="complaint" class="block text-sm font-medium text-gray-700">
                        Keluhan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="complaint"
                            x-model="form.complaint"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <div x-show="errors.complaint" class="mt-1 text-sm text-red-600" x-text="errors.complaint"></div>
                </div>

                <!-- Diagnosis -->
                <div class="sm:col-span-6">
                    <label for="diagnosis" class="block text-sm font-medium text-gray-700">
                        Diagnosis <span class="text-red-500">*</span>
                    </label>
                    <textarea id="diagnosis"
                            x-model="form.diagnosis"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <div x-show="errors.diagnosis" class="mt-1 text-sm text-red-600" x-text="errors.diagnosis"></div>
                </div>

                <!-- Treatment -->
                <div class="sm:col-span-6">
                    <label for="treatment" class="block text-sm font-medium text-gray-700">
                        Tindakan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="treatment"
                            x-model="form.treatment"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <div x-show="errors.treatment" class="mt-1 text-sm text-red-600" x-text="errors.treatment"></div>
                </div>

                <!-- Prescription -->
                <div class="sm:col-span-6">
                    <label for="prescription" class="block text-sm font-medium text-gray-700">
                        Resep
                    </label>
                    <textarea id="prescription"
                            x-model="form.prescription"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <div x-show="errors.prescription" class="mt-1 text-sm text-red-600" x-text="errors.prescription"></div>
                </div>

                <!-- Referral -->
                <div class="sm:col-span-6">
                    <label for="referral" class="block text-sm font-medium text-gray-700">
                        Rujukan
                    </label>
                    <textarea id="referral"
                            x-model="form.referral"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"></textarea>
                    <div x-show="errors.referral" class="mt-1 text-sm text-red-600" x-text="errors.referral"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button"
                        @click="$dispatch('close-modal')"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Batal
                </button>
                <button type="submit"
                        :disabled="isSubmitting"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                    <span x-show="!isSubmitting" x-text="mode === 'create' ? 'Simpan' : 'Update'"></span>
                    <span x-show="isSubmitting">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Menyimpan...
                    </span>
                </button>
            </div>
        </form>
    </x-modal>
</div>

@push('scripts')
<script>
function journalForm(mode = 'create') {
    return {
        mode: mode,
        form: {
            medical_record_id: '',
            visit_id: '',
            complaint: '',
            diagnosis: '',
            treatment: '',
            prescription: '',
            referral: ''
        },
        patientSearch: '',
        patients: [],
        selectedPatient: null,
        availableVisits: [],
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

        async selectPatient(patient) {
            this.selectedPatient = patient;
            this.patientSearch = patient.nama_lengkap;
            this.patients = [];
            this.form.medical_record_id = patient.medical_record.id;
            
            // Load available visits for this patient
            await this.loadAvailableVisits(patient.id);
        },

        async loadAvailableVisits(patientId) {
            try {
                const response = await fetch(`/api/v1/visits?patient_id=${patientId}&status=waiting`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.availableVisits = data.data.data;
                }
            } catch (error) {
                console.error('Error loading visits:', error);
            }
        },

        async submitForm() {
            this.isSubmitting = true;
            this.errors = {};

            try {
                const url = this.mode === 'create' 
                    ? '/api/v1/medical-journals'
                    : `/api/v1/medical-journals/${this.form.id}`;

                const method = this.mode === 'create' ? 'POST' : 'PUT';

                const response = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.$dispatch('journal-saved', { mode: this.mode });
                    this.$dispatch('close-modal');
                    this.resetForm();
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error submitting form:', error);
            } finally {
                this.isSubmitting = false;
            }
        },

        editJournal(journal) {
            this.mode = 'edit';
            this.form = {
                id: journal.id,
                medical_record_id: journal.medical_record_id,
                visit_id: journal.visit_id,
                complaint: journal.complaint,
                diagnosis: journal.diagnosis,
                treatment: journal.treatment,
                prescription: journal.prescription || '',
                referral: journal.referral || ''
            };
            this.selectedPatient = journal.medical_record.patient;
        },

        resetForm() {
            this.form = {
                medical_record_id: '',
                visit_id: '',
                complaint: '',
                diagnosis: '',
                treatment: '',
                prescription: '',
                referral: ''
            };
            this.selectedPatient = null;
            this.patientSearch = '';
            this.errors = {};
        }
    }
}
</script>
@endpush
