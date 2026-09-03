<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface CalendarDay {
    date: string;
    day: number;
    isCurrentMonth: boolean;
    status: string | null;
    check_in: string | null;
    check_out: string | null;
}

const props = defineProps<{
    calendarData: CalendarDay[];
    filters: {
        month: string;
        year: string;
    };
}>();

const selectedMonth = ref(props.filters.month || (new Date().getMonth() + 1).toString().padStart(2, '0'));
const selectedYear = ref(props.filters.year || new Date().getFullYear().toString());

watch([selectedMonth, selectedYear], () => {
    router.get(
        route('attendance.calendar'),
        { month: selectedMonth.value, year: selectedYear.value },
        { preserveState: true, preserveScroll: true }
    );
});

const getStatusColor = (status: string | null) => {
    if (!status) return 'bg-white';
    switch(status) {
        case 'present': return 'bg-green-100 border-green-300';
        case 'absent': return 'bg-red-100 border-red-300';
        case 'late': return 'bg-yellow-100 border-yellow-300';
        case 'half_day': return 'bg-orange-100 border-orange-300';
        case 'leave': return 'bg-blue-100 border-blue-300';
        case 'holiday': return 'bg-purple-100 border-purple-300';
        case 'weekend': return 'bg-gray-100 border-gray-300';
        default: return 'bg-white border-gray-200';
    }
};

const formatStatus = (status: string | null) => {
    if (!status) return '';
    return status.split('_').map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
};

const formatTime = (isoString: string | null) => {
    if (!isoString) return '';
    return new Date(isoString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};

</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance Calendar</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white p-4 shadow sm:rounded-lg flex justify-end space-x-4 mb-6">
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

                <!-- Calendar Grid -->
                <div class="bg-white shadow sm:rounded-lg p-4 sm:p-6">
                    <div class="grid grid-cols-7 gap-1 sm:gap-2 mb-2">
                        <div v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']" :key="day" class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            {{ day }}
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-7 gap-1 sm:gap-2">
                        <div v-for="(day, idx) in props.calendarData" :key="idx" 
                             class="min-h-[80px] sm:min-h-[120px] p-1 sm:p-2 border rounded-lg transition-colors flex flex-col"
                             :class="[
                                getStatusColor(day.status),
                                day.isCurrentMonth ? 'opacity-100' : 'opacity-40 bg-gray-50'
                             ]">
                            
                            <div class="text-right text-xs sm:text-sm font-medium text-gray-700" :class="{ 'text-indigo-600 font-bold': day.date === new Date().toISOString().split('T')[0] }">
                                {{ day.day }}
                            </div>
                            
                            <div class="mt-auto hidden sm:block">
                                <div v-if="day.status" class="text-xs font-semibold mt-1" :class="{'text-red-700': day.status === 'absent'}">
                                    {{ formatStatus(day.status) }}
                                </div>
                                <div v-if="day.check_in" class="text-[10px] text-gray-500 mt-1">In: {{ formatTime(day.check_in) }}</div>
                                <div v-if="day.check_out" class="text-[10px] text-gray-500">Out: {{ formatTime(day.check_out) }}</div>
                            </div>
                            
                            <!-- Mobile simplified view -->
                            <div class="mt-auto sm:hidden flex justify-center">
                                <div v-if="day.status" class="h-2 w-2 rounded-full" 
                                    :class="{
                                        'bg-green-500': day.status === 'present',
                                        'bg-red-500': day.status === 'absent',
                                        'bg-yellow-500': day.status === 'late' || day.status === 'half_day',
                                        'bg-blue-500': day.status === 'leave',
                                        'bg-gray-400': day.status === 'weekend' || day.status === 'holiday'
                                    }">
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 flex flex-wrap gap-4 text-sm text-gray-600 justify-center">
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-green-500 mr-2"></div> Present</div>
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-red-500 mr-2"></div> Absent</div>
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></div> Late / Half Day</div>
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-blue-500 mr-2"></div> Leave</div>
                    <div class="flex items-center"><div class="w-3 h-3 rounded-full bg-gray-400 mr-2"></div> Weekend / Holiday</div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
