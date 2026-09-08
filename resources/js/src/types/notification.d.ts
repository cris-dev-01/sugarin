export interface Notification {
    messageNotification: string;
    typeNotification: string;
}

export type SweetAlertIcon = 'success' | 'error' | 'warning' | 'info' | 'question';

export type AppNotificationType = 'glucose-log' | 'abnormal-glucose-log' | 'overdue-glucose-log' | 'profile-changed';

export interface AppNotificationData {
    title: string;
    message: string;
    url?: string;
}

export interface AppNotification {
    id: string;
    type: AppNotificationType;
    data: AppNotificationData;
    read_at: string | null;
    created_at: string;
}

export interface PaginatedNotifications {
    data: AppNotification[];
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}