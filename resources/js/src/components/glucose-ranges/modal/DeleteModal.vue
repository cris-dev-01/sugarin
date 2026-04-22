<script lang="ts" setup>
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay } from '@headlessui/vue';
import { 
    ShieldAlertIcon
} from 'lucide-vue-next';
import DeleteForm from '@/components/glucose-ranges/form/DeleteForm.vue';
import type { GlucoseRange } from "@/types";

const props = defineProps<{
    show: boolean;
    range: GlucoseRange;
}>();

const emit = defineEmits<{
    (e: "toggleDeleteModal", range: null): void;
    (e: "removeGlucoseRanges", range: GlucoseRange): void;
    (e: "showNotification", message: string, type: string): void;
}>();

const closeModal = () => {
    emit('toggleDeleteModal', null);
};

const removeGlucoseRanges = (range: GlucoseRange) => {
    emit("removeGlucoseRanges", range);
};

const showNotification = (message: string, type: string) => {
    emit("showNotification", message, type);
};
</script>

<template>
    <TransitionRoot appear :show="props.show" as="template">
        <Dialog as="div" @close="closeModal" class="relative z-50">
            <TransitionChild
                as="template"
                enter="duration-300 ease-out"
                enter-from="opacity-0"
                enter-to="opacity-100"
                leave="duration-200 ease-in"
                leave-from="opacity-100"
                leave-to="opacity-0"
            >
                <DialogOverlay class="fixed inset-0 bg-[black]/60" />
            </TransitionChild>

            <div class="fixed inset-0 overflow-y-auto">
                <div class="flex min-h-full items-start justify-center px-4 py-8">
                    <TransitionChild
                        as="template"
                        enter="duration-300 ease-out"
                        enter-from="opacity-0 scale-95"
                        enter-to="opacity-100 scale-100"
                        leave="duration-200 ease-in"
                        leave-from="opacity-100 scale-100"
                        leave-to="opacity-0 scale-95"
                    >
                        <DialogPanel class="panel border-0 p-0 rounded-lg overflow-hidden w-full max-w-lg text-black dark:text-white-dark">
                            <div class="flex items-center justify-center dark:text-white-dark/70 text-base font-medium text-[#1f2937] p-5">
                                <div class="flex items-center justify-center w-16 h-16 rounded-full bg-red-600/20 dark:bg-white/10">
                                    <ShieldAlertIcon
                                        :size="36"
                                        class="text-red-600"
                                    />
                                </div>
                            </div>
                            <div class="p-5">
                                <DeleteForm 
                                    :range="props.range"
                                    @showNotification="showNotification"
                                    @removeGlucoseRanges="removeGlucoseRanges"
                                    @toggleDeleteModal="closeModal"
                                />
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>