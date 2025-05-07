<div x-data="paymentModal()"
     @confirm-payment.window="showModal($event.detail)">
    
    <x-modal title="Konfirmasi Pembayaran" size="md">
        <div class="space-y-6">
            <!-- Transaction Summary -->
            <div class="bg-gray-50 rounded-lg p-4">
                <dl class="grid grid-cols-1 gap-y-2">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">No. Transaksi</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="transaction.transaction_number"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Pasien</dt>
                        <dd class="mt-1 text-sm text-gray-900" x-text="transaction.patient?.nama_lengkap"></dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Total Tagihan</dt>
                        <dd class="mt-1 text-lg font-semibold text-gray-900" x-text="formatPrice(transaction.final_amount)"></dd>
                    </div>
                </dl>
            </div>

            <!-- Payment Method -->
            <div>
                <label for="payment_method" class="block text-sm font-medium text-gray-700">
                    Metode Pembayaran <span class="text-red-500">*</span>
                </label>
                <div class="mt-2 space-y-4">
                    <div class="flex items-center">
                        <input id="payment_cash" 
                               name="payment_method" 
                               type="radio" 
                               value="cash"
                               x-model="form.payment_method"
                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="payment_cash" class="ml-3 block text-sm font-medium text-gray-700">
                            Tunai
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="payment_bpjs" 
                               name="payment_method" 
                               type="radio" 
                               value="bpjs"
                               x-model="form.payment_method"
                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="payment_bpjs" class="ml-3 block text-sm font-medium text-gray-700">
                            BPJS
                        </label>
                    </div>
                    <div class="flex items-center">
                        <input id="payment_insurance" 
                               name="payment_method" 
                               type="radio" 
                               value="insurance"
                               x-model="form.payment_method"
                               class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="payment_insurance" class="ml-3 block text-sm font-medium text-gray-700">
                            Asuransi
                        </label>
                    </div>
                </div>
                <div x-show="error" 
                     class="mt-2 text-sm text-red-600" 
                     x-text="error"></div>
            </div>

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
                                Setelah transaksi dibayar, status tidak dapat diubah kembali. Pastikan data pembayaran sudah benar sebelum melanjutkan.
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
                    class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                Batal
            </button>
            <button type="button"
                    @click="confirmPayment"
                    :disabled="isSubmitting || !form.payment_method"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                <span x-show="!isSubmitting">Konfirmasi Pembayaran</span>
                <span x-show="isSubmitting">
                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memproses...
                </span>
            </button>
        </div>
    </x-modal>
</div>

@push('scripts')
<script>
function paymentModal() {
    return {
        transaction: {},
        form: {
            payment_method: ''
        },
        error: '',
        isSubmitting: false,

        showModal(transaction) {
            this.transaction = transaction;
            this.form.payment_method = '';
            this.error = '';
        },

        async confirmPayment() {
            if (!this.form.payment_method) return;

            this.isSubmitting = true;
            this.error = '';

            try {
                const response = await fetch(`/api/v1/transactions/${this.transaction.id}/status`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        status: 'paid',
                        payment_method: this.form.payment_method
                    })
                });

                const data = await response.json();

                if (response.ok) {
                    notify('Pembayaran berhasil dikonfirmasi');
                    this.$dispatch('payment-confirmed', data.data);
                    this.$dispatch('close-modal');
                } else {
                    throw new Error(data.message || 'Terjadi kesalahan saat memproses pembayaran');
                }
            } catch (error) {
                console.error('Error confirming payment:', error);
                this.error = error.message || 'Terjadi kesalahan saat memproses pembayaran';
            } finally {
                this.isSubmitting = false;
            }
        },

        formatPrice(amount) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(amount);
        }
    }
}
</script>
@endpush
