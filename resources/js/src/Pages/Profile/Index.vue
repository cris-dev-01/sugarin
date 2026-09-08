<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
import { Tab, TabGroup, TabList, TabPanel, TabPanels } from '@headlessui/vue';
import { KeyRound, UserRound } from 'lucide-vue-next';
import AppLayout from '@/Layouts/AppLayout.vue';
import Notification from '@/components/Base/Notification/Notification.vue';
import ProfileSummaryCard from '@/components/profile/card/ProfileSummaryCard.vue';
import UpdateProfileForm from '@/components/profile/form/UpdateProfileForm.vue';
import UpdatePasswordForm from '@/components/profile/form/UpdatePasswordForm.vue';
import type { Notification as NotificationType, PageProps, Patient } from '@/types';

defineProps<{
    patient: Patient | null;
}>();

const page = usePage<PageProps>();
const user = computed(() => page.props.auth.user);

const notificationIsOpen = ref(false);
const notification = ref<NotificationType>({ messageNotification: '', typeNotification: '' });

const showNotification = (message: string, type: string) => {
    notification.value = { messageNotification: message, typeNotification: type };
    notificationIsOpen.value = true;
};
</script>

<template>
    <Head title="Mi perfil" />
    <AppLayout>
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <span>Mi perfil</span>
            </li>
        </ul>

        <div class="pt-5">
            <ProfileSummaryCard v-if="user" :user="user" :patient="patient" />

            <TabGroup vertical as="div" class="flex flex-col md:flex-row gap-5 mt-5">
                <div class="panel w-full md:w-80 md:shrink-0 self-start">
                    <TabList class="flex flex-col gap-1">
                        <Tab as="template" v-slot="{ selected }">
                            <button
                                type="button"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-md text-left border-l-2 transition-colors !outline-none"
                                :class="
                                    selected
                                        ? 'border-primary bg-primary-light text-primary font-semibold dark:bg-primary dark:text-primary-light'
                                        : 'border-transparent hover:bg-white-light/40 dark:hover:bg-dark/40'
                                "
                            >
                                <UserRound :size="18" />
                                Datos básicos
                            </button>
                        </Tab>
                        <Tab as="template" v-slot="{ selected }">
                            <button
                                type="button"
                                class="flex items-center gap-2 px-4 py-2.5 rounded-md text-left border-l-2 transition-colors !outline-none"
                                :class="
                                    selected
                                        ? 'border-primary bg-primary-light text-primary font-semibold dark:bg-primary dark:text-primary-light'
                                        : 'border-transparent hover:bg-white-light/40 dark:hover:bg-dark/40'
                                "
                            >
                                <KeyRound :size="18" />
                                Cambiar contraseña
                            </button>
                        </Tab>
                    </TabList>
                </div>

                <div class="panel flex-1 min-w-0">
                    <TabPanels>
                        <TabPanel>
                            <UpdateProfileForm v-if="user" :user="user" @showNotification="showNotification" />
                        </TabPanel>
                        <TabPanel>
                            <UpdatePasswordForm @showNotification="showNotification" />
                        </TabPanel>
                    </TabPanels>
                </div>
            </TabGroup>
        </div>

        <Notification :isOpen="notificationIsOpen" :notification="notification" @close="notificationIsOpen = false" />
    </AppLayout>
</template>
