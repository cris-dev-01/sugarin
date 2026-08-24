import type { AppNotification } from './notification';
import type { User } from './user';

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User|null;
    };
    notifications: AppNotification[];
};

export * from "./dashboardSettings";
export * from "./form";
export * from "./glucoseDashboard";
export * from "./glucoseLog";
export * from "./glucoseRange";
export * from "./notification";
export * from "./patient";
export * from "./user";
