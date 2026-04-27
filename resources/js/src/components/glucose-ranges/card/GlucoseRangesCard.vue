<script lang="ts" setup>
import { 
    ChevronDown,
    ChevronUp,
    Sun,
    Moon
} from 'lucide-vue-next';
import type { GlucoseRange } from "@/types";

const props = defineProps<{
    range: GlucoseRange;
    classNames: string;
}>();
</script>

<template>
    <div 
        :class="props.classNames"
    >
        <div class="w-full rounded-md bg-white px-4 py-2.5 shadow dark:bg-[#060818]">
            <span class="mb-4 flex items-center justify-between dark:text-white">
                Ayuno
                <Sun
                    :size="20"
                    :color="'#D99A3D'"
                />
            </span>
            <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] mb-3 gap-2">
                {{ props.range.min_fasting_value }} mg/dL
                <ChevronDown
                    :size="20"
                />
            </div>
            <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] gap-2">
                {{ props.range.max_fasting_value }} mg/dL
                <ChevronUp
                    :size="20"
                />
            </div>
        </div>
        <div class="w-full rounded-md bg-white px-4 py-2.5 shadow dark:bg-[#060818]">
            <span class="mb-4 flex items-center justify-between dark:text-white">
                Última comida
                <Moon
                    :size="20"
                    :color="'#4260EC'"
                />
            </span>
            <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] mb-3 gap-2">
                {{ props.range.min_non_fasting_value }} mg/dL
                <ChevronDown
                    :size="20"
                />
            </div>
            <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] gap-2">
                {{ props.range.max_non_fasting_value }} mg/dL
                <ChevronUp
                    :size="20"
                />
            </div>
        </div>
    </div>
</template>