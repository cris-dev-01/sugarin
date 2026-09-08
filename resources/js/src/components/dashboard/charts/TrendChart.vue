<script lang="ts" setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import * as am5 from '@amcharts/amcharts5';
import * as am5xy from '@amcharts/amcharts5/xy';
import am5themes_Animated from '@amcharts/amcharts5/themes/Animated';
import { useAppStore } from '@/stores/index';
import type { PatientSummaryRecentLog } from '@/types';

const props = defineProps<{
    logs: PatientSummaryRecentLog[];
}>();

const store = useAppStore();
const chartDiv = ref<HTMLDivElement | null>(null);
let root: am5.Root | null = null;

function render(): void {
    if (!chartDiv.value) {
        return;
    }

    root?.dispose();
    root = am5.Root.new(chartDiv.value);
    root.setThemes([am5themes_Animated.new(root)]);

    const isDark = store.isDarkMode;
    const textColor = am5.color(isDark ? 0xbfc9d4 : 0x515365);
    const gridColor = am5.color(isDark ? 0x191e3a : 0xe0e6ed);

    const chart = root.container.children.push(
        am5xy.XYChart.new(root, {
            panX: false,
            panY: false,
            wheelX: 'none',
            wheelY: 'none',
            layout: root.verticalLayout,
        })
    );

    const xAxis = chart.xAxes.push(
        am5xy.DateAxis.new(root, {
            baseInterval: { timeUnit: 'hour', count: 1 },
            renderer: am5xy.AxisRendererX.new(root, {}),
        })
    );
    xAxis.get('renderer').labels.template.setAll({ fill: textColor });
    xAxis.get('renderer').setAll({ stroke: gridColor });

    const yAxis = chart.yAxes.push(
        am5xy.ValueAxis.new(root, {
            renderer: am5xy.AxisRendererY.new(root, {}),
        })
    );
    yAxis.get('renderer').labels.template.setAll({ fill: textColor });
    yAxis.get('renderer').setAll({ stroke: gridColor });

    const makeSeries = (name: string, timeBlock: 'mañana' | 'anochecer', color: number) => {
        const series = chart.series.push(
            am5xy.LineSeries.new(root!, {
                name,
                xAxis,
                yAxis,
                valueYField: 'value',
                valueXField: 'date',
                stroke: am5.color(color),
            })
        );

        series.strokes.template.setAll({ strokeWidth: 2 });

        series.bullets.push(() =>
            am5.Bullet.new(root!, {
                sprite: am5.Circle.new(root!, { radius: 4, fill: am5.color(color) }),
            })
        );

        const data = props.logs
            .filter((log) => log.time_block === timeBlock)
            .map((log) => ({ date: new Date(log.created_at).getTime(), value: log.value }))
            .sort((a, b) => a.date - b.date);

        series.data.setAll(data);

        return series;
    };

    const morning = makeSeries('Mañana', 'mañana', 0xd99a3d);
    const evening = makeSeries('Anochecer', 'anochecer', 0x4260ec);

    chart.set('cursor', am5xy.XYCursor.new(root, { behavior: 'none' }));

    const legend = chart.children.push(am5.Legend.new(root, {}));
    legend.labels.template.setAll({ fill: textColor });
    legend.data.setAll([morning, evening]);
}

onMounted(render);
onUnmounted(() => root?.dispose());
watch(() => props.logs, render);
watch(() => store.isDarkMode, render);
</script>

<template>
    <div v-if="!props.logs.length" class="flex h-[320px] items-center justify-center text-white-dark">
        Sin lecturas registradas para graficar la tendencia.
    </div>
    <div v-else ref="chartDiv" class="h-[320px] w-full"></div>
</template>
