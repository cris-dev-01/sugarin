import { ClipboardClock, Droplets, TriangleAlert, UserRound } from 'lucide-vue-next';
import type { Component } from 'vue';
import type { AppNotificationType } from '@/types';

const NOTIFICATION_ICON: Record<AppNotificationType, Component> = {
    'glucose-log': Droplets,
    'abnormal-glucose-log': TriangleAlert,
    'overdue-glucose-log': ClipboardClock,
    'profile-changed': UserRound,
};

const NOTIFICATION_ICON_CLASS: Record<AppNotificationType, string> = {
    'glucose-log': 'bg-primary-light text-primary dark:bg-primary-dark-light',
    'abnormal-glucose-log': 'bg-danger-light text-danger dark:bg-danger-dark-light',
    'overdue-glucose-log': 'bg-warning-light text-warning dark:bg-warning-dark-light',
    'profile-changed': 'bg-primary-light text-primary dark:bg-primary-dark-light',
};

export function useNotificationIcon() {
    function notificationIcon(type: AppNotificationType): Component {
        return NOTIFICATION_ICON[type];
    }

    function notificationIconClass(type: AppNotificationType): string {
        return NOTIFICATION_ICON_CLASS[type];
    }

    return { notificationIcon, notificationIconClass };
}
