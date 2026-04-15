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
                    <span>Mi Resumen</span>
                </li>
            </ul>
            <div class="pt-5">
               
                <div class="mb-6 grid grid-cols-1 gap-6 text-white sm:grid-cols-2 xl:grid-cols-4">
                    <div class="panel bg-gradient-to-r from-cyan-500 to-cyan-400">
                        <div class="flex justify-between">
                            <div class="text-md font-semibold ltr:mr-1 rtl:ml-1">Rango de glucosa en ayunas</div>
                            <div class="dropdown">
                                <client-only>
                                    <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-start' : 'bottom-end'" offsetDistance="0" class="align-middle">
                                        <button type="button">
                                            <icon-horizontal-dots class="opacity-70 hover:opacity-80" />
                                        </button>
                                        <template #content="{ close }">
                                            <ul @click="close()" class="text-black dark:text-white-dark">
                                                <li>
                                                    <a href="javascript:;">View Report</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:;">Edit Report</a>
                                                </li>
                                            </ul>
                                        </template>
                                    </Popper>
                                </client-only>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center">
                            <div class="flex items-center gap-2 text-3xl font-bold ltr:mr-3 rtl:ml-3">
                                <ChevronUp
                                    :size="20"
                                />
                                140 mg/dL
                        </div>
                        </div>
                        <div class="mt-5 flex items-center font-semibold">
                            <ChevronDown
                                :size="20"
                            />
                            80 mg/dL
                        </div>
                    </div>

                    <!-- Users Visit -->
                    <div class="panel bg-gradient-to-r from-blue-500 to-blue-400">
                        <div class="flex justify-between">
                            <div class="text-md font-semibold ltr:mr-1 rtl:ml-1">Rango de glucosa última comida</div>
                            <div class="dropdown">
                                <client-only>
                                    <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-start' : 'bottom-end'" offsetDistance="0" class="align-middle">
                                        <button type="button">
                                            <icon-horizontal-dots class="opacity-70 hover:opacity-80" />
                                        </button>
                                        <template #content="{ close }">
                                            <ul @click="close()" class="text-black dark:text-white-dark">
                                                <li>
                                                    <a href="javascript:;">View Report</a>
                                                </li>
                                                <li>
                                                    <a href="javascript:;">Edit Report</a>
                                                </li>
                                            </ul>
                                        </template>
                                    </Popper>
                                </client-only>
                            </div>
                        </div>
                        <div class="mt-5 flex items-center">
                            <div class="flex items-center gap-2 text-3xl font-bold ltr:mr-3 rtl:ml-3">
                                <ChevronUp
                                    :size="20"
                                />
                                180 mg/dL
                            </div>
                        </div>
                        <div class="mt-5 flex items-center font-semibold">
                            <ChevronDown
                                :size="20"
                            />
                            80 mg/dL
                        </div>
                    </div>
                </div>

                <div class="mb-6 grid gap-6 grid-cols-1">
                    <div class="panel h-full pb-0 sm:col-span-2 xl:col-span-1">
                        <h5 class="mb-5 text-lg font-semibold dark:text-white-light">Lecturas disponibles (tabla)</h5>
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


