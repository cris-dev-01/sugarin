<script lang="ts" setup>
import { computed } from 'vue';
import { CheckCircle, TriangleAlert } from 'lucide-vue-next';
import type { GlucoseLog } from '@/types';

const props = defineProps<{
    log: GlucoseLog;
}>();

const emit = defineEmits<{
    (e: 'restart'): void;
}>();

const statusColor: Record<string, string> = {
    'Rango normal': 'badge-outline-success',
    'Elevado - fuera de rango normal': 'badge-outline-danger',
    'Bajo - fuera de rango normal': 'badge-outline-warning',
};

const alertLevel = computed<'success' | 'danger' | 'warning'>(() => {
    if (!props.log.is_abnormal) return 'success';
    return props.log.status.name === 'Elevado - fuera de rango normal' ? 'danger' : 'warning';
});

const alertBannerClass: Record<string, string> = {
    success: 'bg-success-light text-success dark:bg-success-dark-light',
    danger: 'bg-danger-light text-danger dark:bg-danger-dark-light',
    warning: 'bg-warning-light text-warning dark:bg-warning-dark-light',
};

const alertTitle = computed(() => (props.log.is_abnormal ? 'Lectura fuera de rango' : 'Lectura dentro del rango normal'));

const valueColorClasses: Record<string, string> = {
    success: 'text-primary',
    danger: 'text-danger',
    warning: 'text-warning',
};

const valueColorClass = computed(() => valueColorClasses[alertLevel.value]);
</script>

<template>
    <div class="max-w-lg mx-auto text-center">
        <CheckCircle :size="56" class="text-success mx-auto mb-4" />
        <h2 class="text-2xl font-bold mb-2 dark:text-white-light">Lectura registrada</h2>
        <p class="text-gray-500 dark:text-[#506690] mb-8">El registro fue guardado correctamente.</p>

        <div class="mb-6 flex items-center justify-between">
            <div class="flex items-center rounded-full bg-primary/80 p-1 font-semibold text-white ltr:pr-3 rtl:pl-3">
                <img
                    class="block h-8 w-8 rounded-full border-2 border-white/50 object-cover ltr:mr-1 rtl:ml-1"
                    src="/assets/images/profile-34.jpeg"
                    alt=""
                />
                Paciente: <span class="pl-1 font-light">{{ log.user_patient.name }}</span>
            </div>
        </div>

        <div
            class="mb-6 flex items-center gap-3 rounded-lg p-4 text-left"
            :class="alertBannerClass[alertLevel]"
        >
            <TriangleAlert v-if="log.is_abnormal" :size="28" class="shrink-0" />
            <CheckCircle v-else :size="28" class="shrink-0" />
            <div>
                <p class="font-semibold">{{ alertTitle }}</p>
                <p v-if="log.range" class="text-sm opacity-80">
                    Rango esperado ({{ log.time_block }}): {{ log.range.min }} - {{ log.range.max }} mg/dL
                </p>
            </div>
        </div>

        <div class="panel p-6 text-left space-y-4">
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-[#506690]">Valor</span>
                <span class="text-2xl font-bold" :class="valueColorClass">{{ log.value }} <small class="text-sm font-normal">mg/dL</small></span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-[#506690]">Bloque de tiempo</span>
                <span class="capitalize font-medium dark:text-white-light">{{ log.time_block }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-[#506690]">Estado</span>
                <span :class="['badge', statusColor[log.status.name] ?? 'badge-outline-secondary']">
                    {{ log.status.name }}
                </span>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-500 dark:text-[#506690]">Fecha y hora</span>
                <span class="font-medium dark:text-white-light">{{ log.created_at }}</span>
            </div>
        </div>

        <button
            type="button"
            class="btn btn-primary mt-6 w-full"
            @click="emit('restart')"
        >
            Tomar otra muestra
        </button>
    </div>
</template>
