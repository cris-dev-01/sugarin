import type { VisitOptions } from '@inertiajs/core';

interface FormHandlersOptions {
    onSuccess?: (data: any) => void;
    onError?: (errors: any) => void;
    onForbidden?: (message?: string) => void;
    onServerError?: (message?: string) => void;
}

/**
 * Composable para manejar respuestas HTTP comunes en formularios de Inertia.js
 * 
 * @param options - Opciones para los diferentes callbacks
 * @returns Un objeto con los handlers para usar en form.post(), form.put(), etc.
 * 
 * @example
 * const handlers = useInertiaFormHandlers({
 *   onSuccess: () => emit("showNotification", "Éxito", "success"),
 *   onError: () => emit("showNotification", "Error de validación", "error"),
 *   onForbidden: (message) => emit("showNotification", message || "Sin permisos", "error"),
 * });
 * 
 * form.post('/users', handlers);
 */
export function useInertiaFormHandlers(options: FormHandlersOptions = {}) {
    const handlers: Partial<VisitOptions> = {
        onSuccess: options.onSuccess,
        onError: options.onError,
        onHttpException: (response) => {
            // Error 403 - Forbidden (sin permisos)
            if (response.status === 403) {
                if (options.onForbidden) {
                    // Extraer el mensaje del servidor si existe
                    const message = response.data?.message || 'No tienes permisos para realizar esta acción.';
                    options.onForbidden(message);
                }
                return false; // Previene la navegación a la página de error
            }
            
            // Error 500 - Internal Server Error
            if (response.status === 500) {
                if (options.onServerError) {
                    const message = response.data?.message || 'Ocurrió un error en el servidor.';
                    options.onServerError(message);
                }
                return false;
            }
            
            // Para otros errores HTTP, permite el comportamiento por defecto
            // (puedes agregar más casos según necesites)
        },
    };

    return handlers;
}
