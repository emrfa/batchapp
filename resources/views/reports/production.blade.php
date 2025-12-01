<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Pemakaian Bahan Baku') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6" x-data="{
                reportData: null,
                isLoading: true,
                filters: {
                    start_date: new Date().toISOString().slice(0, 10),
                    end_date: new Date().toISOString().slice(0, 10),
                    mixer_code: '',
                    PageNumber: 1,
                    PageSize: 20
                },
                pagination: {
                    PageNumber: 1,
                    PageSize: 20,
                    totalBatches: 0,
                    totalPages: 0
                },
                appliedMixer: '',
                autoRefreshPaused: false,
                refreshInterval: null,

                init() {
                    this.fetchReport();
                    this.startAutoRefresh();
                },

                startAutoRefresh() {
                    this.refreshInterval = setInterval(() => {
                        if (!this.autoRefreshPaused && !this.isLoading) {
                            this.fetchReport(true); // Pass true to indicate background refresh
                        }
                    }, 30000); // 30 seconds
                },

                async fetchReport(isBackground = false) {
                    if (!isBackground) this.isLoading = true;
                    this.appliedMixer = this.filters.mixer_code;
                    try {
                        const params = new URLSearchParams({
                            startDate: this.filters.start_date,
                            endDate: this.filters.end_date,
                            mixer: this.filters.mixer_code,
                            PageNumber: this.filters.PageNumber,
                            PageSize: this.filters.PageSize
                        });
                        const response = await fetch(`/api/reports/production?${params.toString()}`);
                        const json = await response.json();
                        this.reportData = json;
                        
                        // Update pagination info
                        this.pagination.PageNumber = json.PageNumber || 1;
                        this.pagination.PageSize = json.PageSize || 20;
                        this.pagination.totalBatches = json.totalBatches || 0;
                        this.pagination.totalPages = Math.ceil(this.pagination.totalBatches / this.pagination.PageSize);
                    } catch (error) {
                        console.error('Error fetching report:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                getMaterialQty(batch, materialCode, storageCode = null) {
                    if (!batch.details) return 0;
                    
                    const item = batch.details.find(d => 
                        d.materialCode === materialCode && 
                        (!storageCode || d.storageCode === storageCode)
                    );
                    return item ? item.quantity : 0;
                }
            }">

            {{-- FILTER --}}
            <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200">
                <form @submit.prevent="filters.PageNumber = 1; fetchReport()" class="flex flex-wrap gap-4 items-end">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">From</label>
                        <input type="date" x-model="filters.start_date" class="border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">To</label>
                        <input type="date" x-model="filters.end_date" class="border-gray-300 rounded-md text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Mixer</label>
                        <select x-model="filters.mixer_code" class="border-gray-300 rounded-md text-sm w-40">
                            <option value="">All Mixers</option>
                            <template x-if="reportData && reportData.mixers">
                                <template x-for="mixer in reportData.mixers" :key="mixer.mixerCode">
                                    <option :value="mixer.mixerCode" x-text="mixer.mixerCode"></option>
                                </template>
                            </template>
                        </select>
                    </div>

                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm">Filter Data</button>
                    <button type="button" 
                            @click="async () => {
                                const url = `/api/reports/production/export?mixer=${filters.mixer_code}&startDate=${filters.start_date}&endDate=${filters.end_date}`;
                                try {
                                    const response = await fetch(url);
                                    if (!response.ok) {
                                        const error = await response.json();
                                        alert(error.message || 'Export failed. Please try a smaller date range.');
                                        return;
                                    }
                                    const blob = await response.blob();
                                    const downloadUrl = window.URL.createObjectURL(blob);
                                    const a = document.createElement('a');
                                    a.href = downloadUrl;
                                    a.download = `Production_Report_${filters.start_date}_to_${filters.end_date}.xlsx`;
                                    document.body.appendChild(a);
                                    a.click();
                                    window.URL.revokeObjectURL(downloadUrl);
                                    a.remove();
                                } catch (err) {
                                    alert('Export failed. Please try again.');
                                }
                            }"
                            class="bg-green-600 text-white px-4 py-2 rounded-md text-sm hover:bg-green-700 ml-auto">
                        Export to Excel
                    </button>
                </form>
            </div>

            {{-- SUMMARY --}}
            {{-- SUMMARY --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4" x-show="reportData">
                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <p class="text-xs text-gray-400 uppercase">Total Batches</p>
                    <p class="text-2xl font-bold" x-text="reportData ? reportData.totalBatches : 0"></p>
                </div>
                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <p class="text-xs text-gray-400 uppercase">Total Weight</p>
                    <p class="text-2xl font-bold text-indigo-600"><span x-text="reportData ? Number(reportData.totalWeight).toLocaleString() : 0"></span> <span class="text-sm text-gray-400">kg</span></p>
                </div>

                <template x-if="reportData">
                    <template x-for="item in reportData.materialSummary" :key="item.label">
                        <div class="bg-white p-4 rounded-lg border shadow-sm" x-show="item.value > 0">
                            <p class="text-xs text-gray-400 uppercase" x-text="item.label + ' Terpakai'"></p>
                            <p class="text-xl font-bold">
                                <span x-text="Number(item.value).toLocaleString()"></span> 
                                <span class="text-sm text-gray-400" x-text="item.unit"></span>
                            </p>
                        </div>
                    </template>
                </template>
            </div>

            {{-- TABLE --}}
            {{-- TABLE --}}
           <div class="bg-white rounded-lg border border-gray-200 shadow-sm overflow-hidden" 
                x-show="reportData"
                x-on:mouseenter="autoRefreshPaused = true" 
                x-on:mouseleave="autoRefreshPaused = false">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 text-sm border-collapse">
            <thead>

                {{-- ========================================== --}}
                {{--              HEADERS: MIXER FM5            --}}
                {{-- ========================================== --}}
                <template x-if="appliedMixer === 'Mixer FM5'">
                    <tr class="bg-gray-100">
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r w-16 bg-gray-100">No</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Date</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Time</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Batch ID</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Mixer</th>

                        <th colspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100">Semen (Kg)</th>
                        {{-- Pasir beton (Galunggung) - Single Entity --}}
                        <th rowspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100 align-middle">Pasir beton (Galunggung)</th>
                        <th colspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100">Pigmen</th>
                        <th rowspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100 align-middle">Air</th>
                    </tr>
                </template>
                <template x-if="appliedMixer === 'Mixer FM5'">
                    <tr class="bg-gray-100">
                        {{-- Semen Sub-headers --}}
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Abu</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Putih</th>
                        
                        {{-- Pigmen Sub-headers --}}
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Warna</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Qty (Kg)</th>
                    </tr>
                </template>


                {{-- ========================================== --}}
                {{--           HEADERS: OTHER MIXERS            --}}
                {{-- ========================================== --}}
                <template x-if="appliedMixer !== 'Mixer FM5'">
                    <tr class="bg-gray-100">
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r w-16 bg-gray-100">No</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Date</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Time</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Batch ID</th>
                        <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Mixer</th>

                        <th colspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100">Semen (Kg)</th>
                        <th colspan="1" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100">Semen HC (Kg)</th>
                        <th colspan="3" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100">Pasir (Pulsa)</th>
                        <th rowspan="2" class="px-3 py-2 text-center font-bold text-gray-700 border-r bg-gray-100 align-middle">Air</th>

                        {{-- Machine Column for CM4 --}}
                        <template x-if="appliedMixer === 'Mixer CM4'">
                            <th rowspan="2" class="px-3 py-2 text-left font-semibold text-gray-600 border-r bg-gray-100">Machine</th>
                        </template>
                    </tr>
                </template>
                <template x-if="appliedMixer !== 'Mixer FM5'">
                    <tr class="bg-gray-100">
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Silo 1</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Silo 3</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Silo 2</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Ciloseh / Kuarsa</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Giling 5 / Giling 6</th>
                        <th class="px-2 py-1 text-center text-xs text-gray-500 border-r font-mono bg-gray-100">Screening</th>
                    </tr>
                </template>

            </thead>

            {{-- ========================================== --}}
            {{--             BODY: MIXER FM5                --}}
            {{-- ========================================== --}}
            <template x-if="reportData && appliedMixer === 'Mixer FM5'">
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template x-for="(batch, index) in reportData.batches" :key="batch.idBatch">
                        <tr class="hover:bg-gray-50">
                            {{-- Common Columns --}}
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r text-center" x-text="(pagination.PageNumber - 1) * pagination.PageSize + index + 1"></td>
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r" x-text="new Date(batch.batchTime).toLocaleDateString('en-CA')"></td>
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r" x-text="new Date(batch.batchTime).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'})"></td>
                            <td class="px-3 py-2 font-mono text-gray-600 border-r" x-text="'#' + batch.idBatch"></td>
                            <td class="px-3 py-2 text-gray-600 border-r" x-text="batch.mixerCode"></td>

                            {{-- FM5 Specific Columns --}}
                            {{-- Semen (Kg): Abu --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Semen FM5 Abu', 'Abu') > 0 ? Number(getMaterialQty(batch, 'Semen FM5 Abu', 'Abu')).toLocaleString() : '-'">
                            </td>
                            {{-- Semen (Kg): Putih --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Semen FM5 Putih', 'Putih') > 0 ? Number(getMaterialQty(batch, 'Semen FM5 Putih', 'Putih')).toLocaleString() : '-'">
                            </td>

                            {{-- Pasir beton (Galunggung) --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pasir FM5', 'Galunggung') > 0 ? Number(getMaterialQty(batch, 'Pasir FM5', 'Galunggung')).toLocaleString() : '-'">
                            </td>

                            {{-- Pigmen: Warna --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pigmen Warna') || '-'">
                            </td>
                            {{-- Pigmen: Qty --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pigmen Qty') > 0 ? Number(getMaterialQty(batch, 'Pigmen Qty')).toLocaleString() : '-'">
                            </td>

                            {{-- Air (Mapped) --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Air') > 0 ? Number(getMaterialQty(batch, 'Air')).toLocaleString() : '-'">
                            </td>
                        </tr>
                    </template>
                </tbody>
            </template>

            {{-- ========================================== --}}
            {{--           BODY: OTHER MIXERS               --}}
            {{-- ========================================== --}}
            <template x-if="reportData && appliedMixer !== 'Mixer FM5'">
                <tbody class="divide-y divide-gray-200 bg-white">
                    <template x-for="(batch, index) in reportData.batches" :key="batch.idBatch">
                        <tr class="hover:bg-gray-50">
                            {{-- Common Columns --}}
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r text-center" x-text="(pagination.PageNumber - 1) * pagination.PageSize + index + 1"></td>
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r" x-text="new Date(batch.batchTime).toLocaleDateString('en-CA')"></td>
                            <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r" x-text="new Date(batch.batchTime).toLocaleTimeString('en-GB', {hour: '2-digit', minute: '2-digit', second: '2-digit'})"></td>
                            <td class="px-3 py-2 font-mono text-gray-600 border-r" x-text="'#' + batch.idBatch"></td>
                            <td class="px-3 py-2 text-gray-600 border-r" x-text="batch.mixerCode"></td>

                            {{-- Original Columns --}}
                            {{-- Semen (Kg): Silo 1 --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Semen (Kg)', 'Silo 1') > 0 ? Number(getMaterialQty(batch, 'Semen (Kg)', 'Silo 1')).toLocaleString() : '-'">
                            </td>
                            {{-- Semen (Kg): Silo 3 --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Semen (Kg)', 'Silo 3') > 0 ? Number(getMaterialQty(batch, 'Semen (Kg)', 'Silo 3')).toLocaleString() : '-'">
                            </td>

                            {{-- Semen HC (Kg): Silo 2 --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Semen HC (Kg)', 'Silo 2') > 0 ? Number(getMaterialQty(batch, 'Semen HC (Kg)', 'Silo 2')).toLocaleString() : '-'">
                            </td>

                            {{-- Pasir (Pulsa): Ciloseh / Kuarsa --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pasir (Pulsa)', 'Ciloseh / Kuarsa') > 0 ? Number(getMaterialQty(batch, 'Pasir (Pulsa)', 'Ciloseh / Kuarsa')).toLocaleString() : '-'">
                            </td>
                            {{-- Pasir (Pulsa): Giling 5 / Giling 6 --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pasir (Pulsa)', 'Giling 5 / Giling 6') > 0 ? Number(getMaterialQty(batch, 'Pasir (Pulsa)', 'Giling 5 / Giling 6')).toLocaleString() : '-'">
                            </td>
                            {{-- Pasir (Pulsa): Screening --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Pasir (Pulsa)', 'Screening') > 0 ? Number(getMaterialQty(batch, 'Pasir (Pulsa)', 'Screening')).toLocaleString() : '-'">
                            </td>

                            {{-- Air --}}
                            <td class="px-3 py-2 text-right border-r text-gray-700"
                                x-text="getMaterialQty(batch, 'Air') > 0 ? Number(getMaterialQty(batch, 'Air')).toLocaleString() : '-'">
                            </td>

                            {{-- Machine Column for CM4 --}}
                            <template x-if="appliedMixer === 'Mixer CM4'">
                                <td class="px-3 py-2 text-gray-600 whitespace-nowrap border-r">-</td>
                            </template>
                        </tr>
                    </template>
                </tbody>
            </template>

            {{-- No Data Row --}}
            <template x-if="!reportData || reportData.batches.length === 0">
                <tbody>
                    <tr>
                        <td colspan="100%" class="px-4 py-8 text-center text-gray-500">
                            No production records found.
                        </td>
                    </tr>
                </tbody>
            </template>

        </table>
    </div>
    
    {{-- PAGINATION CONTROLS --}}
    <div class="bg-gray-50 px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6" x-show="reportData && pagination.totalBatches > 0">
        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
            <div>
                <p class="text-sm text-gray-700">
                    Showing
                    <span class="font-medium" x-text="(pagination.PageNumber - 1) * pagination.PageSize + 1"></span>
                    to
                    <span class="font-medium" x-text="Math.min(pagination.PageNumber * pagination.PageSize, pagination.totalBatches)"></span>
                    of
                    <span class="font-medium" x-text="pagination.totalBatches"></span>
                    results
                </p>
            </div>
            <div>
                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                    <button 
                        @click="if(filters.PageNumber > 1) { filters.PageNumber--; fetchReport(); }" 
                        :disabled="filters.PageNumber === 1"
                        :class="{'opacity-50 cursor-not-allowed': filters.PageNumber === 1}"
                        class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Previous</span>
                        <!-- Heroicon name: solid/chevron-left -->
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    
                    <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
                        Page <span x-text="pagination.PageNumber" class="mx-1"></span> of <span x-text="pagination.totalPages" class="mx-1"></span>
                    </span>

                    <button 
                        @click="if(filters.PageNumber < pagination.totalPages) { filters.PageNumber++; fetchReport(); }" 
                        :disabled="filters.PageNumber >= pagination.totalPages"
                        :class="{'opacity-50 cursor-not-allowed': filters.PageNumber >= pagination.totalPages}"
                        class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        <span class="sr-only">Next</span>
                        <!-- Heroicon name: solid/chevron-right -->
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </nav>
            </div>
        </div>
    </div>
</div>




        </div>
    </div>
</x-app-layout>
