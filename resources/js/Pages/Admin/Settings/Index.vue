<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Settings {
    timezone: string;
    gps_accuracy_threshold_meters: number;
    default_geofence_radius_meters: number;
    late_arrival_buffer_minutes: number;
    auto_checkout_limit_hours: number;
}

const props = defineProps<{
    settings: Settings;
}>();

const form = useForm({
    timezone: props.settings.timezone,
    gps_accuracy_threshold_meters: props.settings.gps_accuracy_threshold_meters,
    default_geofence_radius_meters: props.settings.default_geofence_radius_meters,
    late_arrival_buffer_minutes: props.settings.late_arrival_buffer_minutes,
    auto_checkout_limit_hours: props.settings.auto_checkout_limit_hours,
});

const submit = () => {
    form.post(route('admin.settings.update'), {
        preserveScroll: true
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Global Application Settings</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">System Registry Variables</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Centralized configuration to eliminate magic numbers. Changes are aggressively cached and logged to the immutable audit trail.
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="submit" class="space-y-6">
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                
                                <!-- Localization -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900 border-b pb-2">Localization & Time</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">System Timezone</label>
                                        <input type="text" v-model="form.timezone" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Must be a valid PHP timezone string (e.g., UTC, America/New_York)</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Late Arrival Buffer (Minutes)</label>
                                        <input type="number" v-model="form.late_arrival_buffer_minutes" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Grace period before a late tag is applied.</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Auto-Checkout Limit (Hours)</label>
                                        <input type="number" v-model="form.auto_checkout_limit_hours" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Time after check-in when an automatic missing checkout status is applied.</p>
                                    </div>
                                </div>

                                <!-- Geospatial Constraints -->
                                <div class="space-y-4">
                                    <h4 class="font-medium text-gray-900 border-b pb-2">Geospatial Boundaries</h4>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Default Geofence Radius (Meters)</label>
                                        <input type="number" v-model="form.default_geofence_radius_meters" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Standard radius applied when mapping new office/site locations.</p>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">GPS Accuracy Threshold (Meters)</label>
                                        <input type="number" v-model="form.gps_accuracy_threshold_meters" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                        <p class="mt-1 text-xs text-gray-500">Maximum permitted location variance (accuracy radius) for a valid check-in ping. Rejects wild GPS spikes.</p>
                                    </div>
                                </div>

                            </div>

                            <div class="flex justify-end border-t pt-5">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none">
                                    Apply Configuration to Registry
                                </button>
                            </div>
                            
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
