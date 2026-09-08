import { usePage } from '@inertiajs/vue3';
import type { PageProps } from '@/types';

export function usePermissions() {
    const page = usePage<PageProps>();

    const checkPermission = (permission: string): boolean => {
        return !!(page.props.auth.user && page.props.auth.user.permissions.includes(permission));
    };

    return { checkPermission };
}
