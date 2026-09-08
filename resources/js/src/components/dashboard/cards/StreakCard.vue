<script lang="ts" setup>
import { Flame } from 'lucide-vue-next';

const props = defineProps<{
    days: number;
}>();
</script>

<template>
    <div class="panel bg-gradient-to-r from-orange-500 to-amber-400 text-white">
        <div class="flex items-center justify-between">
            <div class="text-md font-semibold">Racha en rango normal</div>
            <Flame :size="24" />
        </div>
        <div class="mt-5 flex items-baseline gap-2">
            <span class="text-4xl font-bold">{{ props.days }}</span>
            <span class="text-lg">{{ props.days === 1 ? 'día' : 'días' }}</span>
        </div>
        <div class="mt-2 text-sm opacity-80">
            {{ props.days > 0 ? 'Sigue así, cada lectura en rango cuenta.' : 'Registra una lectura en rango normal para comenzar tu racha.' }}
        </div>
    </div>
</template>
