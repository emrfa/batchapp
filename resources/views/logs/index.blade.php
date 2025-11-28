<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Batch Logs') }}
                </h2>
                <p class="text-sm text-gray-500 mt-1">Monitor production history and performance</p>
            </div>
            <a href="{{ route('dashboard') }}" class="group flex items-center gap-1 text-sm font-medium text-gray-500 hover:text-indigo-600 transition-colors">
                <svg class="w-4 h-4 transition-transform group-hover:-translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Back to Dashboard
            </a>
        </div>
    </x-slot>

    @push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
    @endpush

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @endpush

    <div class="py-8" x-data="logs">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- Filters & Actions -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 flex flex-col md:flex-row gap-5 items-end justify-between">
                <div class="flex flex-wrap gap-4 items-end w-full md:w-auto">
                    
                    <div class="w-full md:w-auto">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Date Range</label>
                        <div class="relative">
                            <input type="text" x-ref="dateRangePicker" class="w-full md:w-64 border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50 hover:bg-white transition-colors pl-10" placeholder="Select date range...">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                        </div>
                    </div>

                    <div class="w-full md:w-auto flex-grow">
                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Search</label>
                        <div class="relative group">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <svg class="h-4 w-4 text-gray-400 group-focus-within:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input type="text" x-model="filters.search" placeholder="Batch # or Recipe..." 
                                   class="pl-9 border-gray-200 rounded-lg text-sm focus:border-indigo-500 focus:ring-indigo-500 bg-gray-50/50 hover:bg-white w-full md:w-64 transition-colors"
                                   @keydown.enter="fetchLogs(1)">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button @click="fetchLogs(1)" class="bg-indigo-600 text-white px-5 py-2.5 rounded-lg text-sm font-semibold hover:bg-indigo-700 active:bg-indigo-800 transition-all shadow-sm hover:shadow-md">
                            Search
                        </button>
                        
                        <button @click="resetFilters()" 
                                class="px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                            Reset
                        </button>
                    </div>

                </div>
                
                <button onclick="window.print()" class="flex items-center gap-2 text-gray-600 hover:text-indigo-600 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Export PDF
                </button>
            </div>

            <!-- Table -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-50">
                        <thead>
                            <tr class="bg-gray-50/50">
                                <th class="px-8 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                                <th class="px-8 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch ID</th>
                                <th class="px-8 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Date & Time</th>
                                <th class="px-8 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Recipe</th>
                                <th class="px-8 py-5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Mixer</th>
                                <th class="px-8 py-5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Cycle Time</th>
                                <th class="px-8 py-5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Qty</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-50">
                            <template x-for="log in logs" :key="log.id">
                            <tr class="hover:bg-gray-50/50 transition-colors group">
                                <td class="px-8 py-5 whitespace-nowrap">
                                    <template x-if="log.unloadTime > 0">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2.5 w-2.5">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-0"></span>
                                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                                            </span>
                                            <span class="text-xs font-medium text-emerald-700">Completed</span> 
                                        </div>
                                    </template>
                                    <template x-if="!(log.unloadTime > 0)">
                                        <div class="flex items-center gap-2">
                                            <span class="relative flex h-2.5 w-2.5">
                                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                              <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-amber-500"></span>
                                            </span>
                                            <span class="text-xs font-medium text-amber-700">In Progress</span>
                                        </div>
                                    </template>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="text-sm font-bold text-gray-900" x-text="'#' + log.idBatch"></span>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900" x-text="new Date(log.batchTime).toLocaleDateString()"></span>
                                        <span class="text-xs text-gray-500" x-text="new Date(log.batchTime).toLocaleTimeString()"></span>
                                    </div>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900" x-text="log.recipe ? log.recipe.name : 'Standard Mix'"></span>
                                        <span class="text-xs font-mono text-gray-400 mt-0.5" x-text="log.recipeCode"></span>
                                    </div>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap">
                                    <span class="text-sm text-gray-600 font-medium" x-text="log.mixerCode"></span>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap text-right">
                                    <template x-if="log.unloadTime > 0">
                                        <span class="text-sm font-mono font-medium text-gray-700"><span x-text="Number(log.mixTime) + Number(log.unloadTime)"></span><span class="text-xs text-gray-400 ml-0.5">s</span></span>
                                    </template>
                                    <template x-if="!(log.unloadTime > 0)">
                                        <span class="text-xs text-gray-400 italic">--</span>
                                    </template>
                                </td>

                                <td class="px-8 py-5 whitespace-nowrap text-right">
                                    <span class="text-sm font-bold text-gray-900" x-text="Number(log.totalWeight || 0).toLocaleString()"></span>
                                    <span class="text-xs text-gray-500 ml-1">kg</span>
                                </td>
            
                            </tr>
                            </template>
                            
                            <template x-if="logs.length === 0 && !isLoading">
                            <tr>
                                <td colspan="7" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        </div>
                                        <h3 class="text-gray-900 font-medium text-base">No logs found</h3>
                                        <p class="text-gray-500 text-sm mt-1">Try adjusting your filters or search terms.</p>
                                    </div>
                                </td>
                            </tr>
                            </template>
                            
                            <template x-if="isLoading">
                                <tr>
                                    <td colspan="7" class="px-8 py-16 text-center">
                                        <div class="flex flex-col items-center justify-center">
                                            <svg class="animate-spin h-8 w-8 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            <span class="text-sm text-gray-500 font-medium">Loading batch data...</span>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div class="px-8 py-5 border-t border-gray-100 bg-gray-50/50 flex justify-between items-center" x-show="pagination.last_page > 1">
                    <button @click="fetchLogs(pagination.current_page - 1)" :disabled="!pagination.prev_page_url" 
                            class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                        Previous
                    </button>
                    <span class="text-sm text-gray-600 font-medium">Page <span x-text="pagination.current_page" class="text-gray-900"></span> of <span x-text="pagination.last_page" class="text-gray-900"></span></span>
                    <button @click="fetchLogs(pagination.current_page + 1)" :disabled="!pagination.next_page_url" 
                            class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm">
                        Next
                    </button>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>