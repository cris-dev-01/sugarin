<script setup lang="ts">
import { computed } from "vue";
import useVuelidate from "@vuelidate/core";
import { useForm } from "@inertiajs/vue3";
import { required, minValue, maxValue, helpers } from "@vuelidate/validators";
import { 
    Check,
    ChevronDown, 
    ChevronUp, 
    Info,
    LoaderCircle
} from 'lucide-vue-next';
import type { FormGlucoseRanges } from "@/types";

const form = useForm<FormGlucoseRanges>({
    min_fasting_value: null,
    max_fasting_value: null,
    min_non_fasting_value: null,
    max_non_fasting_value: null,
});
const rules = computed(() => ({
    min_fasting_value: {
        required: helpers.withMessage('El valor mínimo en ayuno es requerido', required),
        minValue: helpers.withMessage('El valor debe ser desde 60', minValue(60)),
        maxValue: helpers.withMessage('El valor no debe superar 500', maxValue(500)),
    },
    max_fasting_value: {
        required: helpers.withMessage('El valor máximo en ayuno es requerido', required),
        minValue: helpers.withMessage('El valor debe ser desde 60', minValue(60)),
        maxValue: helpers.withMessage('El valor no debe superar 500', maxValue(500)),
    },
    min_non_fasting_value: {
        required: helpers.withMessage('El valor mínimo no ayuno es requerido', required),
        minValue: helpers.withMessage('El valor debe ser desde 60', minValue(60)),
        maxValue: helpers.withMessage('El valor no debe superar 500', maxValue(500)),
    },
    max_non_fasting_value: {
        required: helpers.withMessage('El valor máximo no ayuno es requerido', required),
        minValue: helpers.withMessage('El valor debe ser desde 60', minValue(60)),
        maxValue: helpers.withMessage('El valor no debe superar 500', maxValue(500)),
    },
}));
const v$ = useVuelidate(rules, form);

const emit = defineEmits<{
    (e: "showNotification", message: string, type: string): void;
    (e: "toggleSlideover", value: boolean): void;
}>();

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();

        form.post(`/glucose-ranges/`, {
            onSuccess: (data: any) => {
                form.reset();
                emit(
                    "showNotification",
                    "Los rangos fueron creados correctamente.",
                    "success",
                );
                emit("toggleSlideover", false);
            },
            onError: (error) => {
                emit(
                    "showNotification",
                    "No fue posible crear este rango, verifica los datos.",
                    "error",
                );
            },
        });
    }
};
</script>

<template>
    <form 
        @submit.prevent="handleForm" 
        id="create-glucose-ranges-form"
    >
        <div>
            <label class="mb-4 text-base dark:text-white leading-none">
                Rangos en Ayuno
                <span class="text-danger">*</span>
            </label>
            <div class="flex mb-1">
                <div class="bg-warning flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <ChevronUp
                        :size="20"
                        :color="'#FFFFFF'"
                    />
                </div>
                <input 
                    id="min_fasting_value" 
                    name="min_fasting_value" 
                    type="number" 
                    placeholder="Valor mínimo"
                    v-model="v$.min_fasting_value.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                    :class="{
                        'border-danger': (v$.min_fasting_value.$invalid && v$.min_fasting_value.$dirty) || (form.errors as any).min_fasting_value
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.min_fasting_value.$invalid && v$.min_fasting_value.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.min_fasting_value.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).min_fasting_value">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).min_fasting_value }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="bg-warning flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <ChevronDown
                        :size="20"
                        :color="'#FFFFFF'"
                    />
                </div>
                <input 
                    id="max_fasting_value" 
                    name="max_fasting_value" 
                    type="number" 
                    placeholder="Valor máximo"
                    v-model="v$.max_fasting_value.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                    :class="{
                        'border-danger': (v$.max_fasting_value.$invalid && v$.max_fasting_value.$dirty) || (form.errors as any).max_fasting_value
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.max_fasting_value.$invalid && v$.max_fasting_value.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.max_fasting_value.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).max_fasting_value">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).max_fasting_value }}
                </span>
            </small>
        </div>
       

        <div class="mt-5">
            <label class="mb-4 text-base dark:text-white leading-none">
                Rangos Normales
                <span class="text-danger">*</span>
            </label>
            <div class="flex mb-1">
                <div class="bg-primary flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <ChevronUp
                        :size="20"
                        :color="'#FFFFFF'"
                    />
                </div>
                <input 
                    id="min_non_fasting_value" 
                    name="min_non_fasting_value" 
                    type="number" 
                    placeholder="Valor mínimo"
                    v-model="v$.min_non_fasting_value.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                    :class="{
                        'border-danger': (v$.min_non_fasting_value.$invalid && v$.min_non_fasting_value.$dirty) || (form.errors as any).min_non_fasting_value
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.min_non_fasting_value.$invalid && v$.min_non_fasting_value.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.min_non_fasting_value.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).min_non_fasting_value">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).min_non_fasting_value }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="bg-primary flex justify-center items-center ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <ChevronDown
                        :size="20"
                        :color="'#FFFFFF'"
                    />
                </div>
                <input 
                    id="max_non_fasting_value" 
                    name="max_non_fasting_value" 
                    type="number" 
                    placeholder="Valor máximo"
                    v-model="v$.max_non_fasting_value.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none"
                    :class="{
                        'border-danger': (v$.max_non_fasting_value.$invalid && v$.max_non_fasting_value.$dirty) || (form.errors as any).max_non_fasting_value
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.max_non_fasting_value.$invalid && v$.max_non_fasting_value.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.max_non_fasting_value.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).max_non_fasting_value">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).max_non_fasting_value }}
                </span>
            </small>
        </div>

        <div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="(form.errors as any).duplicate">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).duplicate }}
                </span>
            </small>
        </div>

        <div class="flex justify-end border-t dark:border-gray-700 mt-7 pt-4">
            <button 
                type="submit" 
                class="btn btn-primary"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                >
                <Check
                    v-if="!form.processing"
                    class="mr-1"
                    :size="16"
                />
                <LoaderCircle
                    v-else
                    class="animate-spin"
                    :size="16"
                />
                Crear rango
            </button>
        </div>
    </form>
</template>