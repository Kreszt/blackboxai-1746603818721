<div x-data="journalStatus()"
     @update-journal-status.window="showStatusUpdate($event.detail)">
    
    <x-modal title="Update Status Catatan Medis" size="md">
        <div class="space-y-6">
            <!-- Loading State -->
            <div x-show="loading" class="flex justify-center py-8">
                <svg class="animate-spin h-8 w-8 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
            </div>

            <div x-show="!loading">
                <form @submit.prevent="updateStatus">
                    <!-- Current Status -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700">Status Saat Ini</label>
                        <div class="mt-1">
                            <x-medical-journals.components.status-badge x-bind:status="journal.status" />
                        </div>
                    </div>

                    <!-- New Status -->
                    <div class="mb-4">
                        <label for="status" class="block text-sm font-medium text-gray-700">
                            Status Baru <span class="text-red-500">*</span>
                        </label>
                        <select id="status"
                                x-model="form.status"
                                @change="checkReferral"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Pilih Status</option>
                            <template x-for="status in availableStatuses" :key="status.value">
                                <option :value="status.value" x-text="status.label"></option>
                            </template>
                        </select>
                        <div x-show="errors.status" class="mt-1 text-sm text-red-600" x-text="errors.status"></div>
                    </div>

                    <!-- Referral Note (shown only when status is 'referred') -->
                    <div x-show="form.status === 'referred'" class="mb-4">
                        <label for="referral" class="block text-sm font-medium text-gray-700">
                            Catatan Rujukan <span class="text-red-500">*</span>
                        </label>
                        <textarea id="referral"
                                x-model="form.referral"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Masukkan detail rujukan..."></textarea>
                        <div x-show="errors.referral" class="mt-1 text-sm text-red-600" x-text="errors.referral"></div>
                    </div>

                    <!-- Warning Message -->
                    <div class="rounded-md bg-yellow-50 p-4 mb-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">
                                    Perhatian
                                </h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p>
                                        Perubahan status tidak dapat dibatalkan. Pastikan Anda yakin sebelum melanjutkan.
                                    </p>
                                </div>
                            </div>
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
                                :disabled="isSubmitting || !form.status"
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50">
                            <span x-show="!isSubmitting">Update Status</span>
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
            </div>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function journalStatus() {
    return {
        journal: {},
        form: {
            status: '',
            referral: ''
        },
        errors: {},
        loading: false,
        isSubmitting: false,
        availableStatuses: [
            { value: 'completed', label: 'Selesai' },
            { value: 'referred', label: 'Dirujuk' }
        ],

        showStatusUpdate(journal) {
            this.journal = journal;
            this.form.status = '';
            this.form.referral = '';
            this.errors = {};
        },

        checkReferral() {
            if (this.form.status === 'referred') {
                this.form.referral = this.journal.referral || '';
            } else {
                this.form.referral = '';
            }
        },

        async updateStatus() {
            if (!this.form.status) return;

            this.isSubmitting = true;
            this.errors = {};

            try {
                const response = await fetch(`/api/v1/medical-journals/${this.journal.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    this.$dispatch('journal-status-updated', data.data);
                    this.$dispatch('close-modal');
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error updating status:', error);
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
