<script lang="ts" setup>
import { computed, ref, watch } from 'vue';
import { ChevronLeft, ChevronRight, XIcon } from 'lucide-vue-next';
import NotificationCard from '@/components/notifications/card/NotificationCard.vue';
import { useNotifications } from '@/composables/useNotifications';
import type { AppNotification, PaginatedNotifications } from '@/types';

const props = defineProps<{
    show: boolean;
}>();

const emit = defineEmits<{
    (e: 'close'): void;
}>();

const { markAsRead } = useNotifications();

const isLoading = ref(false);
const notifications = ref<AppNotification[]>([]);
const currentPage = ref(1);
const lastPage = ref(1);
const total = ref(0);
const totalLabel = computed(() => `${total.value} ${total.value === 1 ? 'notificación' : 'notificaciones'}`);

async function loadNotifications(page: number) {
    isLoading.value = true;

    try {
        const response = await fetch(`/notifications?page=${page}`, {
            headers: { Accept: 'application/json' },
        });

        if (response.ok) {
            const body = (await response.json()) as PaginatedNotifications;
            notifications.value = body.data;
            currentPage.value = body.meta.current_page;
            lastPage.value = body.meta.last_page;
            total.value = body.meta.total;
        }
    } finally {
        isLoading.value = false;
    }
}

watch(
    () => props.show,
    (show) => {
        if (show) {
            loadNotifications(1);
        }
    }
);

function goToPage(page: number) {
    if (page >= 1 && page <= lastPage.value) {
        loadNotifications(page);
    }
}

function onNotificationClick(notification: AppNotification) {
    if (notification.read_at) {
        return;
    }

    notification.read_at = new Date().toISOString();
    markAsRead(notification.id);
}

function closeDrawer() {
    emit('close');
}
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed inset-0 z-[70] hidden bg-[black]/60 px-4 transition-[display]"
            :class="{ '!block': props.show }"
            @click="closeDrawer"
        ></div>

        <nav
            class="fixed bottom-0 top-0 z-[70] w-full max-w-[420px] bg-white shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] transition-[right] duration-300 ltr:-right-[420px] rtl:-left-[420px] dark:bg-[#0e1726]"
            :class="{ 'ltr:!right-0 rtl:!left-0': props.show }"
        >
            <perfect-scrollbar :options="{ swipeEasing: true, wheelPropagation: false }" class="relative h-full overflow-x-hidden">
                <div class="relative p-4 pb-0 text-start">
                    <button
                        type="button"
                        class="absolute top-4 opacity-30 hover:opacity-100 dark:text-white ltr:right-4 rtl:left-4"
                        @click="closeDrawer"
                    >
                        <XIcon :size="22" />
                    </button>
                    <h4 class="mb-1 dark:text-white">Notificaciones</h4>
                    <p class="text-white-dark">{{ totalLabel }}.</p>
                </div>

                <div v-if="isLoading" class="flex justify-center py-8">
                    <span class="inline-flex h-6 w-6 animate-spin rounded-full border-2 border-black !border-l-transparent dark:border-white"></span>
                </div>

                <template v-else>
                    <p v-if="!notifications.length" class="px-4 py-4 text-white-dark">No hay notificaciones para mostrar.</p>

                    <ul v-else class="mt-2 divide-y divide-white-light dark:divide-[#1b2e4b]">
                        <li v-for="notification in notifications" :key="notification.id">
                            <NotificationCard :notification="notification" @click="onNotificationClick" />
                        </li>
                    </ul>

                    <div v-if="notifications.length" class="flex items-center justify-between p-4">
                        <button type="button" class="btn btn-outline-primary btn-sm" :disabled="currentPage <= 1" @click="goToPage(currentPage - 1)">
                            <ChevronLeft :size="16" />
                        </button>
                        <span class="text-sm text-white-dark">Página {{ currentPage }} de {{ lastPage }}</span>
                        <button
                            type="button"
                            class="btn btn-outline-primary btn-sm"
                            :disabled="currentPage >= lastPage"
                            @click="goToPage(currentPage + 1)"
                        >
                            <ChevronRight :size="16" />
                        </button>
                    </div>
                </template>
            </perfect-scrollbar>
        </nav>
    </Teleport>
</template>
