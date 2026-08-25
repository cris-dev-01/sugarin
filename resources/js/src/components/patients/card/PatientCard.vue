<script lang="ts" setup>
import Avatar from '@/components/Base/Avatar/Avatar.vue';

withDefaults(
    defineProps<{
        name: string;
        email?: string;
        document?: string;
        size?: 'sm' | 'md' | 'lg';
    }>(),
    {
        size: 'sm',
    }
);
</script>

<template>
    <div class="flex items-center gap-4">
        <Avatar :name="name" :size="size" />
        <div>
            <p class="font-semibold dark:text-white-light">{{ name }}</p>
            <p v-if="email" class="text-sm text-gray-500 dark:text-[#506690]">{{ email }}</p>
            <p v-if="document" class="text-xs text-gray-400 dark:text-[#506690]">{{ document }}</p>
        </div>
    </div>
</template>
