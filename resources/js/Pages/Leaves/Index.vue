<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PaginatedData } from '@/types';

interface LeaveRequest {
    id: number;
    type: string;
    start_date: string;
    end_date: string;
    reason: string;
    status: string;
    reviewer_reason: string | null;
    created_at: string;
}

const props = defineProps<{
    leaves: PaginatedData<LeaveRequest>;
}>();

const showModal = ref(false);

const form = useForm({
    type: 'annual',
    start_date: '',
    end_date: '',
    reason: '',
});

const submitLeave = () => {
    form.post(route('leaves.store'), {
        onSuccess: () => {
            showModal.value = false;
            form.reset();
        }
    });
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Leave Requests</h2>
                <button @click="showModal = true" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-700">
                    Request Leave
                </button>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-if="props.leaves.data.length === 0" class="px-4 py-8 text-center text-gray-500">
                            You have no leave requests.
                        </li>
                        <li v-for="leave in props.leaves.data" :key="leave.id" class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 uppercase tracking-wider">{{ leave.type.replace('_', ' ') }}</p>
                                    <div class="mt-2 text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900">Dates:</span> {{ leave.start_date }} to {{ leave.end_date }}
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600 italic">"{{ leave.reason }}"</p>
                                    <p v-if="leave.reviewer_reason" class="mt-1 text-sm text-red-600 font-medium">Note: {{ leave.reviewer_reason }}</p>
                                </div>
                                <div class="ml-2 flex-shrink-0 flex">
                                    <span class="px-3 py-1 text-xs rounded-full font-semibold" :class="getStatusColor(leave.status)">
                                        {{ leave.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>

        <!-- Request Modal -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Request Leave</h3>
                    <form @submit.prevent="submitLeave" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Leave Type</label>
                            <select v-model="form.type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="annual">Annual Leave</option>
                                <option value="sick">Sick Leave</option>
                                <option value="unpaid">Unpaid Leave</option>
                                <option value="maternity">Maternity</option>
                                <option value="paternity">Paternity</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Start Date</label>
                                <input type="date" v-model="form.start_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" v-model="form.end_date" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason</label>
                            <textarea v-model="form.reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                        </div>
                        <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" :disabled="form.processing" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:w-auto sm:text-sm">
                                Submit Request
                            </button>
                            <button type="button" @click="showModal = false" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:mt-0 sm:w-auto sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
