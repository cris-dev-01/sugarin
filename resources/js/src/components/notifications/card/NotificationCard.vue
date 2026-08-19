<script setup lang="ts">
import { useNotificationIcon } from '@/composables/useNotificationIcon';
import type { AppNotification } from '@/types';

withDefaults(
    defineProps<{
        notification: AppNotification;
        showUnreadIndicator?: boolean;
    }>(),
    {
        showUnreadIndicator: true,
    }
);

defineEmits<{
    (e: 'click', notification: AppNotification): void;
}>();

const { notificationIcon, notificationIconClass } = useNotificationIcon();

function timeAgo(dateString: string): string {
    const diffMinutes = Math.floor((Date.now() - new Date(dateString).getTime()) / 60_000);

    if (diffMinutes < 1) return 'hace instantes';
    if (diffMinutes < 60) return `hace ${diffMinutes} min`;

    const diffHours = Math.floor(diffMinutes / 60);
    if (diffHours < 24) return `hace ${diffHours} h`;

    const diffDays = Math.floor(diffHours / 24);
    return `hace ${diffDays} d`;
}
</script>

<template>
    <button
        type="button"
        class="flex w-full items-start gap-3 px-4 py-3 text-left hover:bg-white-light/40 dark:hover:bg-dark/40"
        @click="$emit('click', notification)"
    >
        <span
            class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full"
            :class="notificationIconClass(notification.type)"
        >
            <component :is="notificationIcon(notification.type)" :size="16" />
        </span>
        <span class="min-w-0 flex-1">
            <span class="block truncate font-semibold dark:text-white-light">{{ notification.data.title }}</span>
            <span class="block text-sm dark:text-white-light/80">{{ notification.data.message }}</span>
            <span class="block text-xs font-normal text-white-dark">{{ timeAgo(notification.created_at) }}</span>
        </span>
        <span
            v-if="showUnreadIndicator && !notification.read_at"
            class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-primary"
        ></span>
    </button>
</template>
