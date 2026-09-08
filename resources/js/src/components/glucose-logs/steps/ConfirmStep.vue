<script lang="ts" setup>
import { ref, watch } from 'vue';
import {
    ChevronLeft,
    CircleCheckBig,
} from 'lucide-vue-next';
import PatientCard from '@/components/patients/card/PatientCard.vue';
import type { User } from '@/types';


const props = defineProps<{
    patient: User;
    extractedValue: number | null;
    isSubmitting: boolean;
    errorMessage?: string;
}>();

const emit = defineEmits<{
    (e: 'confirm', value: number): void;
    (e: 'back'): void;
}>();

const inputValue = ref<string>(props.extractedValue !== null ? String(props.extractedValue) : '');
const validationError = ref('');

watch(() => props.extractedValue, (val) => {
    inputValue.value = val !== null ? String(val) : '';
});

function submit() {
    validationError.value = '';
    const parsed = parseInt(inputValue.value, 10);
    if (!inputValue.value || isNaN(parsed) || parsed < 1) {
        validationError.value = 'Ingresa un valor numérico positivo.';
        return;
    }
    emit('confirm', parsed);
}
</script>

<template>
    <div class="max-w-lg mx-auto">
        <h2 class="text-xl font-semibold mb-4 dark:text-white-light">Confirmar lectura</h2>
        <div class="mb-6">
            <PatientCard :name="patient.name" :email="patient.email" :document="patient.patient.formatted_document" />
        </div>

        <div class="panel p-6">
            <label class="block mb-2 text-sm font-medium dark:text-white-light">
                Valor de glucosa (mg/dL)
            </label>
            <input
                v-model="inputValue"
                type="number"
                min="1"
                class="form-input text-2xl text-center font-bold tracking-widest"
                placeholder="---"
                :disabled="isSubmitting"
            />
            <p v-if="validationError" class="mt-2 text-sm text-danger">{{ validationError }}</p>
            <p v-if="errorMessage" class="mt-2 text-sm text-danger">{{ errorMessage }}</p>
        </div>

        <div class="flex gap-3 mt-6">
            <button
                type="button"
                class="btn btn-outline-dark flex-1 flex gap-2"
                :disabled="isSubmitting"
                @click="emit('back')"
            >
                <ChevronLeft
                    :size="18"
                />
                Volver
            </button>
            <button
                type="button"
                class="btn btn-primary flex-1 flex gap-2"
                :disabled="isSubmitting"
                @click="submit"
            >
                <CircleCheckBig
                    :size="16"
                />
                <span v-if="isSubmitting">Guardando…</span>
                <span v-else>Registrar lectura</span>
            </button>
        </div>
    </div>
</template>
