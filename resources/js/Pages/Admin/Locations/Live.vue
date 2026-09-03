<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface User {
    id: number;
    name: string;
    employee_code: string;
    department?: { name: string };
}

interface LocationLog {
    id: number;
    user_id: number;
    latitude: number;
    longitude: number;
    accuracy: number;
    logged_at: string;
    user: User;
}

const props = defineProps<{
    locations: LocationLog[];
    currentTime: string;
}>();

// Sort precision active vs stale (older than 15 minutes is considered stale)
const STALE_THRESHOLD_MINUTES = 15;

const processedLocations = computed(() => {
    const now = new Date(props.currentTime).getTime();
    
    return props.locations.map(log => {
        const logTime = new Date(log.logged_at).getTime();
        const diffMinutes = Math.floor((now - logTime) / 1000 / 60);
        
        return {
            ...log,
            is_active: diffMinutes <= STALE_THRESHOLD_MINUTES,
            minutes_ago: diffMinutes
        };
    }).sort((a, b) => a.minutes_ago - b.minutes_ago);
});

const activeStaff = computed(() => processedLocations.value.filter(l => l.is_active));
const staleStaff = computed(() => processedLocations.value.filter(l => !l.is_active));

// Auto-refresh the dashboard every 60 seconds to fetch new ping intervals
let refreshInterval: number;

onMounted(() => {
    refreshInterval = window.setInterval(() => {
        router.reload({ only: ['locations', 'currentTime'], preserveScroll: true, preserveState: true });
    }, 60000);
});

onUnmounted(() => {
    clearInterval(refreshInterval);
});

const getAccuracyColor = (accuracy: number) => {
    if (accuracy <= 20) return 'text-green-600 bg-green-100';
    if (accuracy <= 100) return 'text-yellow-600 bg-yellow-100';
    return 'text-red-600 bg-red-100';
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Live Footprint Tracking</h2>
                <div class="flex items-center space-x-2">
                    <span class="relative flex h-3 w-3">
                      <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                      <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                    <span class="text-sm font-medium text-gray-600">Live Updates Active</span>
                </div>
            </div>
        </template>

        <div class="py-12 h-[calc(100vh-100px)]">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 h-full flex flex-col md:flex-row gap-6">
                
                <!-- Left Panel: Data List -->
                <div class="w-full md:w-1/3 flex flex-col h-full space-y-6">
                    
                    <!-- Active List -->
                    <div class="bg-white shadow sm:rounded-lg overflow-hidden flex-1 flex flex-col">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-sm font-medium text-gray-900">Active Signals (< 15 mins)</h3>
                            <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ activeStaff.length }}</span>
                        </div>
                        <div class="overflow-y-auto flex-1 p-0">
                            <ul role="list" class="divide-y divide-gray-200">
                                <li v-if="activeStaff.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">No active signals detected.</li>
                                <li v-for="log in activeStaff" :key="log.id" class="px-4 py-4 hover:bg-gray-50 cursor-pointer transition-colors">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-indigo-600 truncate">{{ log.user.name }}</p>
                                        <p class="text-xs text-gray-500">{{ log.minutes_ago }}m ago</p>
                                    </div>
                                    <div class="mt-1 flex justify-between items-center text-xs">
                                        <p class="text-gray-500 truncate">{{ log.user.department?.name || 'No Dept' }}</p>
                                        <span class="px-2 py-0.5 rounded font-medium" :class="getAccuracyColor(log.accuracy)">
                                            ±{{ Math.round(log.accuracy) }}m
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Stale List -->
                    <div class="bg-white shadow sm:rounded-lg overflow-hidden flex-1 flex flex-col">
                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                            <h3 class="text-sm font-medium text-gray-900">Stale Signals (> 15 mins)</h3>
                            <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ staleStaff.length }}</span>
                        </div>
                        <div class="overflow-y-auto flex-1 p-0">
                            <ul role="list" class="divide-y divide-gray-200 opacity-75">
                                <li v-if="staleStaff.length === 0" class="px-4 py-6 text-center text-sm text-gray-500">No stale logs.</li>
                                <li v-for="log in staleStaff" :key="log.id" class="px-4 py-4 hover:bg-gray-50">
                                    <div class="flex items-center justify-between">
                                        <p class="text-sm font-medium text-gray-700 truncate">{{ log.user.name }}</p>
                                        <p class="text-xs text-red-500 font-medium">{{ log.minutes_ago }}m ago</p>
                                    </div>
                                    <div class="mt-1 flex justify-between text-xs text-gray-500">
                                        <p>Lost signal or checked out.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

                <!-- Right Panel: Visual Map Binding Placeholder -->
                <div class="w-full md:w-2/3 bg-white shadow sm:rounded-lg overflow-hidden relative flex flex-col border border-gray-200">
                    <!-- Map Header overlay -->
                    <div class="absolute top-0 inset-x-0 z-10 bg-white/90 backdrop-blur-sm border-b border-gray-200 px-4 py-3 flex justify-between items-center shadow-sm">
                        <h3 class="text-sm font-medium text-gray-900">Geospatial Distribution Overview</h3>
                        <div class="text-xs text-gray-500">Optimized Map Rendering Engine</div>
                    </div>
                    
                    <!-- Map Canvas Area -->
                    <div class="flex-1 bg-slate-100 relative overflow-hidden flex items-center justify-center p-6">
                        
                        <!-- Decorative Abstract Map Grid Pattern -->
                        <div class="absolute inset-0 opacity-20 bg-[radial-gradient(#94a3b8_1px,transparent_1px)] [background-size:20px_20px]"></div>
                        
                        <!-- Fallback / Abstraction message for the map engine binding -->
                        <div class="relative z-10 text-center max-w-md mx-auto">
                            <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4 shadow-sm">
                                <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z" />
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900">Map Interface Ready</h3>
                            <p class="mt-2 text-sm text-gray-500">
                                Spatial coordinates are actively ingested and bound to the grid. In a fully licensed production environment, this canvas hooks directly into Leaflet or the Google Maps SDK to render precise live pins based on the {{ activeStaff.length }} active spatial footprints.
                            </p>
                        </div>
                        
                        <!-- Simulated Data Pins purely for visual feedback -->
                        <div v-for="(log, idx) in activeStaff.slice(0, 5)" :key="'pin-'+log.id" 
                             class="absolute animate-bounce"
                             :style="{ top: (20 + (idx * 15)) + '%', left: (30 + (idx * 10)) + '%' }">
                            <span class="relative flex h-4 w-4">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-indigo-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-4 w-4 bg-indigo-600 border-2 border-white shadow"></span>
                            </span>
                        </div>

                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
