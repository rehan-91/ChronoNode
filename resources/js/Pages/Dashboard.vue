<script setup lang="ts">
import { ref, onMounted, onUnmounted, computed } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { Attendance, Office, PageProps } from '@/types';

const props = defineProps<{
    todayAttendance: Attendance | null;
    office: Office | null;
}>();

const page = usePage<PageProps>();

// GPS State
const latitude = ref<number | null>(null);
const longitude = ref<number | null>(null);
const accuracy = ref<number | null>(null);
const locationError = ref<string | null>(null);
const isLocating = ref<boolean>(true);
let watchId: number | null = null;

const form = useForm({
    latitude: 0,
    longitude: 0,
    accuracy: 0,
});

// Haversine implementation for frontend visual feedback
function calculateDistanceMeters(lat1: number, lon1: number, lat2: number, lon2: number): number {
    const R = 6371000;
    const p1 = lat1 * Math.PI / 180;
    const p2 = lat2 * Math.PI / 180;
    const dp = (lat2 - lat1) * Math.PI / 180;
    const dl = (lon2 - lon1) * Math.PI / 180;
    const a = Math.sin(dp / 2) * Math.sin(dp / 2) +
              Math.cos(p1) * Math.cos(p2) *
              Math.sin(dl / 2) * Math.sin(dl / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    return Math.round(R * c);
}

const distanceFromOffice = computed(() => {
    if (!props.office || latitude.value === null || longitude.value === null) return null;
    return calculateDistanceMeters(
        props.office.latitude,
        props.office.longitude,
        latitude.value,
        longitude.value
    );
});

const isWithinRadius = computed(() => {
    if (distanceFromOffice.value === null || !props.office) return false;
    return distanceFromOffice.value <= props.office.radius_meters;
});

const hasPoorAccuracy = computed(() => {
    return accuracy.value !== null && accuracy.value > 150;
});

onMounted(() => {
    if (!props.office) {
        locationError.value = "You are not assigned to an active office.";
        isLocating.value = false;
        return;
    }

    if (!("geolocation" in navigator)) {
        locationError.value = "Geolocation is not supported by your browser.";
        isLocating.value = false;
        return;
    }

    watchId = navigator.geolocation.watchPosition(
        (position) => {
            latitude.value = position.coords.latitude;
            longitude.value = position.coords.longitude;
            accuracy.value = Math.round(position.coords.accuracy);
            
            form.latitude = position.coords.latitude;
            form.longitude = position.coords.longitude;
            form.accuracy = Math.round(position.coords.accuracy);
            
            isLocating.value = false;
            locationError.value = null;
        },
        (error) => {
            isLocating.value = false;
            switch (error.code) {
                case error.PERMISSION_DENIED:
                    locationError.value = "Permission denied. Please enable location access.";
                    break;
                case error.POSITION_UNAVAILABLE:
                    locationError.value = "Location information is unavailable.";
                    break;
                case error.TIMEOUT:
                    locationError.value = "The request to get user location timed out.";
                    break;
                default:
                    locationError.value = "An unknown error occurred getting location.";
                    break;
            }
        },
        { enableHighAccuracy: true, maximumAge: 0, timeout: 10000 }
    );
});

onUnmounted(() => {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
});

const submitCheckIn = () => {
    if (form.processing) return;
    form.post(route('attendance.check-in'), {
        preserveScroll: true,
    });
};

const submitCheckOut = () => {
    if (form.processing) return;
    form.post(route('attendance.check-out'), {
        preserveScroll: true,
    });
};

// Formatted Time Helper
const formatTime = (timeString: string | null) => {
    if (!timeString) return '--:--';
    return new Date(timeString).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Employee Dashboard</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    
                    <!-- Top Status Bar -->
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">Today's Attendance</h3>
                                <p class="text-sm text-gray-500">{{ new Date().toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}</p>
                            </div>
                            <div class="text-center md:text-right">
                                <span v-if="todayAttendance?.check_in && !todayAttendance?.check_out" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                    Checked In
                                </span>
                                <span v-else-if="todayAttendance?.check_out" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                    Checked Out
                                </span>
                                <span v-else class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                    Not Checked In
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Time Grid -->
                    <div class="grid grid-cols-2 divide-x divide-gray-200 border-b border-gray-200">
                        <div class="p-6 text-center">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-widest">Check In</p>
                            <p class="mt-2 text-3xl font-light text-gray-900">{{ formatTime(todayAttendance?.check_in ?? null) }}</p>
                        </div>
                        <div class="p-6 text-center">
                            <p class="text-sm font-medium text-gray-500 uppercase tracking-widest">Check Out</p>
                            <p class="mt-2 text-3xl font-light text-gray-900">{{ formatTime(todayAttendance?.check_out ?? null) }}</p>
                        </div>
                    </div>

                    <!-- GPS Module & Actions -->
                    <div class="p-6 bg-gray-50">
                        
                        <!-- Missing Office Error -->
                        <div v-if="!props.office" class="text-center p-4">
                            <p class="text-red-600 font-medium">No active office assigned. Please contact HR.</p>
                        </div>

                        <div v-else>
                            <!-- GPS Visual Feedback -->
                            <div class="mb-6 rounded-md p-4" :class="{
                                'bg-blue-50 border border-blue-200': isLocating,
                                'bg-red-50 border border-red-200': locationError || (hasPoorAccuracy && !isLocating) || (!isWithinRadius && !isLocating && latitude),
                                'bg-green-50 border border-green-200': !isLocating && !locationError && !hasPoorAccuracy && isWithinRadius
                            }">
                                <div class="flex items-center justify-center space-x-3">
                                    <div v-if="isLocating" class="animate-pulse flex space-x-2">
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                        <div class="h-2 w-2 bg-blue-400 rounded-full"></div>
                                    </div>
                                    <p class="text-sm font-medium" :class="{
                                        'text-blue-700': isLocating,
                                        'text-red-700': locationError || hasPoorAccuracy || (!isWithinRadius && latitude),
                                        'text-green-700': !isLocating && !locationError && !hasPoorAccuracy && isWithinRadius
                                    }">
                                        <span v-if="isLocating">Waiting for your location...</span>
                                        <span v-else-if="locationError">{{ locationError }}</span>
                                        <span v-else-if="hasPoorAccuracy">
                                            GPS accuracy is currently {{ accuracy }}m. Please move to an area with a clearer GPS signal and try again.
                                        </span>
                                        <span v-else-if="!isWithinRadius">
                                            You are {{ distanceFromOffice }}m away from the office. Must be within {{ props.office.radius_meters }}m.
                                        </span>
                                        <span v-else>
                                            Location detected. Accuracy: {{ accuracy }}m. Distance: {{ distanceFromOffice }}m. You are inside the office area.
                                        </span>
                                    </p>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex justify-center space-x-4">
                                <!-- Check In Button -->
                                <button 
                                    v-if="!todayAttendance?.check_in"
                                    @click="submitCheckIn"
                                    :disabled="form.processing || isLocating || !!locationError || hasPoorAccuracy || !isWithinRadius"
                                    class="w-full sm:w-auto px-8 py-4 bg-indigo-600 hover:bg-indigo-700 disabled:bg-gray-400 text-white text-lg font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                >
                                    <span v-if="form.processing">Checking In...</span>
                                    <span v-else>CHECK IN</span>
                                </button>

                                <!-- Check Out Button -->
                                <button 
                                    v-else-if="!todayAttendance?.check_out"
                                    @click="submitCheckOut"
                                    :disabled="form.processing || isLocating || !!locationError || hasPoorAccuracy || !isWithinRadius"
                                    class="w-full sm:w-auto px-8 py-4 bg-red-600 hover:bg-red-700 disabled:bg-gray-400 text-white text-lg font-medium rounded-lg shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                >
                                    <span v-if="form.processing">Checking Out...</span>
                                    <span v-else>CHECK OUT</span>
                                </button>
                                
                                <div v-else class="text-center w-full py-4 text-gray-500 font-medium">
                                    You have completed your attendance for today.
                                </div>
                            </div>
                            
                            <!-- Display backend validation errors if any -->
                            <div v-if="form.errors.location || form.errors.attendance" class="mt-4 text-center text-sm text-red-600">
                                {{ form.errors.location || form.errors.attendance }}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
