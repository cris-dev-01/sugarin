import { Info, TriangleAlert } from 'lucide-vue-next';
import type { Component } from 'vue';

const STATUS_TEXT_CLASS: Record<string, string> = {
    'Rango normal': 'text-success',
    'Elevado - fuera de rango normal': 'text-warning',
    'Bajo - fuera de rango normal': 'text-danger',
};

const STATUS_ICON: Record<string, Component> = {
    'Elevado - fuera de rango normal': TriangleAlert,
    'Bajo - fuera de rango normal': Info,
};

export function useGlucoseStatus() {
    function statusTextClass(status: string): string {
        return STATUS_TEXT_CLASS[status] ?? '';
    }

    function statusIcon(status: string): Component | null {
        return STATUS_ICON[status] ?? null;
    }

    return { statusTextClass, statusIcon };
}
