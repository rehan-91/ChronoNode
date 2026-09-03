<script setup lang="ts">
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

interface User {
    id: number;
    name: string;
    email: string;
}

interface AuditLog {
    id: number;
    user_id: number;
    action: string;
    model_type: string;
    model_id: string;
    old_values: Record<string, any> | null;
    new_values: Record<string, any> | null;
    ip_address: string;
    user_agent: string;
    created_at: string;
    user: User | null;
}

interface Pagination {
    data: AuditLog[];
    links: any[];
}

const props = defineProps<{
    logs: Pagination;
}>();

const selectedLog = ref<AuditLog | null>(null);

const formatJSON = (data: any) => {
    if (!data) return 'None';
    return JSON.stringify(data, null, 2);
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Immutable Audit Trail</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white shadow sm:rounded-lg overflow-hidden">
                    <div class="px-4 py-5 sm:px-6 bg-gray-50 border-b border-gray-200">
                        <h3 class="text-lg leading-6 font-medium text-gray-900">Security Log</h3>
                        <p class="mt-1 max-w-2xl text-sm text-gray-500">
                            Append-only ledger of administrative actions, data manipulations, and system registry changes.
                        </p>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Signature / User</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action Vector</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Target Resource</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Network Signature</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="log in props.logs.data" :key="log.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ new Date(log.created_at).toLocaleString() }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ log.user?.name || 'System / Ghost' }}</div>
                                        <div class="text-sm text-gray-500">{{ log.user?.email || 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                            {{ log.action }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ log.model_type }} #{{ log.model_id }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 font-mono">{{ log.ip_address }}</div>
                                        <div class="text-xs text-gray-400 truncate w-32" :title="log.user_agent">{{ log.user_agent }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <button @click="selectedLog = log" class="text-indigo-600 hover:text-indigo-900">Inspect</button>
                                    </td>
                                </tr>
                                <tr v-if="props.logs.data.length === 0">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No audit records found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

        <!-- Inspection Modal -->
        <div v-if="selectedLog" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="selectedLog = null"></div>

                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                    Cryptographic Data Variance Inspection
                                </h3>
                                <div class="mt-4 grid grid-cols-2 gap-4">
                                    <div class="bg-gray-50 p-4 rounded-md border border-red-100">
                                        <h4 class="text-sm font-semibold text-red-800 mb-2">Previous State (old_values)</h4>
                                        <pre class="text-xs text-gray-700 overflow-x-auto font-mono whitespace-pre-wrap">{{ formatJSON(selectedLog.old_values) }}</pre>
                                    </div>
                                    <div class="bg-gray-50 p-4 rounded-md border border-green-100">
                                        <h4 class="text-sm font-semibold text-green-800 mb-2">New State (new_values)</h4>
                                        <pre class="text-xs text-gray-700 overflow-x-auto font-mono whitespace-pre-wrap">{{ formatJSON(selectedLog.new_values) }}</pre>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm" @click="selectedLog = null">
                            Close Inspection
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
