<script lang="ts" setup>
import { AlertTriangle } from 'lucide-vue-next';
import type { TriagePatientRisk } from '@/types';

const props = defineProps<{
    patients: TriagePatientRisk[];
    selectedId: number | null;
}>();

const emit = defineEmits<{
    (e: 'select', id: number): void;
}>();
</script>

<template>
    <div v-if="props.patients.length > 1" class="panel mb-6">
        <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Pacientes (ordenados por riesgo)</h5>
        <div class="space-y-2">
            <button
                v-for="patient in props.patients"
                :key="patient.id"
                type="button"
                class="flex w-full items-center justify-between rounded-md border p-3 text-left"
                :class="patient.id === props.selectedId ? 'border-primary bg-primary/10' : 'border-white-light dark:border-[#1b2e4b]'"
                @click="emit('select', patient.id)"
            >
                <div class="flex items-center gap-2">
                    <AlertTriangle v-if="patient.has_low_recent" :size="16" class="text-danger" />
                    <span class="dark:text-white-light">{{ patient.name }}</span>
                </div>
                <div class="text-sm text-white-dark">
                    <span v-if="patient.in_range_percentage !== null">{{ patient.in_range_percentage }}% en rango</span>
                    <span v-else>Sin lecturas</span>
                </div>
            </button>
        </div>
    </div>
</template>
