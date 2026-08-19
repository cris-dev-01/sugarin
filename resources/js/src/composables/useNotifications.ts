import { router } from '@inertiajs/vue3';
import { useZiggyRoute } from '@/composables/useRoute';

export function useNotifications() {
    const route = useZiggyRoute();

    function markAsRead(id: string) {
        router.patch(route('notifications.read', id), {}, { preserveScroll: true, preserveState: true });
    }

    function markAllAsRead() {
        router.patch(route('notifications.read-all'), {}, { preserveScroll: true, preserveState: true });
    }

    return { markAsRead, markAllAsRead };
}
