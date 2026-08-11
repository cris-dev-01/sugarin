<script lang="ts" setup>
import { Clock4, Info, ShieldCheck } from 'lucide-vue-next';
import type { TriageCriteria, TriageOverview } from '@/types';

const props = defineProps<{
    triage: TriageOverview;
}>();

const emit = defineEmits<{
    (e: 'select', criteria: TriageCriteria): void;
}>();
</script>

<template>
    <div class="mb-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
        <button
            type="button"
            class="panel relative overflow-hidden text-left transition-transform hover:-translate-y-0.5"
            :class="props.triage.low_recent_count > 0 ? 'bg-gradient-to-r from-red-500 to-red-400 text-white' : ''"
            @click="emit('select', 'low_recent')"
        >
            <div class="absolute -bottom-12 h-28 w-28 ltr:-right-12 rtl:-left-12">
                <Info class="h-full w-20 text-danger opacity-30" />
            </div>
            <div class="flex items-center justify-between">
                <div class="text-md font-semibold">Pacientes con hipoglucemia reciente</div>
            </div>
            <div class="mt-5 text-3xl font-bold">{{ props.triage.low_recent_count }}</div>
            <div class="mt-2 text-sm opacity-80">Lectura "Bajo" en las últimas 48 horas.</div>
        </button>

        <button
            type="button"
            class="panel relative overflow-hidden text-left transition-transform hover:-translate-y-0.5"
            @click="emit('select', 'inactive')"
        >
            <div class="absolute -bottom-12 h-28 w-28 ltr:-right-12 rtl:-left-12">
                <Clock4 class="h-full w-20 text-warning opacity-30" />
            </div>
            <div class="flex items-center justify-between dark:text-white-light">
                <div class="text-md font-semibold">Pacientes sin registro reciente</div>
            </div>
            <div class="mt-5 text-3xl font-bold dark:text-white-light">{{ props.triage.inactive_count }}</div>
            <div class="mt-2 text-sm text-white-dark">Sin ninguna lectura en las últimas 48 horas.</div>
        </button>

        <button
            type="button"
            class="panel relative overflow-hidden text-left transition-transform hover:-translate-y-0.5"
            @click="emit('select', 'good_control')"
        >
            <div class="absolute -bottom-12 h-28 w-28 ltr:-right-12 rtl:-left-12">
                <ShieldCheck class="h-full w-20 text-success opacity-30" />
            </div>
            <div class="flex items-center justify-between dark:text-white-light">
                <div class="text-md font-semibold">Pacientes en buen control</div>
            </div>
            <div class="mt-5 text-3xl font-bold dark:text-white-light">{{ props.triage.good_control_percentage }}%</div>
            <div class="mt-2 text-sm text-white-dark">Con al menos 80% de sus lecturas en rango normal.</div>
        </button>
    </div>
</template>
