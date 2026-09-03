<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface Metrics {
    total_employees: number;
    present: number;
    late: number;
    half_day: number;
    on_leave: number;
    missing_checkout: number;
    absent: number;
}

const props = defineProps<{
    metrics: Metrics;
}>();
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Admin & Manager Overview</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <!-- Data Overview Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Total Active Staff</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.total_employees }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-green-500">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Present Today</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.present }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-yellow-500">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Late Arrivals</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.late }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Absent / No Show</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.absent }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-400">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">Missing Checkouts</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.missing_checkout }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-400">
                        <div class="flex items-center">
                            <div class="flex-1">
                                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider">On Leave / Holiday</h3>
                                <p class="mt-2 text-3xl font-bold text-gray-900">{{ metrics.on_leave }}</p>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Fast Actions / Highlights -->
                <div class="bg-white shadow sm:rounded-lg p-6 mt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Operational Status</h3>
                    <p class="text-gray-600 text-sm">
                        This dashboard summarizes real-time presence data scoped strictly to your permissions.
                        If you are a Manager, these metrics apply only to your assigned subordinates.
                        If you are an HR Admin, this reflects the entire organization.
                    </p>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
