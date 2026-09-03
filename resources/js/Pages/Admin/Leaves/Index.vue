<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PaginatedData } from '@/types';

interface LeaveRequest {
    id: number;
    user: { name: string; employee_code: string };
    type: string;
    start_date: string;
    end_date: string;
    reason: string;
    status: string;
}

const props = defineProps<{
    leaves: PaginatedData<LeaveRequest>;
    filters: {
        status?: string;
    };
}>();

const statusFilter = ref(props.filters.status || 'pending');

watch(statusFilter, (val) => {
    router.get(route('admin.leaves.index'), { status: val }, { preserveState: true });
});

const showModal = ref(false);
const selectedLeave = ref<LeaveRequest | null>(null);

const form = useForm({
    status: 'approved',
    reviewer_reason: '',
});

const openReview = (leave: LeaveRequest) => {
    selectedLeave.value = leave;
    form.reset();
    form.status = 'approved';
    showModal.value = true;
};

const submitReview = () => {
    if (!selectedLeave.value) return;
    form.put(route('admin.leaves.update', selectedLeave.value.id), {
        onSuccess: () => {
            showModal.value = false;
        }
    });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Leave Approvals</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <div class="bg-white p-4 shadow sm:rounded-lg mb-6 flex space-x-4">
                    <select v-model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>

                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-if="props.leaves.data.length === 0" class="px-4 py-8 text-center text-gray-500">
                            No leave requests found.
                        </li>
                        <li v-for="leave in props.leaves.data" :key="leave.id" class="px-4 py-4 sm:px-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ leave.user.name }} <span class="text-gray-500 font-normal">({{ leave.user.employee_code }})</span></p>
                                    <p class="text-sm text-indigo-600 font-medium uppercase tracking-wider mt-1">{{ leave.type.replace('_', ' ') }}</p>
                                    <p class="text-sm text-gray-700 mt-1">{{ leave.start_date }} to {{ leave.end_date }}</p>
                                    <p class="text-sm text-gray-600 italic mt-1">"{{ leave.reason }}"</p>
                                </div>
                                <div class="ml-4">
                                    <button v-if="leave.status === 'pending'" @click="openReview(leave)" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-100 border border-indigo-200">
                                        Review
                                    </button>
                                    <span v-else class="px-3 py-1 text-xs rounded-full font-semibold" :class="leave.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ leave.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showModal = false"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Review Leave Request</h3>
                    <form @submit.prevent="submitReview" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Decision</label>
                            <select v-model="form.status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reviewer Notes (Required if rejecting)</label>
                            <textarea v-model="form.reviewer_reason" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm sm:text-sm"></textarea>
                        </div>
                        <div class="mt-5 sm:mt-6 flex flex-row-reverse">
                            <button type="submit" class="w-full sm:w-auto inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 sm:ml-3 sm:text-sm">
                                Submit
                            </button>
                            <button type="button" @click="showModal = false" class="w-full sm:w-auto mt-3 sm:mt-0 inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 sm:text-sm">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
