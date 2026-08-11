<script lang="ts" setup>
import type { PatientSummaryStatusDistributionItem } from '@/types';

const props = defineProps<{
    distribution: PatientSummaryStatusDistributionItem[];
}>();

const colorByStatus: Record<string, string> = {
    'Rango normal': 'bg-success',
    'Elevado - fuera de rango normal': 'bg-warning',
    'Bajo - fuera de rango normal': 'bg-danger',
};
</script>

<template>
    <div class="panel">
        <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Distribución por estado</h5>
        <div v-if="!props.distribution.length" class="text-white-dark">Sin lecturas registradas en el período.</div>
        <div v-else class="space-y-4">
            <div v-for="item in props.distribution" :key="item.status">
                <div class="mb-1 flex items-center justify-between dark:text-white-light">
                    <span>{{ item.status }}</span>
                    <span>{{ item.percentage }}%</span>
                </div>
                <div class="h-2.5 w-full rounded-full bg-[#ebedf2] dark:bg-[#1b2e4b]">
                    <div
                        class="h-2.5 rounded-full"
                        :class="colorByStatus[item.status] ?? 'bg-primary'"
                        :style="{ width: item.percentage + '%' }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>
