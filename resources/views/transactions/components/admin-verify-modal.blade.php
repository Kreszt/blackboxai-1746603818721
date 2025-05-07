<div x-data="adminVerifyModal()"
     @verify-admin.window="showModal"
     @admin-verified.window="handleVerified">
    
    <x-modal title="Verifikasi Admin" size="sm">
        <div class="space-y-4">
            <!-- Warning Message -->
            <div class="rounded-md bg-yellow-50 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495zM10 5a.75.75 0 01.75.75v3.5a.75.75 0 01-1.5 0v-3.5A.75.75 0 0110 5zm0 9a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-yellow-800">
                            Perhatian
                        </h3>
                        <div class="mt-2 text-sm text-yellow-700">
                            <p>
                                Penambahan item manual memerlukan verifikasi admin. Silakan masukkan password admin untuk melanjutkan.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Password Input -->
            <div>
                <label for="admin_password" class="block text-sm font-medium text-gray-700">
                    Password Admin <span class="text-red-500">*</span>
                </label>
                <div class="mt-1">
                    <input type="password"
                           id="admin_password"
                           x-model="password"
                           @keydown.enter.prevent="verifyPassword"
                           class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                           placeholder="Masukkan password admin">
                </div>
                <div x-show="error" 
                     class="mt-1 text-sm text-red-600" 
                     x-text="error"></div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 flex justify-end space-x-3">
            <button type="button"
                    @click="$dispatch('close-modal')"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Batal
            </button>
            <button type="button"
                    @click="verifyPassword"
                    :disabled="isVerifying || !password"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                <span x-show="!isVerifying">Verifikasi</span>
                <span x-show="isVerifying">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memverifikasi...
                </span>
            </button>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function adminVerifyModal() {
    return {
        password: '',
        error: '',
        isVerifying: false,
        callback: null,

        showModal(event) {
            this.password = '';
            this.error = '';
            this.callback = event.detail;
        },

        async verifyPassword() {
            if (!this.password) return;

            this.isVerifying = true;
            this.error = '';

            try {
                const response = await fetch('/api/v1/admin/verify', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        password: this.password
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    this.$dispatch('admin-verified');
                    this.$dispatch('close-modal');
                } else {
                    this.error = data.message || 'Password admin tidak valid';
                }
            } catch (error) {
                console.error('Error verifying admin:', error);
                this.error = 'Terjadi kesalahan saat verifikasi';
            } finally {
                this.isVerifying = false;
            }
        },

        handleVerified() {
            if (this.callback) {
                this.callback();
            }
            this.callback = null;
        }
    }
}
</script>
@endpush
