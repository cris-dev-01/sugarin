<script lang="ts" setup>
import { onMounted } from 'vue';
import PatientCard from '@/components/patients/card/PatientCard.vue';
import type { User } from '@/types';

const props = defineProps<{
    patients: User[];
}>();

const emit = defineEmits<{
    (e: 'select', patient: User): void;
}>();

onMounted(() => {
    if (props.patients.length === 1) {
        emit('select', props.patients[0]);
    }
});
</script>

<template>
    <div class="max-w-lg mx-auto">
        <h2 class="text-xl font-semibold mb-6 dark:text-white-light">Seleccionar paciente</h2>

        <div class="space-y-3">
            <button
                v-for="patient in patients"
                :key="patient.id"
                type="button"
                class="w-full flex items-center p-4 rounded-lg border border-gray-200 dark:border-[#17263c] bg-white dark:bg-[#1b2e4b] hover:border-primary hover:shadow-sm transition-all text-left"
                @click="emit('select', patient)"
            >
                <PatientCard :name="patient.name" :email="patient.email" :document="patient.patient.formatted_document" />
            </button>
        </div>
    </div>
</template>
