<script lang="ts" setup>
import { 
    LoaderCircle,
    TrashIcon
} from 'lucide-vue-next';
import { useForm } from "@inertiajs/vue3";
import GlucoseRangesCard from '@/components/glucose-ranges/card/GlucoseRangesCard.vue';
import type { GlucoseRange } from "@/types";

const props = defineProps<{
    range: GlucoseRange;
}>();

const form = useForm({
    _method: "DELETE",
});

const emit = defineEmits<{
    (e: "showNotification", message: string, type: string): void;
    (e: "removeGlucoseRanges", range: GlucoseRange): void;
    (e: "toggleDeleteModal", range: null): void;
}>();

const closeModal = () => {
    emit('toggleDeleteModal', null);
};

const handleForm = async () => {
    form.delete(`/glucose-ranges/${props.range.id}`, {
        onSuccess: (data: any) => {
            emit(
                "showNotification",
                "Los rangos fueron eliminados correctamente.",
                "success",
            );
            emit("removeGlucoseRanges", data.props.flash.response);
            emit("toggleDeleteModal", null);
        },
        onError: (error) => {
            emit(
                "showNotification",
                "No fue posible eliminar este rango, inténtalo nuevamente.",
                "error",
            );
        },
    });
};
</script>

<template>
    <form 
        @submit.prevent="handleForm" 
        id="delete-glucose-ranges-form"
    >
        <div class="dark:text-white text-center mb-4">
            <p>
                Estás a punto de eliminar este rango de glucosa. Esta acción no se puede deshacer. ¿Deseas continuar?
            </p>
        </div>
        <GlucoseRangesCard 
            :range="props.range"
        />
        <div class="flex justify-end items-center border-t dark:border-gray-700 dark:text-white mt-7 pt-4">
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
                <TrashIcon
                    v-if="!form.processing"
                    :size="16"
                />
                <LoaderCircle
                    v-else
                    class="animate-spin"
                    :size="16"
                />
                Sí, eliminar
            </button>
        </div>
    </form>
</template>