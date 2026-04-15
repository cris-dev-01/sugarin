declare module 'ziggy-js' {
    export interface Config {
        url: string;
        port: number | null;
        defaults: Record<string, any>;
        routes: Record<string, any>;
    }

    export interface Router {
        (name?: string, params?: any, absolute?: boolean, config?: Config): string;
        current(): string | undefined;
        current(name: string): boolean;
        has(name: string): boolean;
    }

    const Ziggy: Router;
    export default Ziggy;
}

declare global {
    interface Window {
        Ziggy?: any;
    }
    
    const Ziggy: any;
}
