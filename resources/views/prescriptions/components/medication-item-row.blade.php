@props(['index', 'errors' => []])

<div class="grid grid-cols-12 gap-4 items-start" x-data="medicationItemRow()">
    <!-- Medication Selection -->
    <div class="col-span-5" x-data="{ open: false }">
        <div class="relative">
            <input type="text"
                   :name="'items[' + index + '][medication_search]'"
                   x-model="searchQuery"
                   @input.debounce.300ms="searchMedications"
                   @click="open = true"
                   @click.away="open = false"
                   placeholder="Cari obat..."
                   class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            
            <!-- Search Results Dropdown -->
            <div x-show="open && medications.length > 0"
                 class="absolute z-10 mt-1 w-full bg-white shadow-lg rounded-md border border-gray-200 overflow-hidden">
                <ul class="max-h-60 overflow-auto py-1">
                    <template x-for="medication in medications" :key="medication.id">
                        <li>
                            <button type="button"
                                    @click="selectMedication(medication)"
                                    class="w-full px-4 py-2 text-left hover:bg-gray-50">
                                <div class="font-medium" x-text="medication.name"></div>
                                <div class="text-sm text-gray-500">
                                    <span x-text="medication.code"></span>
                                    <span class="mx-1">•</span>
                                    <span x-text="formatPrice(medication.price)"></span>
                                </div>
                            </button>
                        </li>
                    </template>
                </ul>
            </div>

            <!-- Selected Medication (hidden) -->
            <input type="hidden"
                   :name="'items[' + index + '][medication_id]'"
                   :value="selectedMedication?.id">
            <input type="hidden"
                   :name="'items[' + index + '][price]'"
                   :value="selectedMedication?.price">
        </div>
        <div x-show="errors['items.' + index + '.medication_id']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.medication_id']"></div>
    </div>

    <!-- Quantity -->
    <div class="col-span-2">
        <input type="number"
               :name="'items[' + index + '][quantity]'"
               x-model="quantity"
               min="1"
               placeholder="Jumlah"
               @input="calculateSubtotal"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <div x-show="errors['items.' + index + '.quantity']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.quantity']"></div>
    </div>

    <!-- Dosage Instructions -->
    <div class="col-span-4">
        <input type="text"
               :name="'items[' + index + '][dosage_instruction]'"
               x-model="dosageInstruction"
               placeholder="Aturan pakai (mis: 3x1 setelah makan)"
               class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <div x-show="errors['items.' + index + '.dosage_instruction']" 
             class="mt-1 text-sm text-red-600" 
             x-text="errors['items.' + index + '.dosage_instruction']"></div>
    </div>

    <!-- Subtotal (Read-only) -->
    <div class="col-span-1">
        <input type="text"
               :value="formatPrice(subtotal)"
               readonly
               class="block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
    </div>

    <!-- Remove Button -->
    <div class="col-span-1">
        <button type="button"
                @click="$dispatch('remove-item', index)"
                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            <svg class="h-4 w-4 text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>
</div>

@push('scripts')
<script>
function medicationItemRow() {
    return {
        searchQuery: '',
        medications: [],
        selectedMedication: null,
        quantity: 1,
        dosageInstruction: '',
        subtotal: 0,

        async searchMedications() {
            if (this.searchQuery.length < 2) return;

            try {
                const response = await fetch(`/api/v1/medications?search=${this.searchQuery}`);
                const data = await response.json();
                
                if (data.status === 'success') {
                    this.medications = data.data;
                }
            } catch (error) {
                console.error('Error searching medications:', error);
            }
        },

        selectMedication(medication) {
            this.selectedMedication = medication;
            this.searchQuery = medication.name;
            this.calculateSubtotal();
            this.$dispatch('medication-selected', {
                index: this.index,
                medication: medication
            });
        },

        calculateSubtotal() {
            if (this.selectedMedication && this.quantity > 0) {
                this.subtotal = this.selectedMedication.price * this.quantity;
                this.$dispatch('update-subtotal', {
                    index: this.index,
                    subtotal: this.subtotal
                });
            }
        },

        formatPrice(price) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR'
            }).format(price);
        }
    }
}
</script>
@endpush
