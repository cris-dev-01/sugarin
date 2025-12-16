<script lang="ts" setup>
import { ref, computed } from 'vue';
import { Head } from "@inertiajs/vue3";
import AppLayout from "@/Layouts/AppLayout.vue";
import apexchart from 'vue3-apexcharts';
import { useAppStore } from '@/stores/index';
import { useMeta } from '@/composables/use-meta';
useMeta({ title: 'Analytics Admin' });
const store = useAppStore();

    // statistics
    const totalVisit = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        return {
            chart: {
                height: 58,
                type: 'line',
                fontFamily: 'Nunito, sans-serif',
                sparkline: {
                    enabled: true,
                },
                dropShadow: {
                    enabled: true,
                    blur: 3,
                    color: '#009688',
                    opacity: 0.4,
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            colors: ['#009688'],
            grid: {
                padding: {
                    top: 5,
                    bottom: 5,
                    left: 5,
                    right: 5,
                },
            },
            tooltip: {
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: (val: any) => {
                            return '';
                        },
                    },
                },
            },
        };
    });

    const totalVisitSeries = ref([
        {
            data: [21, 9, 36, 12, 44, 25, 59, 41, 66, 25],
        },
    ]);

    //paid visit
    const paidVisit = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        return {
            chart: {
                height: 58,
                type: 'line',
                fontFamily: 'Nunito, sans-serif',
                sparkline: {
                    enabled: true,
                },
                dropShadow: {
                    enabled: true,
                    blur: 3,
                    color: '#e2a03f',
                    opacity: 0.4,
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            colors: ['#e2a03f'],
            grid: {
                padding: {
                    top: 5,
                    bottom: 5,
                    left: 5,
                    right: 5,
                },
            },
            tooltip: {
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: (val: any) => {
                            return '';
                        },
                    },
                },
            },
        };
    });

    const paidVisitSeries = ref([
        {
            data: [22, 19, 30, 47, 32, 44, 34, 55, 41, 69],
        },
    ]);

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
                categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
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
            name: 'Direct',
            data: [58, 44, 55, 57, 56, 61, 58, 63, 60, 66, 56, 63],
        },
        {
            name: 'Organic',
            data: [91, 76, 85, 101, 98, 87, 105, 91, 114, 94, 66, 70],
        },
    ]);

    // followers
    const followers = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        return {
            chart: {
                height: 160,
                type: 'area',
                fontFamily: 'Nunito, sans-serif',
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            colors: ['#4361ee'],
            grid: {
                padding: {
                    top: 5,
                },
            },
            yaxis: {
                show: false,
            },
            tooltip: {
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: (val: any) => {
                            return '';
                        },
                    },
                },
            },
            option: { fill: null },

            if(isDark: boolean) {
                option: {
                    fill: {
                        type: 'gradient';
                        gradient: {
                            type: 'vertical';
                            shadeIntensity: 1;
                            inverseColors: !1;
                            opacityFrom: 0.3;
                            opacityTo: 0.05;
                            stops: [100, 100];
                        }
                    }
                }
            },
        };
    });

    const followersSeries = ref([
        {
            data: [38, 60, 38, 52, 36, 40, 28],
        },
    ]);

    // referral
    const referral = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        return {
            chart: {
                height: 160,
                type: 'area',
                fontFamily: 'Nunito, sans-serif',
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            colors: ['#e7515a'],
            grid: {
                padding: {
                    top: 5,
                },
            },
            yaxis: {
                show: false,
            },
            tooltip: {
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: (val: any) => {
                            return '';
                        },
                    },
                },
            },

            if(isDark: boolean) {
                option: {
                    fill: {
                        type: 'gradient';
                        gradient: {
                            type: 'vertical';
                            shadeIntensity: 1;
                            inverseColors: !1;
                            opacityFrom: 0.3;
                            opacityTo: 0.05;
                            stops: [100, 100];
                        }
                    }
                }
            },
        };
    });

    const referralSeries = ref([
        {
            data: [60, 28, 52, 38, 40, 36, 38],
        },
    ]);

    // engagement
    const engagement = computed(() => {
        const isDark: boolean = store.theme === 'dark' || store.isDarkMode ? true : false;
        return {
            chart: {
                height: 160,
                type: 'area',
                fontFamily: 'Nunito, sans-serif',
                sparkline: {
                    enabled: true,
                },
            },
            stroke: {
                curve: 'smooth',
                width: 2,
            },
            colors: ['#1abc9c'],
            grid: {
                padding: {
                    top: 5,
                },
            },
            yaxis: {
                show: false,
            },
            tooltip: {
                x: {
                    show: false,
                },
                y: {
                    title: {
                        formatter: (val: any) => {
                            return '';
                        },
                    },
                },
            },

            if(isDark: boolean) {
                option: {
                    fill: {
                        type: 'gradient';
                        gradient: {
                            type: 'vertical';
                            shadeIntensity: 1;
                            inverseColors: !1;
                            opacityFrom: 0.3;
                            opacityTo: 0.05;
                            stops: [100, 100];
                        }
                    }
                }
            },
        };
    });

    const engagementSeries = ref([
        {
            name: 'Sales',
            data: [28, 50, 36, 60, 38, 52, 38],
        },
    ]);
</script>

<template>
    <Head title="Inicio" />
    <AppLayout>
    <div>
        <ul class="flex space-x-2 rtl:space-x-reverse">
            <li>
                <a href="javascript:;" class="text-primary hover:underline">Dashboard</a>
            </li>
            <li class="before:content-['/'] before:mr-2 rtl:before:ml-2">
                <span>Analytics</span>
            </li>
        </ul>
        <div class="pt-5">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                <div class="panel h-full sm:col-span-2 lg:col-span-1">
                    <!-- statistics -->
                    <div class="flex items-center justify-between dark:text-white-light mb-5">
                        <h5 class="font-semibold text-lg">Statistics</h5>
                        <div class="dropdown">
                            <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-start' : 'bottom-end'" offsetDistance="0" class="align-middle">
                                <button type="button">
                                    <svg
                                        class="w-5 h-5 text-black/70 dark:text-white/70 hover:!text-primary"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <circle cx="5" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                        <circle opacity="0.5" cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                        <circle cx="19" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                    </svg>
                                </button>
                                <template #content="{ close }">
                                    <ul @click="close()">
                                        <li>
                                            <a href="javascript:;">This Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">Last Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">This Month</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">Last Month</a>
                                        </li>
                                    </ul>
                                </template>
                            </Popper>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-8 text-sm text-[#515365] font-bold">
                        <div>
                            <div>
                                <div>Total Visits</div>
                                <div class="text-[#f8538d] text-lg">423,964</div>
                            </div>
                            <apexchart height="58" :options="totalVisit" :series="totalVisitSeries" class="overflow-hidden">
                                <!-- loader -->
                                <div class="min-h-[58px] grid place-content-center bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08]">
                                    <span
                                        class="animate-spin border-2 border-black dark:border-white !border-l-transparent rounded-full w-5 h-5 inline-flex"
                                    ></span>
                                </div>
                            </apexchart>
                        </div>

                        <div>
                            <div>
                                <div>Paid Visits</div>
                                <div class="text-[#f8538d] text-lg">7,929</div>
                            </div>
                            <apexchart height="58" :options="paidVisit" :series="paidVisitSeries" class="overflow-hidden">
                                <!-- loader -->
                                <div class="min-h-[58px] grid place-content-center bg-white-light/30 dark:bg-dark dark:bg-opacity-[0.08]">
                                    <span
                                        class="animate-spin border-2 border-black dark:border-white !border-l-transparent rounded-full w-5 h-5 inline-flex"
                                    ></span>
                                </div>
                            </apexchart>
                        </div>
                    </div>
                </div>

                <div class="panel h-full">
                    <div class="flex items-center justify-between dark:text-white-light mb-5">
                        <h5 class="font-semibold text-lg">Expenses</h5>
                        <div class="dropdown">
                            <Popper :placement="store.rtlClass === 'rtl' ? 'bottom-start' : 'bottom-end'" offsetDistance="0" class="align-middle">
                                <button type="button">
                                    <svg
                                        class="w-5 h-5 text-black/70 dark:text-white/70 hover:!text-primary"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                    >
                                        <circle cx="5" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                        <circle opacity="0.5" cx="12" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                        <circle cx="19" cy="12" r="2" stroke="currentColor" stroke-width="1.5" />
                                    </svg>
                                </button>
                                <template #content="{ close }">
                                    <ul @click="close()">
                                        <li>
                                            <a href="javascript:;">This Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">Last Week</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">This Month</a>
                                        </li>
                                        <li>
                                            <a href="javascript:;">Last Month</a>
                                        </li>
                                    </ul>
                                </template>
                            </Popper>
                        </div>
                    </div>
                    <div class="text-[#e95f2b] text-3xl font-bold my-10">
                        <span class="ltr:mr-2 rtl:ml-2">$ 45,141</span>
                        <span class="text-black text-sm dark:text-white-light ltr:mr-1 rtl:ml-1">this week </span>
                        <svg class="w-5 h-5 text-success inline" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path
                                opacity="0.5"
                                d="M22 7L14.6203 14.3347C13.6227 15.3263 13.1238 15.822 12.5051 15.822C11.8864 15.8219 11.3876 15.326 10.3902 14.3342L10.1509 14.0962C9.15254 13.1035 8.65338 12.6071 8.03422 12.6074C7.41506 12.6076 6.91626 13.1043 5.91867 14.0977L2 18"
                                stroke="currentColor"
                                stroke-width="1.5"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path d="M22.0001 12.5458V7H16.418" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="w-full rounded-full h-5 p-1 bg-dark-light overflow-hidden shadow-3xl dark:shadow-none dark:bg-dark-light/10">
                            <div
                                class="bg-gradient-to-r from-[#4361ee] to-[#805dca] w-full h-full rounded-full relative before:absolute before:inset-y-0 ltr:before:right-0.5 rtl:before:left-0.5 before:bg-white before:w-2 before:h-2 before:rounded-full before:m-auto"
                                style="width: 65%"
                            ></div>
                        </div>
                        <span class="ltr:ml-5 rtl:mr-5 dark:text-white-light">57%</span>
                    </div>
                </div>

                <div
                    class="panel h-full overflow-hidden before:bg-[#1937cc] before:absolute before:-right-44 before:top-0 before:bottom-0 before:m-auto before:rounded-full before:w-96 before:h-96 grid grid-cols-1 content-between"
                    style="background: linear-gradient(0deg, #00c6fb -227%, #005bea) !important"
                >
                    <div class="flex items-start justify-between text-white-light mb-16 z-[7]">
                        <h5 class="font-semibold text-lg">Total Balance</h5>

                        <div class="relative text-xl whitespace-nowrap">
                            $ 41,741.42
                            <span class="table text-[#d3d3d3] bg-[#4361ee] rounded p-1 text-xs mt-1 ltr:ml-auto rtl:mr-auto">+ 2453</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between z-10">
                        <div class="flex items-center justify-between">
                            <a
                                href="javascript:;"
                                class="shadow-[0_0_2px_0_#bfc9d4] rounded p-1 text-white-light hover:bg-[#1937cc] place-content-center ltr:mr-2 rtl:ml-2"
                            >
                                <svg
                                    class="w-5 h-5"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    fill="none"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                >
                                    <line x1="12" y1="5" x2="12" y2="19"></line>
                                    <line x1="5" y1="12" x2="19" y2="12"></line>
                                </svg>
                            </a>
                            <a href="javascript:;" class="shadow-[0_0_2px_0_#bfc9d4] rounded p-1 text-white-light hover:bg-[#1937cc] grid place-content-center">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                        d="M2 12C2 8.22876 2 6.34315 3.17157 5.17157C4.34315 4 6.22876 4 10 4H14C17.7712 4 19.6569 4 20.8284 5.17157C22 6.34315 22 8.22876 22 12C22 15.7712 22 17.6569 20.8284 18.8284C19.6569 20 17.7712 20 14 20H10C6.22876 20 4.34315 20 3.17157 18.8284C2 17.6569 2 15.7712 2 12Z"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                    ></path>
                                    <path opacity="0.5" d="M10 16H6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path opacity="0.5" d="M14 16H12.5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                    <path opacity="0.5" d="M2 10L22 10" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"></path>
                                </svg>
                            </a>
                        </div>
                        <a href="javascript:;" class="shadow-[0_0_2px_0_#bfc9d4] rounded p-1 text-white-light hover:bg-[#4361ee] z-10"> Upgrade </a>
                    </div>
                </div>
            </div>

            
        </div>
    </div>
    </AppLayout>
</template>


