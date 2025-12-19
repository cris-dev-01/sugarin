<script lang="ts" setup>
import { ref, computed } from 'vue';
import { Head, usePage } from "@inertiajs/vue3";
import { 
    ChevronDown, 
    ChevronUp, 
    Droplet, 
    SlidersHorizontal, 
    Sun, 
    Moon 
} from 'lucide-vue-next';
import AppLayout from "@/Layouts/AppLayout.vue";
import apexchart from 'vue3-apexcharts';
import { useAppStore } from '@/stores/index';
import type { PageProps } from '@/types';

const page = usePage<PageProps>();
const store = useAppStore();
// unique visitors
    const uniqueVisitor = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        const isRtl = store.rtlClass === 'rtl' ? true : false;
        return {
            chart: {
                height: 360,
                type: 'bar',
                fontFamily: 'Nunito, sans-serif',
                toolbar: {
                    show: false,
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                width: 2,
                colors: ['transparent'],
            },
            colors: ['#5c1ac3', '#ffbb44'],
            dropShadow: {
                enabled: true,
                blur: 3,
                color: '#515365',
                opacity: 0.4,
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 10,
                    borderRadiusApplication: 'end',
                },
            },
            legend: {
                position: 'bottom',
                horizontalAlign: 'center',
                fontSize: '14px',
                itemMargin: {
                    horizontal: 8,
                    vertical: 8,
                },
            },
            grid: {
                borderColor: isDark ? '#191e3a' : '#e0e6ed',
                padding: {
                    left: 20,
                    right: 20,
                },
            },
            xaxis: {
                categories: ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'],
                axisBorder: {
                    show: true,
                    color: isDark ? '#3b3f5c' : '#e0e6ed',
                },
            },
            yaxis: {
                tickAmount: 6,
                opposite: isRtl ? true : false,
                labels: {
                    offsetX: isRtl ? -10 : 0,
                },
            },
            fill: {
                type: 'gradient',
                gradient: {
                    shade: isDark ? 'dark' : 'light',
                    type: 'vertical',
                    shadeIntensity: 0.3,
                    inverseColors: false,
                    opacityFrom: 1,
                    opacityTo: 0.8,
                    stops: [0, 100],
                },
            },
            tooltip: {
                marker: {
                    show: true,
                },
                y: {
                    formatter: (val: any) => {
                        return val;
                    },
                },
            },
        };
    });

    const uniqueVisitorSeries = ref([
        {
            name: 'Ayuno',
            data: [102, 110, 90, 134, 122],
        },
        {
            name: 'Última comida',
            data: [103, 115, 100, 86, 143],
        },
    ]);
</script>

<template>
    <Head title="Inicio" />
    <AppLayout>
        <div>
            <ul class="flex space-x-2 rtl:space-x-reverse">
                <li>
                    <span>Dashboard</span>
                </li>
            </ul>
            <div class="pt-5">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center rounded-full bg-primary/80 p-1 font-semibold text-white ltr:pr-3 rtl:pl-3">
                        <img
                            class="block h-8 w-8 rounded-full border-2 border-white/50 object-cover ltr:mr-1 rtl:ml-1"
                            src="/assets/images/profile-34.jpeg"
                            alt=""
                        />
                        Paciente: <span class="pl-1 font-light">Alan Green</span>
                    </div>
                </div>

                <div class="mb-6 grid gap-6 sm:grid-cols-2 xl:grid-cols-2">
                    <div class="panel h-full sm:col-span-2 xl:col-span-1">
                        <div class="mb-5 flex items-center">
                            <h5 class="text-lg font-semibold dark:text-white-light">
                                Últimas lecturas 
                                <span class="block text-sm font-normal text-white-dark">
                                    Ir a cada columna para el detalle.
                                </span>
                            </h5>
                            <div class="relative ltr:ml-auto rtl:mr-auto">
                                <div class="grid h-9 w-9 place-content-center rounded-full bg-primary/80 text-warning dark:bg-warning dark:text-[#ffeccb]">
                                    <Droplet
                                        :size="20"
                                        :color="'#ffffff'"
                                    />
                                </div>
                            </div>
                        </div>
                        <div>
                            <apexchart height="360" :options="uniqueVisitor" :series="uniqueVisitorSeries" class="overflow-hidden">
                                <!-- loader -->
                                <div class="grid min-h-[360px] place-content-center bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08]">
                                    <span
                                        class="inline-flex h-5 w-5 animate-spin rounded-full border-2 border-black !border-l-transparent dark:border-white"
                                    ></span>
                                </div>
                            </apexchart>
                        </div>
                    </div>

                    <div class="panel h-full">
                        <div class="mb-5 flex items-center dark:text-white-light">
                            <h5 class="text-lg font-semibold">Rango de glucosa establecido</h5>
                            <div class="relative ltr:ml-auto rtl:mr-auto">
                                <div class="grid h-9 w-9 place-content-center rounded-full bg-primary/80 text-warning dark:bg-warning dark:text-[#ffeccb]">
                                    <SlidersHorizontal
                                        :size="20"
                                        :color="'#ffffff'"
                                    />
                                </div>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div class="rounded-md bg-white px-4 py-2.5 shadow dark:bg-[#060818]">
                                <span class="mb-4 flex items-center justify-between dark:text-white">
                                    Ayuno
                                    <Sun
                                        :size="20"
                                        :color="'#D99A3D'"
                                    />
                                </span>
                                <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] mb-3 gap-2">
                                    80 mg/dL
                                    <ChevronDown
                                        :size="20"
                                    />
                                </div>
                                <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] gap-2">
                                    120 mg/dL
                                    <ChevronUp
                                        :size="20"
                                    />
                                </div>
                            </div>
                            <div class="rounded-md bg-white px-4 py-2.5 shadow dark:bg-[#060818]">
                                <span class="mb-4 flex items-center justify-between dark:text-white">
                                    Última comida
                                    <Moon
                                        :size="20"
                                        :color="'#4260EC'"
                                    />
                                </span>
                                <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] mb-3 gap-2">
                                    80 mg/dL
                                    <ChevronDown
                                        :size="20"
                                    />
                                </div>
                                <div class="btn w-full border-0 bg-[#ebedf2] py-1 text-base text-[#515365] shadow-none dark:bg-black dark:text-[#bfc9d4] gap-2">
                                    120 mg/dL
                                    <ChevronUp
                                        :size="20"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-6 grid gap-6 grid-cols-1">
                    <div class="panel h-full pb-0 sm:col-span-2 xl:col-span-1">
                        <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Lecturas recientes</h5>
                        <div class="relative mb-4 ltr:-mr-3 ltr:pr-3 rtl:-ml-3 rtl:pl-3">
                            <div class="cursor-pointer text-sm">
                                <div class="group relative flex items-center py-1.5">
                                    <div class="h-1.5 w-1.5 rounded-full bg-primary ltr:mr-1 rtl:ml-1.5"></div>
                                    <div class="flex-1">Updated Server Logs</div>
                                    <div class="text-xs text-white-dark ltr:ml-auto rtl:mr-auto dark:text-gray-500">Just Now</div>

                                    <span class="badge badge-outline-primary absolute bg-primary-light text-xs opacity-0 group-hover:opacity-100 ltr:right-0 rtl:left-0 dark:bg-[#0e1726]">
                                        Pending
                                    </span>
                                </div>
                                <div class="group relative flex items-center py-1.5">
                                    <div class="h-1.5 w-1.5 rounded-full bg-success ltr:mr-1 rtl:ml-1.5"></div>
                                    <div class="flex-1">Send Mail to HR and Admin</div>
                                    <div class="text-xs text-white-dark ltr:ml-auto rtl:mr-auto dark:text-gray-500">2 min ago</div>

                                    <span class="badge badge-outline-success absolute bg-success-light text-xs opacity-0 group-hover:opacity-100 ltr:right-0 rtl:left-0 dark:bg-[#0e1726]">
                                        Completed
                                    </span>
                                </div> 
                            </div>
                        </div>
                        <div class="border-t border-white-light dark:border-white/10">
                            <a href="javascript:;" class="group group flex items-center justify-center p-4 font-semibold hover:text-primary">
                                Ver todos
                                <ChevronDown
                                        :size="20"
                                    />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>


