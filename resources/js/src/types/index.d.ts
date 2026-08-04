import type { User } from './user';

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User|null;
    };
};

export * from "./form";
export * from "./glucoseLog";
export * from "./glucoseRange";
export * from "./notification";
export * from "./user";
