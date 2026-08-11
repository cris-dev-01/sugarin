<script lang="ts" setup>
import { computed, ref, watch } from 'vue';
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay, DialogTitle } from '@headlessui/vue';
import { Info, X } from 'lucide-vue-next';
import type {
    TriageCriteria,
    TriagePatientsByCriteria,
    TriagePatientLowRecent,
    TriagePatientInactive,
    TriagePatientGoodControl,
} from '@/types';

const props = defineProps<{
    show: boolean;
    criteria: TriageCriteria | null;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const CRITERIA_META: Record<TriageCriteria, { title: string; emptyText: string }> = {
    low_recent: {
        title: 'Pacientes con hipoglucemia reciente',
        emptyText: 'Ningún paciente tiene lecturas "Bajo" recientes.',
    },
    inactive: {
        title: 'Pacientes sin registro reciente',
        emptyText: 'Todos los pacientes tienen registros recientes.',
    },
    good_control: {
        title: 'Pacientes en buen control',
        emptyText: 'Ningún paciente alcanza el umbral de buen control todavía.',
    },
};

const isLoading = ref(false);
const patients = ref<TriagePatientsByCriteria['patients']>([]);

const meta = computed(() => (props.criteria ? CRITERIA_META[props.criteria] : null));
const lowRecentPatients = computed(() => (props.criteria === 'low_recent' ? (patients.value as TriagePatientLowRecent[]) : []));
const inactivePatients = computed(() => (props.criteria === 'inactive' ? (patients.value as TriagePatientInactive[]) : []));
const goodControlPatients = computed(() => (props.criteria === 'good_control' ? (patients.value as TriagePatientGoodControl[]) : []));

async function loadPatients(criteria: TriageCriteria) {
    isLoading.value = true;
    patients.value = [];

    try {
        const response = await fetch(`/dashboard/triage-patients?criteria=${criteria}`, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const body = await response.json();
            patients.value = (body.data as TriagePatientsByCriteria).patients;
        }
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => [props.show, props.criteria] as const,
    ([show, criteria]) => {
        if (show && criteria) {
            loadPatients(criteria);
        }
    },
    { immediate: true }
);

function formatDate(value: string): string {
    return new Date(value).toLocaleString('es-CL');
}

function closeModal() {
    emit('close');
}
</script>

<template>
    <TransitionRoot appear :show="props.show" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <DialogOverlay class="fixed inset-0 bg-[black]/60" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-start justify-center px-4 py-8">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="panel w-full max-w-2xl overflow-hidden rounded-lg border-0 p-0 text-black dark:text-white-dark">
                            <div class="flex items-center justify-between border-b border-white-light p-5 dark:border-[#1b2e4b]">
                                <DialogTitle class="text-lg font-semibold dark:text-white-light">
                                    {{ meta?.title }}
                                </DialogTitle>
                                <button type="button" class="text-white-dark hover:text-danger" @click="closeModal">
                                    <X :size="20" />
                                </button>
                            </div>

                            <div class="max-h-[60vh] overflow-y-auto p-5">
                                <div v-if="isLoading" class="flex justify-center py-8">
                                    <span
                                        class="inline-flex h-6 w-6 animate-spin rounded-full border-2 border-black !border-l-transparent dark:border-white"
                                    ></span>
                                </div>

                                <template v-else>
                                    <p v-if="!patients.length" class="flex gap-2 py-4">
                                        <Info :size="20" />
                                        {{ meta?.emptyText }}
                                    </p>

                                    <div v-else class="table-responsive">
                                        <table v-if="props.criteria === 'low_recent'" class="w-full">
                                            <thead>
                                                <tr class="border-b border-white-light dark:border-[#1b2e4b]">
                                                    <th class="pb-2 text-left font-semibold dark:text-white-light">Paciente</th>
                                                    <th class="pb-2 text-right font-semibold dark:text-white-light">Valor</th>
                                                    <th class="pb-2 text-right font-semibold dark:text-white-light">Bloque</th>
                                                    <th class="pb-2 text-right font-semibold dark:text-white-light">Fecha</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white-light dark:divide-[#1b2e4b]">
                                                <tr v-for="patient in lowRecentPatients" :key="patient.id">
                                                    <td class="py-3 dark:text-white-light">{{ patient.name }}</td>
                                                    <td class="py-3 text-right text-danger">{{ patient.last_low_value }} mg/dL</td>
                                                    <td class="py-3 text-right text-white-dark">{{ patient.last_low_time_block }}</td>
                                                    <td class="py-3 text-right text-white-dark">{{ formatDate(patient.last_low_at) }}</td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table v-else-if="props.criteria === 'inactive'" class="w-full">
                                            <thead>
                                                <tr class="border-b border-white-light dark:border-[#1b2e4b]">
                                                    <th class="pb-2 text-left font-semibold dark:text-white-light">Paciente</th>
                                                    <th class="pb-2 text-right font-semibold dark:text-white-light">Última lectura</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white-light dark:divide-[#1b2e4b]">
                                                <tr v-for="patient in inactivePatients" :key="patient.id">
                                                    <td class="py-3 dark:text-white-light">{{ patient.name }}</td>
                                                    <td class="py-3 text-right text-white-dark">
                                                        {{ patient.last_log_at ? formatDate(patient.last_log_at) : 'Sin lecturas registradas' }}
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                        <table v-else-if="props.criteria === 'good_control'" class="w-full">
                                            <thead>
                                                <tr class="border-b border-white-light dark:border-[#1b2e4b]">
                                                    <th class="pb-2 text-left font-semibold dark:text-white-light">Paciente</th>
                                                    <th class="pb-2 text-right font-semibold dark:text-white-light">% en rango</th>
                                                </tr>
                                            </thead>
                                            <tbody class="divide-y divide-white-light dark:divide-[#1b2e4b]">
                                                <tr v-for="patient in goodControlPatients" :key="patient.id">
                                                    <td class="py-3 dark:text-white-light">{{ patient.name }}</td>
                                                    <td class="py-3 text-right font-semibold text-success">{{ patient.in_range_percentage }}%</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </template>
                            </div>

                            <div class="flex justify-end border-t border-white-light p-5 dark:border-[#1b2e4b]">
                                <button type="button" class="btn btn-outline-dark dark:btn-dark" @click="closeModal">Volver</button>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
