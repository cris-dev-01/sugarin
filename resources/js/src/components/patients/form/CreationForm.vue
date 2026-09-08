<script setup lang="ts">
import { computed, ref } from "vue";
import useVuelidate from "@vuelidate/core";
import { useForm } from "@inertiajs/vue3";
import { email, helpers, maxLength, minValue, numeric, required } from "@vuelidate/validators";
import { 
    Calendar,
    UserRoundCheck,
    Droplet,
    Mail, 
    IdCard,
    Info,
    LoaderCircle,
    UserRound
} from 'lucide-vue-next';
import flatPickr from 'vue-flatpickr-component';
import 'flatpickr/dist/flatpickr.css';
import Multiselect from '@suadelabs/vue3-multiselect';
import '@suadelabs/vue3-multiselect/dist/vue3-multiselect.css';
import {
    formatRut,
    isRut,
} from "@rut-toolkit/core";
import GlucoseRangesCard from '@/components/glucose-ranges/card/GlucoseRangesCard.vue';
import type { GlucoseRange } from "@/types";

const props = defineProps<{
    glucoseRanges: GlucoseRange[];
}>();

const selectedGlucoseRange = ref<GlucoseRange | null>(null);

const form = useForm({
    glucose_range_id: 0,
    name: '',
    email: '',
    document: '',
    illness_found_at: '',
    initial_max_glucose_value: null,
});
const rules = computed(() => ({
    glucose_range_id: {
        required: helpers.withMessage('El valor mínimo en ayuno es requerido.', required),
        minValue: helpers.withMessage('Debes seleccionar un rango de glucosa.', minValue(1)),
    },
    name: {
        required: helpers.withMessage('Los nombres son requeridos.', required),
        maxLength: helpers.withMessage('El campo no debe superar 255 caracteres.', maxLength(255)),
    },
    email: {
        required: helpers.withMessage('El correo electrónico es requerido.', required),
        email: helpers.withMessage('El correo electrónico no es válido.', email),
        maxLength: helpers.withMessage('El campo no debe superar 255 caracteres.', maxLength(255)),
    },
    document: {
        required: helpers.withMessage('El rut es requerido.', required),
        maxLength: helpers.withMessage('El campo no debe superar 12 caracteres.', maxLength(12)),
    },
    illness_found_at: {
        required: helpers.withMessage('La fecha de debut es requerida.', required),
    },
    initial_max_glucose_value: {
        required: helpers.withMessage('El valor de glucosa debut es requerido.', required),
        numeric: helpers.withMessage('El valor de glucosa debut debe ser válido.', numeric),
    },
}));
const v$ = useVuelidate(rules, form);

const emit = defineEmits<{
    (e: "showNotification", message: string, type: string): void;
    (e: "toggleSlideover", value: boolean): void;
}>();

const setSelectedGlucoseRange = (range: GlucoseRange) => {
    v$.value.glucose_range_id.$model = range.id;
    selectedGlucoseRange.value = range;
};

const formatDocument = (event: FocusEvent) => {
    const target = event.target as HTMLInputElement;
    form.document = formatRut(target.value, { withDots: true, withHyphen: true });
    checkDocument();
};

const checkDocument = async () => {
    if (form.document === '' || form.document.length > 12) {
        return;
    }

    const isValid = isRut(form.document);
    if (!isValid) {
        form.setError('document', 'El RUT ingresado no es válido.');
        return;
    }

    form.clearErrors('document');
    await isRegistered(form.document);
};

const isRegistered = async (document: string) => {
    try {
        const csrfToken = (window.document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content;

        const response = await fetch(`/patients/${encodeURIComponent(document)}`, {
            headers: {
                'Accept': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
        });

        if (response.ok) {
            const data = await response.json();
            if (data.response) {
                form.setError('document', 'Ya existe un paciente registrado con este RUT.');
            }
        }
    } catch {}
};

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();

        form.post(`/patients/`, {
            onSuccess: () => {
                form.reset();
                emit(
                    "showNotification",
                    "El paciente fue registrado correctamente.",
                    "success",
                );
                emit("toggleSlideover", false);
            },
            onError: (error: any) => {
                console.log(error)
                emit(
                    "showNotification",
                    "No fue posible registrar el paciente, verifica los datos.",
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
        id="create-patient-form"
    >
        <div>
            <label class="mb-4 text-base dark:text-white leading-none">
                Información del paciente
                <span class="text-danger">*</span>
            </label>
            <div class="flex mb-1">
                <div class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <UserRound
                        :size="20"
                        :class="'dark:text-white'"
                    />
                </div>
                <input 
                    id="name" 
                    name="name" 
                    type="text" 
                    placeholder="Nombres"
                    v-model="v$.name.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.name.$invalid && v$.name.$dirty) || (form.errors as any).name
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.name.$invalid && v$.name.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.name.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).name">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).name }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <Mail
                        :size="20"
                        :class="'dark:text-white'"
                    />
                </div>
                <input 
                    id="email" 
                    name="email" 
                    type="email" 
                    placeholder="Correo electrónico"
                    v-model="v$.email.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.email.$invalid && v$.email.$dirty) || (form.errors as any).email
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.email.$invalid && v$.email.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.email.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).email">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).email }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <IdCard
                        :size="20"
                        :class="'dark:text-white'"
                    />
                </div>
                <input 
                    id="document" 
                    name="document" 
                    type="text" 
                    placeholder="RUT"
                    v-model="v$.document.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.document.$invalid && v$.document.$dirty) || (form.errors as any).document
                    }"
                    @blur="formatDocument($event)"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.document.$invalid && v$.document.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.document.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).document">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).document }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <Calendar
                        :size="20"
                        :class="'dark:text-white'"
                    />
                </div>
                <flat-pickr 
                    v-model="v$.illness_found_at.$model" 
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.illness_found_at.$invalid && v$.illness_found_at.$dirty) || (form.errors as any).illness_found_at
                    }"
                    :config="{
                        dateFormat: 'Y-m-d',
                    }"
                ></flat-pickr>
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.illness_found_at.$invalid && v$.illness_found_at.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.illness_found_at.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).illness_found_at">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).illness_found_at }}
                </span>
            </small>

            <div class="flex mt-3 mb-1">
                <div class="flex justify-center items-center text-gray-700 ltr:rounded-l-md rtl:rounded-r-md px-3 font-semibold border ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <Droplet
                        :size="20"
                        :class="'dark:text-white'"
                    />
                </div>
                <input 
                    id="initial_max_glucose_value" 
                    name="initial_max_glucose_value" 
                    type="number" 
                    placeholder="Valor de glucosa debut"
                    v-model="v$.initial_max_glucose_value.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.initial_max_glucose_value.$invalid && v$.initial_max_glucose_value.$dirty) || (form.errors as any).initial_max_glucose_value
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.initial_max_glucose_value.$invalid && v$.initial_max_glucose_value.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.initial_max_glucose_value.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).initial_max_glucose_value">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).initial_max_glucose_value }}
                </span>
            </small>
        </div>
       

        <div class="mt-6">
            <label class="mb-4 text-base dark:text-white leading-none">
                Establecer rango de glucosa
                <span class="text-danger">*</span>
            </label>

            <div class="flex mb-1">
                <multiselect
                    v-model="selectedGlucoseRange"
                    :options="props.glucoseRanges"
                    label="alias"
                    track-by="id"
                    class="custom-multiselect dark:text-white"
                    :searchable="true"
                    placeholder="Selecciona un rango"
                    selected-label=""
                    select-label=""
                    deselect-label=""
                    @select="(option: GlucoseRange) => setSelectedGlucoseRange(option)"
                    @remove="() => { v$.glucose_range_id.$model = 0; selectedGlucoseRange = null; }"
                ></multiselect>
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.glucose_range_id.$invalid && v$.glucose_range_id.$dirty">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ v$.glucose_range_id.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).glucose_range_id">
                    <Info
                        icon="AlertCircle"
                        class="mr-2 h-5 w-5 stroke-[1.5]"
                    />
                    {{ (form.errors as any).glucose_range_id }}
                </span>
            </small>
        </div>

        <div 
            v-if="selectedGlucoseRange"
            class="mt-4"
        >
            <GlucoseRangesCard 
                :range="selectedGlucoseRange"
                :classNames="'flex flex-col gap-4'"
            />
        </div>

        
        <div class="flex justify-end border-t dark:border-gray-700 mt-7 pt-4">
            <button 
                type="submit" 
                class="btn btn-primary"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
                >
                <UserRoundCheck
                    v-if="!form.processing"
                    class="mr-1"
                    :size="16"
                />
                <LoaderCircle
                    v-else
                    class="animate-spin"
                    :size="16"
                />
                Registrar paciente
            </button>
        </div>
    </form>
</template>