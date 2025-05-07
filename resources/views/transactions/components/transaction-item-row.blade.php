@props(['index', 'errors' => [], 'isAdmin' => false])

<div class="grid grid-cols-12 gap-4 items-start" 
     x-data="transactionItemRow({ isAdmin: {{ json_encode($isAdmin) }} })"
     @admin-verified.window="handleAdminVerified">
    
    <!-- Item Type Selection -->
    <div class="col-span-3">
        <label :for="'items[' + index + '][type]'" class="block text-sm font-medium text-gray-700">
            Jenis Item <span class="text-red-500">*</span>
        </label>
        <select :name="'items[' + index + '][type]'"
                x-model="item.type"
                @change="handleTypeChange"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">Pilih Jenis</option>
            <option value="consultation">Konsultasi</option>
            <option value="prescription">Resep</option>
            <option value="manual" x-show="isAdmin || isVerified">Manual</option>
        </select>
        <div x-show="errors['items.' + index + '.type']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.type']"></div>
    </div>

    <!-- Reference Selection -->
    <div class="col-span-4" x-show="item.type && item.type !== 'manual'">
        <div x-data="{ open: false }">
            <label :for="'items[' + index + '][reference_search]'" class="block text-sm font-medium text-gray-700">
                Pilih Referensi <span class="text-red-500">*</span>
            </label>
            <div class="relative mt-1">
                <input type="text"
                       :name="'items[' + index + '][reference_search]'"
                       x-model="referenceSearch"
                       @input.debounce.300ms="searchReferences"
                       @click="open = true"
                       @click.away="open = false"
                       class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                       :placeholder="getPlaceholder()">
                
                <!-- Search Results Dropdown -->
                <div x-show="open && references.length > 0"
                     class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200">
                    <ul class="max-h-60 overflow-auto py-1">
                        <template x-for="ref in references" :key="ref.id">
                            <li>
                                <button type="button"
                                        @click="selectReference(ref)"
                                        class="w-full px-4 py-2 text-left hover:bg-gray-50">
                                    <div class="font-medium" x-text="getRefTitle(ref)"></div>
                                    <div class="text-sm text-gray-500" x-text="getRefSubtitle(ref)"></div>
                                </button>
                            </li>
                        </template>
                    </ul>
                </div>

                <!-- Hidden Reference Fields -->
                <input type="hidden" :name="'items[' + index + '][reference_id]'" x-model="item.reference_id">
                <input type="hidden" :name="'items[' + index + '][reference_type]'" x-model="item.reference_type">
            </div>
            <div x-show="errors['items.' + index + '.reference_id']" 
                 class="mt-1 text-sm text-red-600" 
                 x-text="errors['items.' + index + '.reference_id']"></div>
        </div>
    </div>

    <!-- Manual Item Description -->
    <div class="col-span-4" x-show="item.type === 'manual'">
        <label :for="'items[' + index + '][description]'" class="block text-sm font-medium text-gray-700">
            Deskripsi <span class="text-red-500">*</span>
        </label>
        <input type="text"
               :name="'items[' + index + '][description]'"
               x-model="item.description"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               placeholder="Masukkan deskripsi item">
        <div x-show="errors['items.' + index + '.description']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.description']"></div>
    </div>

    <!-- Price -->
    <div class="col-span-2">
        <label :for="'items[' + index + '][price]'" class="block text-sm font-medium text-gray-700">
            Harga <span class="text-red-500">*</span>
        </label>
        <input type="number"
               :name="'items[' + index + '][price]'"
               x-model="item.price"
               @input="calculateSubtotal"
               :readonly="item.type !== 'manual'"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
               :class="{ 'bg-gray-50': item.type !== 'manual' }">
        <div x-show="errors['items.' + index + '.price']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.price']"></div>
    </div>

    <!-- Quantity -->
    <div class="col-span-2">
        <label :for="'items[' + index + '][quantity]'" class="block text-sm font-medium text-gray-700">
            Jumlah <span class="text-red-500">*</span>
        </label>
        <input type="number"
               :name="'items[' + index + '][quantity]'"
               x-model="item.quantity"
               @input="calculateSubtotal"
               min="1"
               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <div x-show="errors['items.' + index + '.quantity']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.quantity']"></div>
    </div>

    <!-- Remove Button -->
    <div class="col-span-1 pt-6">
        <button type="button"
                @click="$dispatch('remove-item', index)"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

@push('scripts')
<script>
function transactionItemRow({ isAdmin }) {
    return {
        isAdmin,
        isVerified: false,
        item: {
            type: '',
            reference_id: '',
            reference_type: '',
            description: '',
            price: '',
            quantity: 1
        },
        referenceSearch: '',
        references: [],

        handleTypeChange() {
            this.item.reference_id = '';
            this.item.reference_type = '';
            this.item.description = '';
            this.item.price = '';
            this.referenceSearch = '';
            this.references = [];

            if (this.item.type === 'manual' && !this.isAdmin && !this.isVerified) {
                this.$dispatch('verify-admin', () => {
                    this.isVerified = true;
                });
            }
        },

        async searchReferences() {
            if (!this.referenceSearch || this.referenceSearch.length < 2) return;

            try {
                let endpoint = '';
                if (this.item.type === 'consultation') {
                    endpoint = `/api/v1/medical-journals/search?query=${this.referenceSearch}&status=completed`;
                } else if (this.item.type === 'prescription') {
                    endpoint = `/api/v1/prescriptions/search?query=${this.referenceSearch}&status=final`;
                }

                const response = await fetch(endpoint);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.references = data.data;
                }
            } catch (error) {
                console.error('Error searching references:', error);
            }
        },

        selectReference(ref) {
            this.item.reference_id = ref.id;
            this.item.reference_type = this.item.type === 'consultation' ? 'MedicalJournal' : 'Prescription';
            this.item.description = this.getRefTitle(ref);
            this.item.price = this.getRefPrice(ref);
            this.referenceSearch = this.getRefTitle(ref);
            this.references = [];
            this.calculateSubtotal();
        },

        getRefTitle(ref) {
            if (this.item.type === 'consultation') {
                return `Konsultasi - ${ref.clinic.name}`;
            } else if (this.item.type === 'prescription') {
                return `Resep #${ref.prescription_number}`;
            }
            return '';
        },

        getRefSubtitle(ref) {
            if (this.item.type === 'consultation') {
                return `${ref.doctor.name} - ${ref.visit_date}`;
            } else if (this.item.type === 'prescription') {
                return `${ref.items.length} item - ${ref.created_at}`;
            }
            return '';
        },

        getRefPrice(ref) {
            if (this.item.type === 'consultation') {
                return ref.consultation_fee || 0;
            } else if (this.item.type === 'prescription') {
                return ref.total_amount || 0;
            }
            return 0;
        },

        getPlaceholder() {
            if (this.item.type === 'consultation') {
                return 'Cari konsultasi...';
            } else if (this.item.type === 'prescription') {
                return 'Cari resep...';
            }
            return '';
        },

        calculateSubtotal() {
            const subtotal = this.item.price * this.item.quantity;
            this.$dispatch('update-subtotal', {
                index: this.index,
                subtotal: subtotal
            });
        },

        handleAdminVerified() {
            this.isVerified = true;
        }
    }
}
</script>
@endpush
