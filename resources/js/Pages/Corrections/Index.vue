<script setup lang="ts">
import { ref, watch } from 'vue';
import { useForm, usePage, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import type { PaginatedData } from '@/types';

interface Correction {
    id: number;
    user: { name: string; employee_code: string };
    attendance: { date: string; check_in: string | null; check_out: string | null } | null;
    requested_check_in: string | null;
    requested_check_out: string | null;
    reason: string;
    status: string;
    created_at: string;
}

const props = defineProps<{
    corrections: PaginatedData<Correction>;
    filters: {
        status?: string;
    };
}>();

const statusFilter = ref(props.filters.status || 'pending');

watch(statusFilter, (val) => {
    router.get(route('corrections.index'), { status: val }, { preserveState: true });
});

// Modal State
const showModal = ref(false);
const selectedCorrection = ref<Correction | null>(null);

const form = useForm({
    status: 'approved',
    reason: '',
    edited_check_in: null as string | null,
    edited_check_out: null as string | null,
});

const openReviewModal = (correction: Correction) => {
    selectedCorrection.value = correction;
    form.reset();
    form.status = 'approved';
    form.reason = '';
    form.edited_check_in = correction.requested_check_in ? new Date(correction.requested_check_in).toISOString().slice(0, 16) : null;
    form.edited_check_out = correction.requested_check_out ? new Date(correction.requested_check_out).toISOString().slice(0, 16) : null;
    showModal.value = true;
};

const closeReviewModal = () => {
    showModal.value = false;
    selectedCorrection.value = null;
    form.reset();
};

const submitReview = () => {
    if (!selectedCorrection.value) return;
    
    // Formatting dates to ISO if provided
    const payload = {
        status: form.status,
        reason: form.reason,
        edited_check_in: form.edited_check_in ? new Date(form.edited_check_in).toISOString() : null,
        edited_check_out: form.edited_check_out ? new Date(form.edited_check_out).toISOString() : null,
    };

    router.post(route('corrections.review', selectedCorrection.value.id), payload, {
        onSuccess: () => {
            closeReviewModal();
        },
        preserveScroll: true
    });
};

const formatTime = (time: string | null) => {
    if (!time) return '--:--';
    return new Date(time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Attendance Corrections</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Filters -->
                <div class="bg-white p-4 shadow sm:rounded-lg mb-6 flex justify-between items-center">
                    <div class="flex space-x-4">
                        <select v-model="statusFilter" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>

                <!-- Corrections List -->
                <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                    <ul role="list" class="divide-y divide-gray-200">
                        <li v-if="props.corrections.data.length === 0" class="px-4 py-8 text-center text-gray-500">
                            No corrections found for the selected status.
                        </li>
                        <li v-for="correction in props.corrections.data" :key="correction.id" class="px-4 py-4 sm:px-6 hover:bg-gray-50">
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
                                <div>
                                    <p class="text-sm font-medium text-indigo-600 truncate">
                                        {{ correction.user.name }} <span class="text-gray-500 text-xs">({{ correction.user.employee_code }})</span>
                                    </p>
                                    <div class="mt-2 text-sm text-gray-700">
                                        <span class="font-semibold text-gray-900">Date:</span> {{ correction.attendance?.date || 'N/A' }}
                                    </div>
                                    <div class="mt-1 flex flex-wrap gap-4 text-xs text-gray-500">
                                        <div>
                                            <span class="block text-gray-400">Current</span>
                                            In: {{ formatTime(correction.attendance?.check_in || null) }} <br>
                                            Out: {{ formatTime(correction.attendance?.check_out || null) }}
                                        </div>
                                        <div>
                                            <span class="block text-blue-400 font-medium">Requested</span>
                                            In: {{ formatTime(correction.requested_check_in) }} <br>
                                            Out: {{ formatTime(correction.requested_check_out) }}
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-600 italic">"{{ correction.reason }}"</p>
                                </div>
                                <div class="mt-4 sm:mt-0 ml-0 sm:ml-4 flex-shrink-0">
                                    <button v-if="correction.status === 'pending'" @click="openReviewModal(correction)" class="bg-indigo-50 text-indigo-700 px-4 py-2 rounded-md text-sm font-medium hover:bg-indigo-100 border border-indigo-200">
                                        Review Request
                                    </button>
                                    <span v-else class="px-3 py-1 text-sm rounded-full font-medium" :class="correction.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                                        {{ correction.status.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </li>
                    </ul>
                    
                    <!-- Pagination (omitted for brevity but normally identical to others) -->
                </div>

            </div>
        </div>

        <!-- Review Modal -->
        <div v-if="showModal" class="fixed z-10 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="closeReviewModal"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                <div class="inline-block align-bottom bg-white rounded-lg px-4 pt-5 pb-4 text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full sm:p-6">
                    <div>
                        <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                            Review Correction Request
                        </h3>
                        <div class="mt-2 text-sm text-gray-500">
                            {{ selectedCorrection?.user.name }} is requesting to change their attendance for {{ selectedCorrection?.attendance?.date }}.
                        </div>
                        
                        <form @submit.prevent="submitReview" class="mt-4 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Action</label>
                                <select v-model="form.status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                            </div>

                            <div v-if="form.status === 'approved'" class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Final Check In</label>
                                    <input type="datetime-local" v-model="form.edited_check_in" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Final Check Out</label>
                                    <input type="datetime-local" v-model="form.edited_check_out" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                                </div>
                                <p class="col-span-2 text-xs text-gray-500">You can edit the requested times before approving.</p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700">Reason / Notes (Required)</label>
                                <textarea v-model="form.reason" rows="3" required class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" placeholder="Enter reason for approval or rejection..."></textarea>
                            </div>

                            <div class="mt-5 sm:mt-6 sm:flex sm:flex-row-reverse">
                                <button type="submit" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                                    Submit Decision
                                </button>
                                <button type="button" @click="closeReviewModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:w-auto sm:text-sm">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
