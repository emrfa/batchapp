<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Material Receiving') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="receiving">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-1">
                    <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-200 sticky top-6">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-gray-800">Input Delivery</h3>
                                <p class="text-xs text-gray-500">From Delivery Order (DO)</p>
                            </div>
                        </div>
                        
                        <form @submit.prevent="submitForm" class="space-y-5">
                            
                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Received Time</label>
                                <input type="datetime-local" x-model="form.receivedTime"
                                       class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm text-gray-700">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Destination Silo</label>
                                <select x-model="form.silo" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                                    <option>Silo 1 (Semen) - 48% Full</option>
                                    <option>Silo 2 (Aux) - 13% Full</option>
                                    <option>Bin 1 (Sand) - 90% Full</option>
                                    <option>Bin 2 (Gravel) - 50% Full</option>
                                    <option>Water Tank - 90% Full</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Supplier / Vendor</label>
                                <div class="relative">
                                    <select x-model="form.supplier" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm pl-9">
                                        <option>Holcim Indonesia</option>
                                        <option>Semen Padang</option>
                                        <option>Local Quarry A</option>
                                        <option>StoneGroup Ltd</option>
                                        <option>Other...</option>
                                    </select>
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Document Ref #</label>
                                <input type="text" placeholder="e.g. DO-88219" x-model="form.documentRef"
                                       class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm uppercase font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Net Weight</label>
                                <div class="relative">
                                    <input type="number" placeholder="0" x-model="form.netWeight"
                                           class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 pl-4 pr-12 py-3 font-mono text-xl font-bold text-gray-800">
                                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                        <span class="text-gray-400 font-bold text-sm">KG</span>
                                    </div>
                                </div>
                            </div>

                            <button type="submit" :disabled="isLoading" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-lg transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-50">
                                <span x-show="!isLoading">Confirm Receipt</span>
                                <span x-show="isLoading">Processing...</span>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                            <h3 class="font-bold text-gray-800">Recent Deliveries</h3>
                            <button class="text-xs text-indigo-600 font-bold hover:underline" @click="fetchRecentLogs">Refresh</button>
                        </div>
                        
                        <table class="min-w-full divide-y divide-gray-100">
                            <thead>
                                <tr class="bg-white">
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Silo & Supplier</th>
                                    <th class="px-6 py-3 text-left text-xs font-bold text-gray-400 uppercase tracking-wider">Document Ref</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Qty Added</th>
                                    <th class="px-6 py-3 text-right text-xs font-bold text-gray-400 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 bg-white">
                                <template x-for="log in recentLogs" :key="log.id">
                                <tr class="hover:bg-gray-50 transition group">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                        <div class="font-bold text-gray-800" x-text="new Date(log.time).toLocaleDateString('en-GB', {day: '2-digit', month: 'short', year: 'numeric'})"></div>
                                        <div class="text-xs text-gray-500" x-text="new Date(log.time).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit'})"></div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm font-bold text-gray-800" x-text="log.silo"></div>
                                        <div class="text-xs text-indigo-500" x-text="log.supplier"></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="font-mono text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200" x-text="log.ticket"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-sm font-bold text-emerald-600 bg-emerald-50 px-2 py-1 rounded" x-text="'+ ' + Number(log.qty).toLocaleString() + ' kg'"></span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <span class="text-xs font-semibold text-blue-700 bg-blue-50 px-2.5 py-1 rounded-full border border-blue-100">
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                                </template>
                                <template x-if="recentLogs.length === 0">
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 text-sm">No recent deliveries found.</td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                        
                        <div class="px-6 py-3 border-t border-gray-100 bg-gray-50 text-xs text-gray-400 flex justify-center">
                            Showing recent entries
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>