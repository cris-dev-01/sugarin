<script lang="ts" setup>
import { onMounted, computed } from 'vue';
import { useAppStore } from '@/stores/index';
import { usePage, Link } from "@inertiajs/vue3";
import {
    ChartColumn,
    Cog,
    Droplets,
    SlidersHorizontal,
    UsersRound
} from 'lucide-vue-next';
import { usePermissions } from '@/composables/usePermissions';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const store = useAppStore();
const { checkPermission } = usePermissions();
const isAdministrator = computed(() => page.props.auth.user?.role === 'Administrator' ? true : false);
const isPatient = computed(() => page.props.auth.user?.role === 'Patient' ? true : false);

onMounted(() => {
    const selector = document.querySelector('.sidebar ul a[href="' + window.location.pathname + '"]');
    if (selector) {
        selector.classList.add('active');
        const ul: any = selector.closest('ul.sub-menu');
        if (ul) {
            let ele: any = ul.closest('li.menu').querySelectorAll('.nav-link') || [];
            if (ele.length) {
                ele = ele[0];
                setTimeout(() => {
                    ele.click();
                });
            }
        }
    }
});

const toggleMobileMenu = () => {
    if (window.innerWidth < 1024) {
        store.toggleSidebar();
    }
};
</script>

<template>
    <div :class="{ 'dark text-white-dark': store.semidark }">
        <nav class="sidebar fixed min-h-screen h-full top-0 bottom-0 w-[260px] shadow-[5px_0_25px_0_rgba(94,92,154,0.1)] z-50 transition-all duration-300">
            <div class="bg-white dark:bg-[#0e1726] h-full">
                <div class="flex justify-between items-center px-4 py-3">
                    <a href="/" class="main-logo flex items-center shrink-0">
                        <img src="/assets/images/logos/logo-light.png" alt="Sugarin" class="w-8 h-8 dark:hidden" />
                        <img src="/assets/images/logos/logo-dark.png" alt="Sugarin" class="hidden w-8 h-8 dark:block" />
                        <span class="text-2xl ltr:ml-1.5 rtl:mr-1.5 font-semibold align-middle lg:inline dark:text-white-light">SugarIn</span>
                    </a>
                    <a
                        href="javascript:;"
                        class="collapse-icon w-8 h-8 rounded-full flex items-center hover:bg-gray-500/10 dark:hover:bg-dark-light/10 dark:text-white-light transition duration-300 rtl:rotate-180 hover:text-primary"
                        @click="store.toggleSidebar()"
                    >
                        <svg class="w-5 h-5 m-auto" width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 19L7 12L13 5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                            <path
                                opacity="0.5"
                                d="M16.9998 19L10.9998 12L16.9998 5"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </a>
                </div>
                <perfect-scrollbar
                    :options="{
                        swipeEasing: true,
                        wheelPropagation: false,
                    }"
                    class="h-[calc(100vh-80px)] relative"
                >
                    <ul class="relative font-semibold space-y-0.5 p-4 py-0">
                        <li 
                            v-if="isAdministrator"
                            class="menu nav-item"
                        >
                            <Link href="/" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <ChartColumn
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Dashboard</span>
                                </div>
                            </Link>
                        </li>
                        <li 
                            v-if="isPatient"
                            class="menu nav-item"
                        >
                            <Link href="/summary" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <ChartColumn
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Mi Resumen</span>
                                </div>
                            </Link>
                        </li>
                        <li 
                            v-if="isAdministrator || isPatient"
                            class="menu nav-item">
                            <Link href="/glucose-logs" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <Droplets
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Tomar Muestra</span>
                                </div>
                            </Link>
                        </li>
                        <li 
                            v-if="isAdministrator"
                            class="menu nav-item"
                        >
                            <Link href="/glucose-ranges" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <Cog
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">
                                        Parámetros
                                    </span>
                                </div>
                            </Link>
                        </li>
                        <li
                            v-if="isAdministrator"
                            class="menu nav-item"
                        >
                            <Link href="/patients" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <UsersRound
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">Pacientes</span>
                                </div>
                            </Link>
                        </li>
                        <li
                            v-if="checkPermission('show-dashboard-settings')"
                            class="menu nav-item"
                        >
                            <Link href="/dashboard-settings" class="nav-link group" @click="toggleMobileMenu">
                                <div class="flex items-center">
                                    <SlidersHorizontal
                                        :size="20"
                                    />
                                    <span class="ltr:pl-3 rtl:pr-3 text-black dark:text-[#506690] dark:group-hover:text-white-dark">
                                        Configuración dashboard
                                    </span>
                                </div>
                            </Link>
                        </li>
                    </ul>
                </perfect-scrollbar>
            </div>
        </nav>
    </div>
</template>