<div x-data="reviseModal()"
     @revise-prescription.window="showModal($event.detail)">
    
    <x-modal title="Revisi Resep" size="md">
        <form @submit.prevent="submitForm">
            <div class="space-y-4">
                <!-- Current Status -->
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status Saat Ini</label>
                    <div class="mt-1">
                        <x-prescriptions.components.status-badge x-bind:status="prescription.status" />
                    </div>
                </div>

                <!-- Revision Reason -->
                <div>
                    <label for="revised_reason" class="block text-sm font-medium text-gray-700">
                        Alasan Revisi <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1">
                        <textarea id="revised_reason"
                                x-model="form.revised_reason"
                                rows="3"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                placeholder="Masukkan alasan revisi..."></textarea>
                        <div x-show="errors.revised_reason" 
                             class="mt-1 text-sm text-red-600" 
                             x-text="errors.revised_reason"></div>
                    </div>
                </div>

                <!-- Warning -->
                <div class="rounded-md bg-yellow-50 p-4">
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
                                    Resep yang sudah direvisi tidak dapat diubah kembali. Pastikan Anda yakin sebelum melanjutkan.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="mt-6 flex justify-end space-x-3">
                <button type="button"
                        @click="$dispatch('close-modal')"
                        class="inline-flex justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Batal
                </button>
                <button type="submit"
                        :disabled="isSubmitting || !form.revised_reason"
                        class="inline-flex justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                    <span x-show="!isSubmitting">Revisi</span>
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
function reviseModal() {
    return {
        prescription: {},
        form: {
            revised_reason: ''
        },
        errors: {},
        isSubmitting: false,

        showModal(prescription) {
            this.prescription = prescription;
            this.form.revised_reason = '';
            this.errors = {};
        },

        async submitForm() {
            if (!this.form.revised_reason) return;

            this.isSubmitting = true;
            try {
                const response = await fetch(`/api/v1/prescriptions/${this.prescription.id}/revise`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(this.form)
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Resep berhasil direvisi');
                    this.$dispatch('prescription-revised', data.data);
                    this.$dispatch('close-modal');
                } else {
                    if (data.errors) {
                        this.errors = data.errors;
                    } else {
                        throw new Error(data.message || 'Terjadi kesalahan');
                    }
                }
            } catch (error) {
                console.error('Error revising prescription:', error);
                notify(error.message || 'Gagal merevisi resep', 'error');
            } finally {
                this.isSubmitting = false;
            }
        }
    }
}
</script>
@endpush
