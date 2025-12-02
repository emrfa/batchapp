<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Material Receiving') }}
        </h2>
    </x-slot>

    <div class="py-10" x-data="receiving">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Create Button (only visible when form is hidden) -->
            <div x-show="!showForm" class="mb-8">
                <button @click="openForm" 
                        class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-lg transition-all duration-200 shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>New Receiving</span>
                </button>
            </div>

            <!-- Form Section (hidden by default) -->
            <div x-show="showForm" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 transform scale-95"
                 x-transition:enter-end="opacity-100 transform scale-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 transform scale-100"
                 x-transition:leave-end="opacity-0 transform scale-95"
                 class="mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                    <!-- Form Header -->
                    <div class="px-8 py-6 bg-gradient-to-r from-slate-50 to-white border-b border-slate-100">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-11 h-11 rounded-xl bg-slate-800 flex items-center justify-center shadow-sm">
                                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-lg font-bold text-slate-900 tracking-tight">New Material Receipt</h3>
                                    <p class="text-sm text-slate-500 mt-0.5">Record incoming material delivery</p>
                                </div>
                            </div>
                            <button @click="cancelForm" 
                                    class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-lg transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                    <!-- Form Body -->
                    <form @submit.prevent="submitForm" class="p-8">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            
                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Received Time</label>
                                <input type="datetime-local" x-model="form.receivedTime"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Destination Silo</label>
                                <select x-model="form.storageId" class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all">
                                    <template x-for="storage in storageList" :key="storage.inventoryId">
                                        <option :value="storage.inventoryId" x-text="storage.name"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Supplier / Vendor</label>
                                <input type="text" placeholder="Enter supplier name" x-model="form.supplier"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all placeholder:text-slate-400">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Document Reference</label>
                                <input type="text" placeholder="e.g. DO-88219" x-model="form.documentRef"
                                       class="w-full px-4 py-2.5 border border-slate-200 rounded-lg text-sm text-slate-800 font-mono uppercase focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all placeholder:text-slate-400">
                            </div>

                            <div class="space-y-2">
                                <label class="block text-xs font-semibold text-slate-600 uppercase tracking-wider">Net Weight</label>
                                <div class="relative">
                                    <input type="number" placeholder="0" x-model="form.netWeight"
                                           class="w-full px-4 py-2.5 pr-14 border border-slate-200 rounded-lg font-mono text-lg font-bold text-slate-900 focus:ring-2 focus:ring-slate-800 focus:border-transparent transition-all placeholder:text-slate-300 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" min="0">
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <span class="text-slate-400 font-bold text-xs uppercase">KG</span>
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-end gap-3 md:col-span-2 lg:col-span-1">
                                <button type="submit" :disabled="isLoading" 
                                        class="flex-1 bg-slate-800 hover:bg-slate-900 disabled:bg-slate-400 text-white font-semibold py-2.5 rounded-lg transition-all duration-200 shadow-sm hover:shadow-md flex items-center justify-center gap-2">
                                    <span x-show="!isLoading">Confirm Receipt</span>
                                    <span x-show="isLoading" class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Processing
                                    </span>
                                </button>
                                <button type="button" @click="cancelForm" 
                                        class="px-6 py-2.5 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Section -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200/60 overflow-hidden">
                <!-- Table Header -->
                <div class="px-8 py-5 bg-gradient-to-r from-slate-50 to-white border-b border-slate-200">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900 tracking-tight">Recent Deliveries</h3>
                        </div>
                        <button @click="fetchTransactions()" 
                                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-slate-700 hover:text-slate-900 hover:bg-slate-100 rounded-lg transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span>Refresh</span>
                        </button>
                    </div>
                </div>
                
                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50/50">
                            <tr>
                                <th class="px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Received Time</th>
                                <th class="px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Silo</th>
                                <th class="px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                                <th class="px-8 py-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Document Ref</th>
                                <th class="px-8 py-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Quantity</th>
                                <th class="px-8 py-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            <template x-for="(transaction, index) in transactions" :key="index">
                            <tr class="hover:bg-slate-50/50 transition-colors duration-150">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="font-semibold text-sm text-slate-900" x-text="new Date(transaction.receivedTime).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})"></div>
                                    <div class="text-xs text-slate-500 mt-0.5" x-text="new Date(transaction.receivedTime).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'})"></div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-semibold text-slate-900" x-text="transaction.storageName"></div>
                                </td>
                                <td class="px-8 py-5">
                                    <div class="text-sm font-medium text-slate-600" x-text="transaction.supplier"></div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="inline-flex items-center font-mono text-xs bg-slate-100 text-slate-700 px-3 py-1.5 rounded-md border border-slate-200 font-semibold" x-text="transaction.documentRef"></span>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <span class="text-sm font-bold text-emerald-700" x-text="'+ ' + Number(transaction.quantity).toLocaleString()"></span>
                                        <span class="text-xs text-slate-500 font-medium">kg</span>
                                    </div>
                                </td>
                                <td class="px-8 py-5 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full"
                                          :class="{
                                              'bg-emerald-50 text-emerald-700 border border-emerald-200': transaction.status === 'Completed' || transaction.status === 'Complated',
                                              'bg-amber-50 text-amber-700 border border-amber-200': transaction.status === 'Pending',
                                              'bg-red-50 text-red-700 border border-red-200': transaction.status === 'Failed'
                                          }"
                                          x-text="transaction.status">
                                    </span>
                                </td>
                            </tr>
                            </template>
                            <template x-if="transactions.length === 0">
                                <tr>
                                    <td colspan="6" class="px-8 py-16 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                                            </svg>
                                            <div>
                                                <p class="text-sm font-semibold text-slate-600">No transactions found</p>
                                                <p class="text-xs text-slate-400 mt-1">Material receiving records will appear here</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
                
                <!-- Table Footer -->
                <div class="px-8 py-4 bg-slate-50/50 border-t border-slate-200">
                    <p class="text-xs text-slate-500 text-center">
                        <span x-show="transactions.length > 0" x-text="`Showing ${transactions.length} record${transactions.length !== 1 ? 's' : ''}`"></span>
                        <span x-show="transactions.length === 0">No records to display</span>
                    </p>
                </div>
            </div>

    <!-- Success Modal -->
    <div x-show="showSuccessModal" 
         class="fixed inset-0 z-50 overflow-y-auto" 
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="showSuccessModal" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity"></div>

        <!-- Modal Panel -->
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <div x-show="showSuccessModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                 x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                 class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-sm">
                
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-base font-semibold leading-6 text-slate-900">Receipt Confirmed</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500">The material receipt has been successfully recorded and added to the inventory.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                    <button type="button" @click="showSuccessModal = false" 
                            class="inline-flex w-full justify-center rounded-md bg-slate-900 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-slate-800 sm:ml-3 sm:w-auto">
                        Done
                    </button>
                </div>
            </div>
        </div>
    </div>
    </div>
</x-app-layout>
