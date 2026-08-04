<script lang="ts" setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PatientSelectStep from '@/components/glucose-logs/steps/PatientSelectStep.vue';
import OcrCaptureStep from '@/components/glucose-logs/steps/OcrCaptureStep.vue';
import ConfirmStep from '@/components/glucose-logs/steps/ConfirmStep.vue';
import SuccessStep from '@/components/glucose-logs/steps/SuccessStep.vue';
import StepsIndicator from '@/components/glucose-logs/StepsIndicator.vue';
import type { GlucoseLog, User } from '@/types';

const STEPS = [
    { id: 'patient-select', label: 'Paciente' },
    { id: 'ocr-capture',    label: 'Captura' },
    { id: 'confirm',        label: 'Confirmación' },
    { id: 'success',        label: 'Listo' },
];

const props = defineProps<{
    patients: User[];
}>();

type Step = 'patient-select' | 'ocr-capture' | 'confirm' | 'success';

const step = ref<Step>('patient-select');
const selectedPatient = ref<User | null>(null);
const extractedValue = ref<number | null>(null);
const createdLog = ref<GlucoseLog | null>(null);
const isSubmitting = ref(false);
const storeError = ref('');

function onPatientSelected(patient: User) {
    selectedPatient.value = patient;
    step.value = 'ocr-capture';
}

function onExtracted(value: number | null) {
    extractedValue.value = value;
    step.value = 'confirm';
}

function onBack() {
    step.value = 'ocr-capture';
    storeError.value = '';
}

async function onConfirm(value: number) {
    if (!selectedPatient.value?.patient?.id) return;

    isSubmitting.value = true;
    storeError.value = '';

    const csrfToken = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content ?? '';

    try {
        const response = await fetch('/glucose-logs', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                user_patient_id: selectedPatient.value.patient.id,
                value,
            }),
        });

        if (!response.ok) {
            const body = await response.json();
            const errors: Record<string, string[]> = body.errors ?? {};
            storeError.value = Object.values(errors).flat().join(' ') || body.message || 'Error al registrar la lectura.';
            return;
        }

        const body = await response.json();
        createdLog.value = body.data as GlucoseLog;
        step.value = 'success';
    } catch {
        storeError.value = 'Error de conexión. Intenta nuevamente.';
    } finally {
        isSubmitting.value = false;
    }
}

function onRestart() {
    step.value = 'patient-select';
    selectedPatient.value = null;
    extractedValue.value = null;
    createdLog.value = null;
    storeError.value = '';
}
</script>

<template>
    <Head title="Tomar Muestra" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse mb-6">
                <li><span>Tomar Muestra</span></li>
            </ul>

            <div class="panel p-5 mb-3">
                <StepsIndicator :steps="STEPS" :current-step="step" />
            </div>

            <div class="panel p-6 sm:p-8">
                <PatientSelectStep
                    v-if="step === 'patient-select'"
                    :patients="patients"
                    @select="onPatientSelected"
                />

                <OcrCaptureStep
                    v-else-if="step === 'ocr-capture'"
                    :patient="selectedPatient!"
                    @extracted="onExtracted"
                />

                <ConfirmStep
                    v-else-if="step === 'confirm'"
                    :patient="selectedPatient!"
                    :extracted-value="extractedValue"
                    :is-submitting="isSubmitting"
                    :error-message="storeError"
                    @confirm="onConfirm"
                    @back="onBack"
                />

                <SuccessStep
                    v-else-if="step === 'success' && createdLog"
                    :log="createdLog"
                    @restart="onRestart"
                />
            </div>
        </div>
    </AppLayout>
</template>
