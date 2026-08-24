<script setup lang="ts">
import { computed } from 'vue';
import useVuelidate from '@vuelidate/core';
import { useForm } from '@inertiajs/vue3';
import { required, maxValue, minValue, helpers } from '@vuelidate/validators';
import { CalendarClock, CheckCheck, Gauge, Info, ListChecks, LoaderCircle } from 'lucide-vue-next';
import { useZiggyRoute } from '@/composables/useRoute';
import { usePermissions } from '@/composables/usePermissions';
import type { DashboardSettings } from '@/types';

const props = defineProps<{
    settings: DashboardSettings;
}>();

const emit = defineEmits<{
    (e: 'showNotification', message: string, type: string): void;
}>();

const route = useZiggyRoute();
const { checkPermission } = usePermissions();
const canUpdate = computed(() => checkPermission('update-dashboard-settings'));

const form = useForm({
    recent_event_window_hours: props.settings.recent_event_window_hours,
    good_control_threshold: props.settings.good_control_threshold,
    good_control_reference_period_days: props.settings.good_control_reference_period_days,
    expected_logs_per_day: props.settings.expected_logs_per_day,
});

const rules = computed(() => ({
    recent_event_window_hours: {
        required: helpers.withMessage('La ventana de evento reciente es requerida.', required),
        minValue: helpers.withMessage('El valor debe ser desde 1 hora.', minValue(1)),
        maxValue: helpers.withMessage('El valor no debe superar 168 horas.', maxValue(168)),
    },
    good_control_threshold: {
        required: helpers.withMessage('El umbral de buen control es requerido.', required),
        minValue: helpers.withMessage('El valor debe ser desde 0.', minValue(0)),
        maxValue: helpers.withMessage('El valor no debe superar 1.', maxValue(1)),
    },
    good_control_reference_period_days: {
        required: helpers.withMessage('El período de referencia es requerido.', required),
        minValue: helpers.withMessage('El valor debe ser desde 1 día.', minValue(1)),
        maxValue: helpers.withMessage('El valor no debe superar 365 días.', maxValue(365)),
    },
    expected_logs_per_day: {
        required: helpers.withMessage('Las lecturas esperadas por día son requeridas.', required),
        minValue: helpers.withMessage('El valor debe ser desde 1.', minValue(1)),
        maxValue: helpers.withMessage('El valor no debe superar 24.', maxValue(24)),
    },
}));
const v$ = useVuelidate(rules, form);

const handleForm = async () => {
    await v$.value.$validate();

    if (!v$.value.$invalid) {
        v$.value.$reset();

        form.put(route('dashboard-settings.update'), {
            onSuccess: () => {
                emit('showNotification', 'Los parámetros del dashboard fueron actualizados correctamente.', 'success');
            },
            onError: () => {
                emit('showNotification', 'No fue posible actualizar los parámetros, verifica los datos.', 'error');
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="handleForm">
        <div>
            <label class="mb-1 text-base dark:text-white leading-none">
                Ventana de evento reciente (horas)
                <span class="text-danger">*</span>
            </label>
            <p class="text-white-dark text-sm mb-2">
                Horas hacia atrás para considerar un evento "Bajo" o una inactividad como recientes.
            </p>
            <div class="flex mb-1">
                <div class="flex justify-center items-center px-3 font-semibold border ltr:rounded-l-md rtl:rounded-r-md ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <CalendarClock :size="20" class="dark:text-white" />
                </div>
                <input
                    id="recent_event_window_hours"
                    name="recent_event_window_hours"
                    type="number"
                    min="1"
                    max="168"
                    v-model="v$.recent_event_window_hours.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.recent_event_window_hours.$invalid && v$.recent_event_window_hours.$dirty) || (form.errors as any).recent_event_window_hours
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.recent_event_window_hours.$invalid && v$.recent_event_window_hours.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.recent_event_window_hours.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).recent_event_window_hours">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).recent_event_window_hours }}
                </span>
            </small>
        </div>

        <div class="mt-5">
            <label class="mb-1 text-base dark:text-white leading-none">
                Umbral de buen control
                <span class="text-danger">*</span>
            </label>
            <p class="text-white-dark text-sm mb-2">
                Proporción (entre 0 y 1) de lecturas en rango normal para clasificar a un paciente en buen control. Ej: 0.8 equivale a 80%.
            </p>
            <div class="flex mb-1">
                <div class="flex justify-center items-center px-3 font-semibold border ltr:rounded-l-md rtl:rounded-r-md ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <Gauge :size="20" class="dark:text-white" />
                </div>
                <input
                    id="good_control_threshold"
                    name="good_control_threshold"
                    type="number"
                    min="0"
                    max="1"
                    step="0.01"
                    v-model="v$.good_control_threshold.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.good_control_threshold.$invalid && v$.good_control_threshold.$dirty) || (form.errors as any).good_control_threshold
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.good_control_threshold.$invalid && v$.good_control_threshold.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.good_control_threshold.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).good_control_threshold">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).good_control_threshold }}
                </span>
            </small>
        </div>

        <div class="mt-5">
            <label class="mb-1 text-base dark:text-white leading-none">
                Período de referencia para buen control (días)
                <span class="text-danger">*</span>
            </label>
            <p class="text-white-dark text-sm mb-2">
                Cantidad de días hacia atrás usados para calcular el porcentaje de buen control.
            </p>
            <div class="flex mb-1">
                <div class="flex justify-center items-center px-3 font-semibold border ltr:rounded-l-md rtl:rounded-r-md ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <ListChecks :size="20" class="dark:text-white" />
                </div>
                <input
                    id="good_control_reference_period_days"
                    name="good_control_reference_period_days"
                    type="number"
                    min="1"
                    max="365"
                    v-model="v$.good_control_reference_period_days.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.good_control_reference_period_days.$invalid && v$.good_control_reference_period_days.$dirty) || (form.errors as any).good_control_reference_period_days
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.good_control_reference_period_days.$invalid && v$.good_control_reference_period_days.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.good_control_reference_period_days.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).good_control_reference_period_days">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).good_control_reference_period_days }}
                </span>
            </small>
        </div>

        <div class="mt-5">
            <label class="mb-1 text-base dark:text-white leading-none">
                Lecturas esperadas por día
                <span class="text-danger">*</span>
            </label>
            <p class="text-white-dark text-sm mb-2">
                Cantidad de registros esperados por día de un paciente, usada para calcular su adherencia.
            </p>
            <div class="flex mb-1">
                <div class="flex justify-center items-center px-3 font-semibold border ltr:rounded-l-md rtl:rounded-r-md ltr:border-r-0 rtl:border-l-0 border-[#e0e6ed] dark:border-[#17263c] dark:bg-[#1b2e4b]">
                    <CheckCheck :size="20" class="dark:text-white" />
                </div>
                <input
                    id="expected_logs_per_day"
                    name="expected_logs_per_day"
                    type="number"
                    min="1"
                    max="24"
                    v-model="v$.expected_logs_per_day.$model"
                    class="form-input ltr:rounded-l-none rtl:rounded-r-none dark:text-white"
                    :class="{
                        'border-danger': (v$.expected_logs_per_day.$invalid && v$.expected_logs_per_day.$dirty) || (form.errors as any).expected_logs_per_day
                    }"
                />
            </div>
            <small class="text-danger">
                <span class="flex font-semibold" v-if="v$.expected_logs_per_day.$invalid && v$.expected_logs_per_day.$dirty">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ v$.expected_logs_per_day.$errors[0]?.$message }}
                </span>
                <span class="flex font-semibold" v-else-if="(form.errors as any).expected_logs_per_day">
                    <Info class="mr-2 h-5 w-5 stroke-[1.5]" />
                    {{ (form.errors as any).expected_logs_per_day }}
                </span>
            </small>
        </div>

        <div class="flex justify-end border-t dark:border-gray-700 mt-7 pt-4">
            <button
                type="submit"
                class="btn btn-primary"
                :class="{ 'opacity-25': form.processing || !canUpdate }"
                :disabled="form.processing || !canUpdate"
                :title="!canUpdate ? 'No tienes permiso para editar estos parámetros.' : undefined"
            >
                <CheckCheck v-if="!form.processing" class="mr-1" :size="16" />
                <LoaderCircle v-else class="animate-spin" :size="16" />
                Guardar cambios
            </button>
        </div>
    </form>
</template>
