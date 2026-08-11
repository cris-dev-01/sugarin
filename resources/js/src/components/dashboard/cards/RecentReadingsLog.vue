<script lang="ts" setup>
import { computed, ref } from 'vue';
import PatientLogsDrawer from '@/components/dashboard/drawer/PatientLogsDrawer.vue';
import { useGlucoseStatus } from '@/composables/useGlucoseStatus';
import type { PatientSummaryRecentLog } from '@/types';

const VISIBLE_LOGS_LIMIT = 10;

const props = defineProps<{
    logs: PatientSummaryRecentLog[];
    patientId: number;
}>();

const showDrawer = ref(false);
const { statusTextClass, statusIcon } = useGlucoseStatus();

const visibleLogs = computed(() => props.logs.slice(0, VISIBLE_LOGS_LIMIT));

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-CL');
}
</script>

<template>
    <div class="panel h-full pb-0">
        <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Lecturas recientes</h5>
        <div v-if="!visibleLogs.length" class="pb-5 text-white-dark">Sin lecturas registradas.</div>
        <div v-else class="relative mb-4 ltr:-mr-3 ltr:pr-3 rtl:-ml-3 rtl:pl-3">
            <div class="text-sm">
                <div v-for="log in visibleLogs" :key="log.id" class="flex items-center justify-between py-1.5">
                    <div class="flex items-center gap-1.5 dark:text-white-light">
                        <component :is="statusIcon(log.status)" v-if="statusIcon(log.status)" :size="14" :class="statusTextClass(log.status)" />
                        {{ log.value }} mg/dL · {{ log.time_block }}
                    </div>
                    <div class="text-xs text-white-dark ltr:ml-auto rtl:mr-auto dark:text-gray-500">
                        <span :class="statusTextClass(log.status)">{{ log.status }}</span> · {{ formatDate(log.created_at) }}
                    </div>
                </div>
            </div>
        </div>
        <div class="border-t border-white-light dark:border-white/10">
            <button
                type="button"
                class="group flex w-full items-center justify-center p-4 font-semibold hover:text-primary"
                @click="showDrawer = true"
            >
                Ver más
            </button>
        </div>

        <PatientLogsDrawer :show="showDrawer" :patient-id="props.patientId" @close="showDrawer = false" />
    </div>
</template>
