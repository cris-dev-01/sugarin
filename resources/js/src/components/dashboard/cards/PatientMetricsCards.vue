<script lang="ts" setup>
import type { PatientSummary } from '@/types';

const props = defineProps<{
    summary: PatientSummary;
}>();
</script>

<template>
    <div class="mb-6 grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-4">
        <div class="panel">
            <div class="text-sm text-white-dark">En rango (mañana)</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">
                <template v-if="props.summary.in_range_percentage['mañana'] !== null">{{ props.summary.in_range_percentage['mañana'] }}%</template>
                <template v-else>—</template>
            </div>
        </div>
        <div class="panel">
            <div class="text-sm text-white-dark">En rango (anochecer)</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">
                <template v-if="props.summary.in_range_percentage['anochecer'] !== null">{{ props.summary.in_range_percentage['anochecer'] }}%</template>
                <template v-else>—</template>
            </div>
        </div>
        <div class="panel">
            <div class="text-sm text-white-dark">Eventos Bajo / Elevado</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">
                {{ props.summary.events.bajo.total }} / {{ props.summary.events.elevado.total }}
            </div>
        </div>
        <div class="panel">
            <div class="text-sm text-white-dark">Adherencia de registro</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">{{ props.summary.adherence_percentage }}%</div>
        </div>
        <div class="panel">
            <div class="text-sm text-white-dark">Promedio mañana (± variabilidad)</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">
                <template v-if="props.summary.value_stats['mañana'].avg !== null">
                    {{ props.summary.value_stats['mañana'].avg }} ± {{ props.summary.value_stats['mañana'].stddev }} mg/dL
                </template>
                <template v-else>—</template>
            </div>
        </div>
        <div class="panel">
            <div class="text-sm text-white-dark">Promedio anochecer (± variabilidad)</div>
            <div class="mt-2 text-2xl font-bold dark:text-white-light">
                <template v-if="props.summary.value_stats['anochecer'].avg !== null">
                    {{ props.summary.value_stats['anochecer'].avg }} ± {{ props.summary.value_stats['anochecer'].stddev }} mg/dL
                </template>
                <template v-else>—</template>
            </div>
        </div>
        <div class="panel sm:col-span-2">
            <div class="text-sm text-white-dark">Última lectura</div>
            <div v-if="props.summary.last_reading" class="mt-2 text-2xl font-bold dark:text-white-light">
                {{ props.summary.last_reading.value }} mg/dL
                <span class="text-sm font-normal text-white-dark">
                    ({{ props.summary.last_reading.status }} · {{ props.summary.last_reading.time_block }})
                </span>
            </div>
            <div v-else class="mt-2 text-white-dark">Sin lecturas registradas.</div>
        </div>
    </div>
</template>
