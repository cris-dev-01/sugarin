<script lang="ts" setup>
import { nextTick, onMounted, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import TriageCards from '@/components/dashboard/cards/TriageCards.vue';
import PatientRiskList from '@/components/dashboard/cards/PatientRiskList.vue';
import PatientMetricsCards from '@/components/dashboard/cards/PatientMetricsCards.vue';
import StreakCard from '@/components/dashboard/cards/StreakCard.vue';
import RecentReadingsLog from '@/components/dashboard/cards/RecentReadingsLog.vue';
import StatusDistributionBars from '@/components/dashboard/cards/StatusDistributionBars.vue';
import TrendChart from '@/components/dashboard/charts/TrendChart.vue';
import TriagePatientsModal from '@/components/dashboard/modal/TriagePatientsModal.vue';
import PatientCard from '@/components/patients/card/PatientCard.vue';
import type { TriageOverview, PatientSummary, SummaryPeriod, TriageCriteria } from '@/types';

const props = defineProps<{
    triage: TriageOverview;
    summary: PatientSummary | null;
}>();

const PERIOD_OPTIONS: SummaryPeriod[] = [7, 30, 90];

const summary = ref<PatientSummary | null>(props.summary);
const selectedPatientId = ref<number | null>(props.summary?.patient.id ?? null);
const period = ref<SummaryPeriod>(30);
const isLoading = ref(false);
const selectedCriteria = ref<TriageCriteria | null>(null);
const showTriageModal = ref(false);
const summaryHeaderRef = ref<HTMLElement | null>(null);

async function loadSummary(patientId: number, selectedPeriod: SummaryPeriod) {
    isLoading.value = true;

    try {
        const response = await fetch(`/patients/${patientId}/summary?period=${selectedPeriod}`, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const body = await response.json();
            summary.value = body.data as PatientSummary;
        }
    } finally {
        isLoading.value = false;
    }
}

function onSelectPatient(id: number) {
    selectedPatientId.value = id;
    loadSummary(id, period.value);
}

function onChangePeriod(newPeriod: SummaryPeriod) {
    period.value = newPeriod;

    if (selectedPatientId.value) {
        loadSummary(selectedPatientId.value, newPeriod);
    }
}

function onSelectCriteria(criteria: TriageCriteria) {
    selectedCriteria.value = criteria;
    showTriageModal.value = true;
}

function closeTriageModal() {
    showTriageModal.value = false;
}

onMounted(() => {
    const requestedPatientId = new URLSearchParams(window.location.search).get('patient');

    if (requestedPatientId && summary.value && String(summary.value.patient.id) === requestedPatientId) {
        nextTick(() => {
            summaryHeaderRef.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    }
});
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li><span>Dashboard</span></li>
            </ul>

            <div class="pt-5">
                <TriageCards :triage="props.triage" @select="onSelectCriteria" />

                <PatientRiskList :patients="props.triage.patients" :selected-id="selectedPatientId" @select="onSelectPatient" />

                <template v-if="summary">
                    <div
                        ref="summaryHeaderRef"
                        class="sticky shadow-sm top-[0px] z-10 -mx-6 mb-6 flex flex-wrap items-center justify-between gap-4 bg-white px-5 py-3 dark:bg-[#0e1726] md:mx-0 "
                    >
                        <PatientCard
                            :name="summary.patient.name"
                            :email="summary.patient.email"
                            :document="summary.patient.formatted_document"
                        />
                        <div class="flex gap-2">
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
                    </div>

                    <PatientMetricsCards :summary="summary" />

                    <div class="mb-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                        <StreakCard :days="summary.streak_days" />
                        <StatusDistributionBars :distribution="summary.status_distribution" class="sm:col-span-2" />
                    </div>

                    <div class="mb-6 grid grid-cols-1 gap-6 xl:grid-cols-2">
                        <div class="panel">
                            <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Tendencia reciente</h5>
                            <TrendChart :logs="summary.recent_logs" />
                        </div>
                        <RecentReadingsLog :logs="summary.recent_logs" :patient-id="summary.patient.id" />
                    </div>
                </template>

                <div v-else class="panel">
                    <p class="text-white-dark">No hay pacientes registrados todavía.</p>
                </div>
            </div>
        </div>

        <TriagePatientsModal :show="showTriageModal" :criteria="selectedCriteria" @close="closeTriageModal" />
    </AppLayout>
</template>
