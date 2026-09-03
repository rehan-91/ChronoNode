<script setup lang="ts">
import { ref, watch } from 'vue';
import { usePage, router, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Attendance, PaginatedData } from '@/types';

const props = defineProps<{
    attendances: PaginatedData<Attendance>;
    summary: {
        present: number;
        absent: number;
        late: number;
        half_day: number;
        leave: number;
        total_working_minutes: number;
        total_overtime_minutes: number;
    };
    filters: {
        month: string;
        year: string;
    };
}>();

const selectedMonth = ref(props.filters.month || (new Date().getMonth() + 1).toString().padStart(2, '0'));
const selectedYear = ref(props.filters.year || new Date().getFullYear().toString());

// Filter watcher
watch([selectedMonth, selectedYear], () => {
    router.get(
        route('attendance.history'),
        { month: selectedMonth.value, year: selectedYear.value },
        { preserveState: true, preserveScroll: true }
    );
});

const formatDuration = (minutes: number) => {
    const h = Math.floor(minutes / 60);
    const m = minutes % 60;
    return `${h}h ${m}m`;
};

const getStatusColor = (status: string) => {
    switch(status) {
        case 'present': return 'bg-green-100 text-green-800';
        case 'absent': return 'bg-red-100 text-red-800';
        case 'late': return 'bg-yellow-100 text-yellow-800';
        case 'half_day': return 'bg-orange-100 text-orange-800';
        case 'leave': return 'bg-blue-100 text-blue-800';
        case 'holiday': return 'bg-purple-100 text-purple-800';
        case 'weekend': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatStatus = (status: string) => {
    return status.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance History</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                
                <!-- Filters -->
                <div class="bg-white p-4 shadow sm:rounded-lg flex justify-end space-x-4">
                    <select v-model="selectedMonth" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="01">January</option>
                        <option value="02">February</option>
                        <option value="03">March</option>
                        <option value="04">April</option>
                        <option value="05">May</option>
                        <option value="06">June</option>
                        <option value="07">July</option>
                        <option value="08">August</option>
                        <option value="09">September</option>
                        <option value="10">October</option>
                        <option value="11">November</option>
                        <option value="12">December</option>
                    </select>
                    
                    <select v-model="selectedYear" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option v-for="y in [2024, 2025, 2026, 2027]" :key="y" :value="y.toString()">{{ y }}</option>
                    </select>
                </div>

                <!-- Monthly Summary Grid -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-white p-4 shadow sm:rounded-lg text-center">
                        <p class="text-sm font-medium text-gray-500">Present</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.summary.present }}</p>
                    </div>
                    <div class="bg-white p-4 shadow sm:rounded-lg text-center">
                        <p class="text-sm font-medium text-gray-500">Absent / Leave</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.summary.absent }} / {{ props.summary.leave }}</p>
                    </div>
                    <div class="bg-white p-4 shadow sm:rounded-lg text-center">
                        <p class="text-sm font-medium text-gray-500">Late / Half Day</p>
                        <p class="mt-1 text-2xl font-semibold text-gray-900">{{ props.summary.late }} / {{ props.summary.half_day }}</p>
                    </div>
                    <div class="bg-white p-4 shadow sm:rounded-lg text-center">
                        <p class="text-sm font-medium text-gray-500">Total Hours / OT</p>
                        <p class="mt-1 text-lg font-semibold text-gray-900">
                            {{ formatDuration(props.summary.total_working_minutes) }} <br>
                            <span class="text-sm text-green-600 font-medium">OT: {{ formatDuration(props.summary.total_overtime_minutes) }}</span>
                        </p>
                    </div>
                </div>

                <!-- Daily Records List (Mobile Friendly) -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-if="props.attendances.data.length === 0" class="px-4 py-8 text-center text-gray-500">
                            No attendance records found for this period.
                        </li>
                        <li v-for="record in props.attendances.data" :key="record.id" class="px-4 py-4 sm:px-6 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 truncate">{{ record.date }}</p>
                                    <div class="mt-2 flex items-center text-sm text-gray-500 space-x-4">
                                        <p>In: {{ record.check_in ? new Date(record.check_in).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '--' }}</p>
                                        <p>Out: {{ record.check_out ? new Date(record.check_out).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) : '--' }}</p>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-400">
                                        Work: {{ formatDuration(record.working_minutes) }} 
                                        <span v-if="record.late_minutes > 0" class="text-red-500 ml-2">Late: {{ record.late_minutes }}m</span>
                                        <span v-if="record.overtime_minutes > 0" class="text-green-500 ml-2">OT: {{ formatDuration(record.overtime_minutes) }}</span>
                                    </div>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <p class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full" :class="getStatusColor(record.status)">
                                        {{ formatStatus(record.status) }}
                                    </p>
                                </div>
                            </div>
                        </li>
                    </ul>

                    <!-- Pagination -->
                    <div class="bg-white px-4 py-3 border-t border-gray-200 flex items-center justify-between sm:px-6" v-if="props.attendances.total > props.attendances.per_page">
                        <div class="flex-1 flex justify-between sm:hidden">
                            <Link v-if="props.attendances.prev_page_url" :href="props.attendances.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Previous </Link>
                            <Link v-if="props.attendances.next_page_url" :href="props.attendances.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50"> Next </Link>
                        </div>
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Showing <span class="font-medium">{{ props.attendances.from }}</span> to <span class="font-medium">{{ props.attendances.to }}</span> of <span class="font-medium">{{ props.attendances.total }}</span> results
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <template v-for="(link, i) in props.attendances.links" :key="i">
                                        <Link v-if="link.url" :href="link.url" v-html="link.label" class="relative inline-flex items-center px-4 py-2 border text-sm font-medium" :class="link.active ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'"></Link>
                                        <span v-else v-html="link.label" class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-300 cursor-not-allowed"></span>
                                    </template>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
