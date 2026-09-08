declare global {
    function route(name?: string, params?: any, absolute?: boolean): string;
    const Ziggy: any;
}

export function useZiggyRoute() {
    return (name?: string, params?: any, absolute?: boolean) => {
        if (typeof route !== 'undefined') {
            return route(name, params, absolute);
        }
        
        // Fallback si route no está definida
        console.error('Ziggy route function is not available');
        return '';
    };
}

// Export default para usar como route()
export default useZiggyRoute;
