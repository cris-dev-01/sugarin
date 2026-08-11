<script lang="ts" setup>
import { ref, watch } from 'vue';
import { ChevronLeft, ChevronRight, XIcon } from 'lucide-vue-next';
import { useGlucoseStatus } from '@/composables/useGlucoseStatus';
import type { PaginatedPatientLogs, PatientSummaryRecentLog } from '@/types';

const props = defineProps<{
    show: boolean;
    patientId: number;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { statusTextClass, statusIcon } = useGlucoseStatus();

const isLoading = ref(false);
const logs = ref<PatientSummaryRecentLog[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const dateFilter = ref('');

async function loadLogs(page: number) {
    isLoading.value = true;

    try {
        const params = new URLSearchParams({ page: String(page) });

        if (dateFilter.value) {
            params.set('date', dateFilter.value);
        }

        const response = await fetch(`/patients/${props.patientId}/glucose-logs?${params.toString()}`, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const body = (await response.json()) as PaginatedPatientLogs;
            logs.value = body.data;
            currentPage.value = body.meta.current_page;
            lastPage.value = body.meta.last_page;
            total.value = body.meta.total;
        }
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => props.show,
    (show) => {
        if (show) {
            dateFilter.value = '';
            loadLogs(1);
        }
    }
);

function onSearchByDate() {
    loadLogs(1);
}

function onClearDateFilter() {
    dateFilter.value = '';
    loadLogs(1);
}

function goToPage(page: number) {
    if (page >= 1 && page <= lastPage.value) {
        loadLogs(page);
    }
}

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-CL');
}

function closeDrawer() {
    emit('close');
}
</script>

<template>
    <div>
        <div
            class="fixed inset-0 z-[51] hidden bg-[black]/60 px-4 transition-[display]"
            :class="{ '!block': props.show }"
            @click="closeDrawer"
        ></div>

        <nav
            class="fixed bottom-0 top-0 z-[51] w-full max-w-[420px] bg-white p-4 shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] transition-[right] duration-300 ltr:-right-[420px] rtl:-left-[420px] dark:bg-[#0e1726]"
            :class="{ 'ltr:!right-0 rtl:!left-0': props.show }"
        >
            <perfect-scrollbar
                :options="{ swipeEasing: true, wheelPropagation: false }"
                class="relative h-full overflow-x-hidden ltr:-mr-3 ltr:pr-3 rtl:-ml-3 rtl:pl-3"
            >
                <div class="relative pb-5 text-start">
                    <button
                        type="button"
                        class="absolute top-0 opacity-30 hover:opacity-100 dark:text-white ltr:right-0 rtl:left-0"
                        @click="closeDrawer"
                    >
                        <XIcon :size="22" />
                    </button>
                    <h4 class="mb-1 dark:text-white">Historial de lecturas</h4>
                    <p class="text-white-dark">{{ total }} lecturas registradas.</p>
                </div>

                <div class="mb-4 flex items-end gap-2">
                    <div class="flex-1">
                        <label class="mb-1 block text-xs text-white-dark">Buscar por fecha</label>
                        <input v-model="dateFilter" type="date" class="form-input" @change="onSearchByDate" />
                    </div>
                    <button v-if="dateFilter" type="button" class="btn btn-outline-dark dark:btn-dark" @click="onClearDateFilter">
                        Limpiar
                    </button>
                </div>

                <div v-if="isLoading" class="flex justify-center py-8">
                    <span
                        class="inline-flex h-6 w-6 animate-spin rounded-full border-2 border-black !border-l-transparent dark:border-white"
                    ></span>
                </div>

                <template v-else>
                    <p v-if="!logs.length" class="py-4 text-white-dark">Sin lecturas para mostrar.</p>

                    <ul v-else class="divide-y divide-white-light dark:divide-[#1b2e4b]">
                        <li v-for="log in logs" :key="log.id" class="flex items-center justify-between py-3 text-sm">
                            <span class="flex items-center gap-1.5 dark:text-white-light">
                                <component :is="statusIcon(log.status)" v-if="statusIcon(log.status)" :size="14" :class="statusTextClass(log.status)" />
                                {{ log.value }} mg/dL · {{ log.time_block }}
                            </span>
                            <span class="flex flex-col text-right text-white-dark">
                                <span :class="statusTextClass(log.status)">{{ log.status }}</span>
                                <span>{{ formatDate(log.created_at) }}</span>
                            </span>
                        </li>
                    </ul>

                    <div v-if="logs.length" class="mt-4 flex items-center justify-between">
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="currentPage <= 1"
                            @click="goToPage(currentPage - 1)"
                        >
                            <ChevronLeft :size="16" />
                        </button>
                        <span class="text-sm text-white-dark">Página {{ currentPage }} de {{ lastPage }}</span>
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="currentPage >= lastPage"
                            @click="goToPage(currentPage + 1)"
                        >
                            <ChevronRight :size="16" />
                        </button>
                    </div>
                </template>
            </perfect-scrollbar>
        </nav>
    </div>
</template>
