<script lang="ts" setup>
import { 
    XIcon
} from 'lucide-vue-next';
import EditionForm from '@/components/glucose-ranges/form/EditionForm.vue';
import type { GlucoseRange } from "@/types";

const props = defineProps<{
    isOpen: boolean;
    range: GlucoseRange | null;
}>();

const emit = defineEmits<{
    (e: "toggleEditionSlideover", range: GlucoseRange | null): void;
    (e: "updateGlucoseRanges", range: GlucoseRange): void;
    (e: "showNotification", message: string, type: string): void;
}>();

const toggleSlideover = () => {
    emit('toggleEditionSlideover', null);
};

const updateGlucoseRanges = (range: GlucoseRange) => {
    emit("updateGlucoseRanges", range);
};

const showNotification = (message: string, type: string) => {
    emit("showNotification", message, type);
};
</script>

<template>
    <div>
        <div
            class="fixed inset-0 bg-[black]/60 z-[51] px-4 hidden transition-[display]"
            :class="{ '!block': props.isOpen }"
            @click="toggleSlideover"
        ></div>

        <nav
            class="bg-white fixed ltr:-right-[400px] rtl:-left-[400px] top-0 bottom-0 w-full max-w-[400px] shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] transition-[right] duration-300 z-[51] dark:bg-[#0e1726] p-4"
            :class="{ 'ltr:!right-0 rtl:!left-0': props.isOpen }"
        >
            <perfect-scrollbar
                :options="{
                    swipeEasing: true,
                    wheelPropagation: false,
                }"
                class="relative h-full overflow-x-hidden ltr:pr-3 rtl:pl-3 ltr:-mr-3 rtl:-ml-3"
            >
                <div>
                    <div class="text-start relative pb-5">
                        <a
                            href="javascript:;"
                            class="absolute top-0 ltr:right-0 rtl:left-0 opacity-30 hover:opacity-100 dark:text-white"
                            @click="toggleSlideover"
                        >
                            <XIcon
                                :size="22"
                            />
                        </a>
                        <h4 class="mb-1 dark:text-white">Edición de rango de glucosa</h4>
                        <p class="text-white-dark">Rango para ayunas y normal.</p>
                    </div>
                    <div class="rounded-md mb-3 p-3">
                        <EditionForm
                            v-if="props.range"
                            :range="props.range"
                            @showNotification="showNotification"
                            @updateGlucoseRanges="updateGlucoseRanges"
                            @toggleSlideover="toggleSlideover"
                        />
                    </div>
                </div>
            </perfect-scrollbar>
        </nav>
    </div>
</template>
