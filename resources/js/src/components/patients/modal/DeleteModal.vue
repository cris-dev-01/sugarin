<script lang="ts" setup>
import { TransitionRoot, TransitionChild, Dialog, DialogPanel, DialogOverlay } from '@headlessui/vue';
import { LoaderCircle, ShieldAlertIcon, TrashIcon } from 'lucide-vue-next';
import { useForm } from "@inertiajs/vue3";
import type { User } from "@/types";

const props = defineProps<{
    show: boolean;
    patient: User;
}>();

const emit = defineEmits<{
    (e: "toggleDeleteModal", patient: null): void;
    (e: "removePatients", patient: User): void;
    (e: "showNotification", message: string, type: string): void;
}>();

const form = useForm({
    _method: "DELETE",
});

const closeModal = () => {
    emit('toggleDeleteModal', null);
};

const handleForm = () => {
    form.delete(`/patients/${props.patient.patient.id}`, {
        onSuccess: () => {
            emit("showNotification", "El paciente fue eliminado correctamente.", "success");
            emit("removePatients", props.patient);
            emit("toggleDeleteModal", null);
        },
        onError: () => {
            emit("showNotification", "No fue posible eliminar este paciente, inténtalo nuevamente.", "error");
        },
    });
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
                                    <ShieldAlertIcon :size="36" class="text-red-600" />
                                </div>
                            </div>
                            <div class="p-5">
                                <div class="dark:text-white text-center mb-4">
                                    <p class="font-semibold text-lg mb-1">{{ props.patient.name }}</p>
                                    <p>
                                        Estás a punto de eliminar este paciente. Esta acción no se puede deshacer. ¿Deseas continuar?
                                    </p>
                                </div>
                                <div class="flex justify-end items-center border-t dark:border-gray-700 dark:text-white mt-4 pt-4">
                                    <button
                                        type="button"
                                        class="btn btn-outline-dark dark:btn-dark"
                                        :disabled="form.processing"
                                        @click="closeModal"
                                    >No, volver</button>
                                    <button
                                        type="button"
                                        class="btn btn-danger ltr:ml-4 rtl:mr-4"
                                        :class="{ 'opacity-25': form.processing }"
                                        :disabled="form.processing"
                                        @click="handleForm"
                                    >
                                        <TrashIcon v-if="!form.processing" :size="16" />
                                        <LoaderCircle v-else class="animate-spin" :size="16" />
                                        Sí, eliminar
                                    </button>
                                </div>
                            </div>
                        </DialogPanel>
                    </TransitionChild>
                </div>
            </div>
        </Dialog>
    </TransitionRoot>
</template>
