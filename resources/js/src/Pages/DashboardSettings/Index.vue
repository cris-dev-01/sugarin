<script lang="ts" setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import Notification from '@/components/Base/Notification/Notification.vue';
import UpdateDashboardSettingsForm from '@/components/dashboard-settings/form/UpdateDashboardSettingsForm.vue';
import type { DashboardSettings, Notification as NotificationType } from '@/types';

defineProps<{
    settings: DashboardSettings;
}>();

const notificationIsOpen = ref(false);
const notification = ref<NotificationType>({ messageNotification: '', typeNotification: '' });

const showNotification = (message: string, type: string) => {
    notification.value = { messageNotification: message, typeNotification: type };
    notificationIsOpen.value = true;
};
</script>

<template>
    <Head title="Configuración del dashboard" />
    <AppLayout>
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <span>Configuración del dashboard</span>
            </li>
        </ul>

        <div class="panel mt-6">
            <h5 class="mb-1 text-lg font-semibold dark:text-white-light">Parámetros del dashboard de glucosa</h5>
            <p class="text-white-dark text-sm mb-5">
                Estos valores gobiernan el cálculo de triage y adherencia. Los cambios se aplican de inmediato.
            </p>

            <UpdateDashboardSettingsForm :settings="settings" @showNotification="showNotification" />
        </div>

        <Notification :isOpen="notificationIsOpen" :notification="notification" @close="notificationIsOpen = false" />
    </AppLayout>
</template>
