<script lang="ts" setup>
import { onMounted } from 'vue';
import { UserRound } from 'lucide-vue-next';
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
                class="w-full flex items-center gap-4 p-4 rounded-lg border border-gray-200 dark:border-[#17263c] bg-white dark:bg-[#1b2e4b] hover:border-primary hover:shadow-sm transition-all text-left"
                @click="emit('select', patient)"
            >
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center text-primary shrink-0">
                    <UserRound :size="20" />
                </div>
                <div>
                    <p class="font-semibold dark:text-white-light">{{ patient.name }}</p>
                    <p class="text-sm text-gray-500 dark:text-[#506690]">{{ patient.email }}</p>
                </div>
            </button>
        </div>
    </div>
</template>
