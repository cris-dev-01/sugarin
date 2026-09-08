<script lang="ts" setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PatientMetricsCards from '@/components/dashboard/cards/PatientMetricsCards.vue';
import StreakCard from '@/components/dashboard/cards/StreakCard.vue';
import RecentReadingsLog from '@/components/dashboard/cards/RecentReadingsLog.vue';
import StatusDistributionBars from '@/components/dashboard/cards/StatusDistributionBars.vue';
import TrendChart from '@/components/dashboard/charts/TrendChart.vue';
import type { PatientSummary, SummaryPeriod } from '@/types';

const props = defineProps<{
    summary: PatientSummary | null;
}>();

const PERIOD_OPTIONS: SummaryPeriod[] = [7, 30, 90];

const period = ref<SummaryPeriod>((props.summary?.period_days as SummaryPeriod) ?? 30);
const isLoading = ref(false);

function onChangePeriod(newPeriod: SummaryPeriod) {
    period.value = newPeriod;
    isLoading.value = true;

    router.reload({
        data: { period: newPeriod },
        only: ['summary'],
        onFinish: () => {
            isLoading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Mi Resumen" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li><span>Mi Resumen</span></li>
            </ul>

            <div class="pt-5">
                <template v-if="props.summary">
                    <div class="sticky top-[70px] z-10 -mx-6 mb-6 flex justify-end gap-2 bg-white px-5 py-3 dark:bg-[#0e1726] lg:mx-0 lg:px-0">
                        <button
                            v-for="option in PERIOD_OPTIONS"
                            :key="option"
                            type="button"
                            class="btn btn-sm"
                            :class="period === option ? 'btn-primary' : 'btn-outline-primary'"
                            :disabled="isLoading"
                            @click="onChangePeriod(option)"
                        >
                            {{ option }} días
                        </button>
                    </div>

                    <PatientMetricsCards :summary="props.summary" />

                    <div class="mb-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <StreakCard :days="props.summary.streak_days" />
                        <StatusDistributionBars :distribution="props.summary.status_distribution" class="sm:col-span-2" />
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                        <div class="panel">
                            <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Tendencia reciente</h5>
                            <TrendChart :logs="props.summary.recent_logs" />
                        </div>
                        <RecentReadingsLog :logs="props.summary.recent_logs" :patient-id="props.summary.patient.id" />
                    </div>
                </template>

                <div v-else class="panel">
                    <p class="text-white-dark">Todavía no tienes un registro de paciente asociado.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
