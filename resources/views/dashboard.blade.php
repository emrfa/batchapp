<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-4" x-data="{ range: 'today' }">
            <div>
                <h2 class="font-bold text-2xl text-gray-900 leading-tight tracking-tight">
                    {{ __('Dashboard') }}
                </h2>
            </div>
            
            <div class="flex items-center gap-4">
                <div class="inline-flex bg-gray-100/50 p-1 rounded-xl border border-gray-200/60">
                    <template x-for="(label, key) in {today: 'Today', weekly: 'Weekly', monthly: 'Monthly', yearly: 'Yearly'}">
                        <button 
                           @click.prevent="range = key; $dispatch('filter-changed', { range: key })"
                           class="px-4 py-1.5 text-sm font-medium rounded-lg transition-all duration-200 ease-out"
                           :class="range === key ? 'bg-white text-indigo-600 shadow-sm border border-gray-100' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-200/50'"
                           x-text="label">
                        </button>
                    </template>
                </div>

                <div class="hidden md:flex items-center gap-3 pl-4 border-l border-gray-200">
                    <div class="flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50/50 border border-blue-100 text-blue-700">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                        </span>
                        <span class="text-xs font-bold tracking-wide uppercase">Shift 1</span>
                    </div>
                    <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                        Live
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    <div class="py-8" x-data="dashboard" @filter-changed.window="range = $event.detail.range; fetchData()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <!-- Loading State -->
            <div class="space-y-8">
            
            <!-- KPI Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Batches -->
                <div class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-indigo-50 rounded-lg group-hover:bg-indigo-100 transition-colors">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        </div>
                        <template x-if="stats.yesterdaysCount > 0">
                            <div class="flex items-center gap-1 text-xs font-bold px-2 py-1 rounded-full"
                                 :class="((stats.todaysCount - stats.yesterdaysCount) / stats.yesterdaysCount) >= 0 ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     :class="((stats.todaysCount - stats.yesterdaysCount) / stats.yesterdaysCount) >= 0 ? '' : 'rotate-180'">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                </svg>
                                <span x-text="Math.abs(Math.round(((stats.todaysCount - stats.yesterdaysCount) / stats.yesterdaysCount) * 100)) + '%'"></span>
                            </div>
                        </template>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Batches</p>
                        <h3 class="text-3xl font-bold text-gray-900 mt-1 tracking-tight" x-text="stats.todaysCount"></h3>
                    </div>
                </div>
                
                <!-- Avg Cycle Time -->
                <div class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-bold px-2 py-1 rounded-full"
                              :class="stats.avgCycleTime > 150 ? 'text-red-600 bg-red-50' : 'text-green-600 bg-green-50'"
                              x-text="stats.avgCycleTime > 150 ? 'Slow' : 'Optimal'">
                        </span>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Avg Cycle Time</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <h3 class="text-3xl font-bold text-gray-900 tracking-tight" x-text="stats.avgCycleTime"></h3>
                            <span class="text-sm font-medium text-gray-400">sec</span>
                        </div>
                    </div>
                </div>

                <!-- Total Volume -->
                <div class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-orange-50 rounded-lg group-hover:bg-orange-100 transition-colors">
                            <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Volume</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <h3 class="text-3xl font-bold text-gray-900 tracking-tight" x-text="stats.totalVolumeTons"></h3>
                            <span class="text-sm font-medium text-gray-400">tons</span>
                        </div>
                    </div>
                </div>

                 <!-- Active Mixers -->
                 <div class="group bg-white p-6 rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] hover:shadow-[0_8px_30px_-4px_rgba(0,0,0,0.05)] transition-all duration-300">
                    <div class="flex justify-between items-start mb-4">
                        <div class="p-2 bg-purple-50 rounded-lg group-hover:bg-purple-100 transition-colors">
                            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-green-500"></span>
                            </span>
                            <span class="text-xs font-bold text-gray-600">Active</span>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Active Mixers</p>
                        <div class="flex items-baseline gap-1 mt-1">
                            <h3 class="text-3xl font-bold text-gray-900 tracking-tight" x-text="stats.mixers.filter(m => m.status === 'RUNNING').length"></h3>
                            <span class="text-lg font-medium text-gray-400" x-text="'/ ' + stats.mixers.length"></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Material Consumption Chart -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Material Consumption</h3>
                        </div>
                        <a href="{{ route('reports.production') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700 flex items-center gap-1">
                            View Report
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                    </div>
                    <div class="relative h-80 w-full">
                        <canvas id="materialChart"></canvas>
                    </div>
                </div>

                <!-- Inventory Levels -->
                <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-lg font-bold text-gray-900">Silo Levels</h3>
                            <p class="text-sm text-gray-500 mt-1">Current inventory status</p>
                        </div>
                        <div class="p-2 bg-gray-50 rounded-lg">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path></svg>
                        </div>
                    </div>
                    
                    <div class="space-y-8">
                        <template x-for="(silo, index) in stats.silos" :key="index">
                        <div class="group" x-data="{ percent: (silo.stock / 700000) * 100 }">
                            <div class="flex justify-between items-end mb-2">
                                <div>
                                    <span class="block text-sm font-bold text-gray-900" x-text="silo.name"></span>
                                    <!-- Material name is included in silo name -->
                                </div>
                                <div class="text-right">
                                    <span class="block text-sm font-bold text-gray-900">
                                        <span x-text="Number(silo.stock).toLocaleString()"></span> <span class="text-gray-500">kg</span>
                                    </span>
                                    <span class="text-xs text-gray-500" x-text="Math.round(percent) + '% Full'"></span>
                                </div>
                            </div>
                            
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-1000 ease-out relative" 
                                     :class="percent < 20 ? 'bg-red-500' : (percent < 50 ? 'bg-amber-400' : 'bg-emerald-500')"
                                     :style="`width: ${percent}%;`">
                                     <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]"></div>
                                </div>
                            </div>
                            
                            <template x-if="percent < 20">
                                <div class="flex items-center gap-1.5 mt-2 text-red-600 bg-red-50 px-3 py-1.5 rounded-md inline-flex">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <span class="text-xs font-bold">Low Level Warning</span>
                                </div>
                            </template>
                        </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Machine Performance -->
            <div class="bg-white rounded-2xl border border-gray-100 shadow-[0_2px_10px_-4px_rgba(0,0,0,0.05)] overflow-hidden">
                <div class="px-8 py-6 border-b border-gray-100 flex justify-between items-center bg-white">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Machine Performance</h3>
                        <p class="text-sm text-gray-500 mt-1">Real-time mixer status and production metrics</p>
                    </div>
                    <a href="{{ route('logs.index') }}" class="text-sm text-indigo-600 font-medium hover:text-indigo-700 flex items-center gap-1">
                            View Full Logs
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </a>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 p-8 bg-gray-50/30">
                    <template x-for="mixer in stats.mixers" :key="mixer.id">
                    <div class="relative bg-white rounded-xl border p-6 flex flex-col justify-between transition-all duration-300 hover:shadow-lg group"
                         :class="mixer.status == 'RUNNING' ? 'border-green-200 shadow-green-100' : 'border-gray-200'">
                        
                        <!-- Status Header -->
                        <div class="flex justify-between items-start mb-6">
                            <div class="flex items-center gap-4">
                                <div class="w-12 h-12 rounded-xl flex items-center justify-center shadow-sm border transition-colors"
                                     :class="mixer.status == 'RUNNING' ? 'bg-green-50 border-green-100 text-green-600' : 'bg-gray-50 border-gray-100 text-gray-400'">
                                    <svg class="w-6 h-6" 
                                         :class="{'animate-spin-slow': mixer.status == 'RUNNING'}"
                                         fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900" x-text="mixer.mixerCode"></h4>
                                    <p class="text-xs text-gray-500 font-medium" x-text="mixer.name || 'Production Line'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 px-3 py-1 rounded-full border text-xs font-bold uppercase tracking-wide"
                                 :class="mixer.status == 'RUNNING' ? 'bg-green-50 border-green-100 text-green-700' : 'bg-gray-50 border-gray-100 text-gray-500'">
                                <span class="relative flex h-2 w-2" x-show="mixer.status == 'RUNNING'">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                                </span>
                                <span x-text="mixer.status"></span>
                            </div>
                        </div>

                        <!-- Metrics -->
                        <div class="grid grid-cols-2 gap-4 mb-6">
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Batches Today</p>
                                <p class="text-xl font-bold text-gray-900" x-text="Number(mixer.today_count).toLocaleString()"></p>
                            </div>
                            <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                                <p class="text-[10px] uppercase tracking-wider font-bold text-gray-400 mb-1">Availability</p>
                                <p class="text-xl font-bold" 
                                   :class="(mixer.availability || 0) > 80 ? 'text-green-600' : 'text-orange-500'"
                                   x-text="(mixer.availability || 0) + '%'">
                                </p>
                            </div>
                        </div>

                        <!-- Progress & Recipe -->
                        <div class="mt-auto">
                             <div class="flex justify-between text-xs mb-2">
                                <span class="text-gray-500 font-medium">Daily Target Progress</span>
                                <span class="font-bold text-gray-900" x-text="Math.round(mixer.progress_percent) + '%'"></span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2 overflow-hidden mb-4">
                                <div class="h-full rounded-full transition-all duration-1000 relative" 
                                     :class="mixer.status == 'RUNNING' ? 'bg-indigo-500' : 'bg-gray-400'"
                                     :style="`width: ${mixer.progress_percent}%`">
                                     <div class="absolute inset-0 bg-white/20 animate-[shimmer_2s_infinite]" x-show="mixer.status == 'RUNNING'"></div>
                                </div>
                            </div>
                            
                            <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="text-xs font-bold text-gray-500">Current Recipe</span>
                                </div>
                                <span class="text-xs font-bold text-indigo-600 bg-indigo-50 px-2 py-1 rounded border border-indigo-100 truncate max-w-[120px]" x-text="mixer.current_recipe"></span>
                            </div>
                        </div>

                    </div>
                    </template>
                </div>
            </div>

        </div>
    </div>

    </div>
</x-app-layout>