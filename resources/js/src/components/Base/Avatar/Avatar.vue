<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        name: string;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        size: 'md',
    }
);

const AVATAR_PALETTE = ['#4361ee', '#805dca', '#00ab55', '#e7515a', '#e2a03f', '#2196f3'];

const avatarColor = computed(() => {
    const name = props.name ?? '';
    let hash = 0;
    for (let i = 0; i < name.length; i++) {
        hash = name.charCodeAt(i) + ((hash << 5) - hash);
    }
    return AVATAR_PALETTE[Math.abs(hash) % AVATAR_PALETTE.length];
});

const initial = computed(() => props.name?.trim().charAt(0).toUpperCase() ?? '?');

const sizeClasses = computed(
    () =>
        ({
            sm: 'w-9 h-9 text-sm',
            md: 'w-12 h-12 text-lg',
            lg: 'w-20 h-20 md:w-32 md:h-32 text-3xl md:text-5xl',
        })[props.size]
);
</script>

<template>
    <span
        class="flex items-center justify-center rounded-full font-semibold text-white shrink-0"
        :class="sizeClasses"
        :style="{ backgroundColor: avatarColor }"
    >
        {{ initial }}
    </span>
</template>
