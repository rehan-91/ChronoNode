<script setup lang="ts">
import { useForm, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { ref } from 'vue';

interface ReportFile {
    name: string;
    size: string;
    last_modified: string;
}

const props = defineProps<{
    files: ReportFile[];
}>();

const form = useForm({
    type: 'monthly_master',
    start_date: '',
    end_date: '',
    department_id: '',
});

const submitGenerate = () => {
    form.post(route('admin.reports.generate'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('type', 'start_date', 'end_date', 'department_id');
        }
    });
};

const downloadFile = (filename: string) => {
    window.location.href = route('admin.reports.download', filename);
};

const deleteFile = (filename: string) => {
    if (confirm('Are you sure you want to permanently delete this report file?')) {
        router.delete(route('admin.reports.destroy', filename), {
            preserveScroll: true
        });
    }
};

const refreshList = () => {
    router.reload({ only: ['files'] });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Data Reports & Exports</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Generator Panel -->
                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Compile New Report</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Extensive reports are processed in the background. Generating a master sheet may take several moments.
                        </p>
                    </div>
                    
                    <div class="p-6">
                        <form @submit.prevent="submitGenerate" class="space-y-6">
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Report Type</label>
                                    <select v-model="form.type" required class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                        <option value="daily_log">Daily Log</option>
                                        <option value="monthly_master">Monthly Master Sheet</option>
                                        <option value="late_patterns">Late Arrival Patterns</option>
                                        <option value="overtime_aggregates">Overtime Aggregates</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                    <input type="date" v-model="form.start_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700">End Date</label>
                                    <input type="date" v-model="form.end_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Department Filter (Optional)</label>
                                    <!-- In a real app, populate with actual DB departments. Hardcoded fallback for UI logic display -->
                                    <input type="number" v-model="form.department_id" placeholder="Dept ID..." class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>

                            </div>

                            <div class="flex justify-end">
                                <button type="submit" :disabled="form.processing" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                    <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Trigger Background Generation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Export Downloads Queue -->
                <div class="bg-white shadow sm:rounded-lg">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Available Exports (CSV)</h3>
                            <p class="mt-1 max-w-2xl text-sm text-gray-500">Files generated by the background queue are securely stored here for payroll download.</p>
                        </div>
                        <button @click="refreshList" class="text-sm text-indigo-600 font-medium hover:text-indigo-900">
                            ↻ Refresh List
                        </button>
                    </div>
                    
                    <div class="overflow-hidden">
                        <ul role="list" class="divide-y divide-gray-200">
                            <li v-if="props.files.length === 0" class="px-6 py-8 text-center text-gray-500">
                                No export files currently available.
                            </li>
                            <li v-for="file in props.files" :key="file.name" class="px-6 py-4 hover:bg-gray-50 flex items-center justify-between">
                                <div class="flex items-center">
                                    <svg class="flex-shrink-0 h-8 w-8 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m6.75 12l-3-3m0 0l-3 3m3-3v6m-1.5-15H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    <div class="ml-4">
                                        <p class="text-sm font-medium text-gray-900">{{ file.name }}</p>
                                        <div class="flex space-x-4 mt-1">
                                            <p class="text-xs text-gray-500">Size: {{ file.size }}</p>
                                            <p class="text-xs text-gray-500">Generated: {{ file.last_modified }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex space-x-3">
                                    <button @click="downloadFile(file.name)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none">
                                        Download
                                    </button>
                                    <button @click="deleteFile(file.name)" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none">
                                        Delete
                                    </button>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
