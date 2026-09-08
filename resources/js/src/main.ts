import { createApp, DefineComponent, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

// pinia
import { createPinia } from "pinia";

// perfect scrollbar
import { PerfectScrollbarPlugin } from "vue3-perfect-scrollbar";

// app settings
import appSetting from "@/app-setting";

// i18n
import i18n from "@/i18n";

// tippy
import { TippyPlugin } from "tippy.vue";

// input mask
import { vMaska } from "maska/vue";

// markdown editor
import VueEasymde from "vue3-easymde";
import "easymde/dist/easymde.min.css";

// popper
import Popper from "vue3-popper";

// json to excel
import vue3JsonExcel from "vue3-json-excel";

// main css
import "@/assets/css/app.css";

const appName = import.meta.env.VITE_APP_NAME || "SugarIn";

createInertiaApp({
    title: (title: string) => `${title} - ${appName}`,
    resolve: (name: string) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob<DefineComponent>("./Pages/**/*.vue"),
        ),
    setup({ el, App, props, plugin }) {
        const vue = createApp({ render: () => h(App, props) });

        // plugins del template
        vue.use(plugin);
        vue.use(createPinia());
        vue.use(PerfectScrollbarPlugin);
        vue.use(i18n);
        vue.use(TippyPlugin);
        vue.use(VueEasymde);
        vue.use(vue3JsonExcel);

        vue.directive("maska", vMaska);

        vue.component("Popper", Popper);

        // settings del template
        appSetting.init();

        vue.mount(el);
    },
});
