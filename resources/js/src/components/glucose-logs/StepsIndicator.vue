<script lang="ts" setup>
import { computed } from 'vue';
import { Check } from 'lucide-vue-next';

interface StepDef {
    id: string;
    label: string;
}

const props = defineProps<{
    steps: StepDef[];
    currentStep: string;
}>();

const currentIndex = computed(() =>
    props.steps.findIndex(s => s.id === props.currentStep)
);

function getState(index: number): 'completed' | 'active' | 'pending' {
    if (index < currentIndex.value) return 'completed';
    if (index === currentIndex.value) {
        return index === props.steps.length - 1 ? 'completed' : 'active';
    }
    return 'pending';
}
</script>

<template>
    <div class="flex items-start w-full">
        <template v-for="(stepDef, index) in steps" :key="stepDef.id">
            <div class="flex flex-col items-center" :class="index === 0 || index === steps.length - 1 ? 'shrink-0' : 'flex-1'">
                <div
                    class="flex items-center justify-center w-9 h-9 rounded-full border-2 text-sm font-bold transition-colors duration-300"
                    :class="{
                        'bg-success border-success text-white': getState(index) === 'completed',
                        'bg-primary border-primary text-white': getState(index) === 'active',
                        'bg-white dark:bg-[#1b2e4b] border-gray-300 dark:border-[#506690] text-gray-400 dark:text-[#506690]': getState(index) === 'pending',
                    }"
                >
                    <Check v-if="getState(index) === 'completed'" :size="16" />
                    <span v-else>{{ index + 1 }}</span>
                </div>
                <span
                    class="mt-2 text-xs font-medium whitespace-nowrap transition-colors duration-300"
                    :class="{
                        'text-success': getState(index) === 'completed',
                        'text-primary': getState(index) === 'active',
                        'text-gray-400 dark:text-[#506690]': getState(index) === 'pending',
                    }"
                >
                    {{ stepDef.label }}
                </span>
            </div>

            <div
                v-if="index < steps.length - 1"
                class="flex-1 h-0.5 mt-4 mx-2 transition-colors duration-300"
                :class="{
                    'bg-success': getState(index) === 'completed',
                    'bg-gray-200 dark:bg-[#253b5c]': getState(index) !== 'completed',
                }"
            />
        </template>
    </div>
</template>
