<script setup lang="ts">
import { computed, ref } from 'vue';
import type { DashboardOverview } from '@/types';
import { Badge } from '@/components/ui/badge';

interface Props {
    data?: DashboardOverview | null;
    loading?: boolean;
}

const props = defineProps<Props>();
const hoveredPoint = ref<{ datasetIndex: number; pointIndex: number } | null>(null);

// Chart dimensions
const CHART_PADDING = { top: 20, right: 20, bottom: 40, left: 50 };
const CHART_WIDTH = 800;
const CHART_HEIGHT = 350;

const chartData = computed(() => {
    if (!props.data) return { labels: [], datasets: [] };
    return props.data;
});

const datasets = computed(() => props.data?.datasets || []);

const maxValue = computed(() => {
    if (!props.data?.datasets.length) return 100;
    const allValues = props.data.datasets.flatMap(d => d.data);
    const max = Math.max(...allValues, 1);
    // Add 10% padding to the top
    return Math.ceil(max * 1.1);
});

const minValue = computed(() => {
    if (!props.data?.datasets.length) return 0;
    const allValues = props.data.datasets.flatMap(d => d.data);
    return Math.min(...allValues, 0);
});

const colorMap = {
    'Users': '#10b981',      // Green
    'Blogs': '#ef4444',      // Red
    'Products': '#3b82f6',   // Blue
};

const getColor = (name: string) => colorMap[name as keyof typeof colorMap] || '#9ca3af';

// Calculate grid lines (5 horizontal lines)
const gridLines = computed(() => {
    const lines = [];
    const step = (maxValue.value - minValue.value) / 4;
    for (let i = 0; i <= 4; i++) {
        lines.push({
            value: minValue.value + step * i,
            y: CHART_HEIGHT - CHART_PADDING.bottom - (step * i / (maxValue.value - minValue.value)) * (CHART_HEIGHT - CHART_PADDING.top - CHART_PADDING.bottom)
        });
    }
    return lines.reverse();
});

// Convert data point to coordinates
const getPointPosition = (dataIndex: number, value: number) => {
    const labels = chartData.value.labels;
    const chartWidth = CHART_WIDTH - CHART_PADDING.left - CHART_PADDING.right;
    const chartHeight = CHART_HEIGHT - CHART_PADDING.top - CHART_PADDING.bottom;
    
    const x = CHART_PADDING.left + (dataIndex / Math.max(labels.length - 1, 1)) * chartWidth;
    const y = CHART_PADDING.top + chartHeight - ((value - minValue.value) / (maxValue.value - minValue.value)) * chartHeight;
    
    return { x, y };
};

// Generate SVG path for line
const getLinePath = (dataset: any) => {
    if (!dataset.data.length) return '';
    
    const points = dataset.data.map((value: number, index: number) => 
        getPointPosition(index, value)
    );
    
    // Simple line path
    let path = `M ${points[0].x} ${points[0].y}`;
    for (let i = 1; i < points.length; i++) {
        path += ` L ${points[i].x} ${points[i].y}`;
    }
    
    return path;
};

// Generate SVG path for area fill
const getAreaPath = (dataset: any) => {
    if (!dataset.data.length) return '';
    
    const points = dataset.data.map((value: number, index: number) => 
        getPointPosition(index, value)
    );
    
    const bottomY = CHART_HEIGHT - CHART_PADDING.bottom;
    
    let path = `M ${points[0].x} ${bottomY}`;
    path += ` L ${points[0].x} ${points[0].y}`;
    
    for (let i = 1; i < points.length; i++) {
        path += ` L ${points[i].x} ${points[i].y}`;
    }
    
    path += ` L ${points[points.length - 1].x} ${bottomY}`;
    path += ' Z';
    
    return path;
};

const handlePointHover = (datasetIndex: number, pointIndex: number) => {
    hoveredPoint.value = { datasetIndex, pointIndex };
};

const clearHover = () => {
    hoveredPoint.value = null;
};

const tooltipData = computed(() => {
    if (!hoveredPoint.value || !props.data) return null;
    
    const { pointIndex } = hoveredPoint.value;
    const label = props.data.labels[pointIndex];
    const values = props.data.datasets.map(dataset => ({
        name: dataset.name,
        value: dataset.data[pointIndex] || 0,
    }));
    
    const position = getPointPosition(pointIndex, props.data.datasets[0].data[pointIndex]);
    
    return { label, values, position };
});
</script>

<template>
    <div class="flex h-full w-full flex-col gap-4">
        <!-- Legend -->
        <div v-if="!loading && datasets.length > 0" class="flex items-center justify-center gap-4">
            <div 
                v-for="dataset in datasets" 
                :key="dataset.name"
                class="flex items-center gap-2"
            >
                <div 
                    class="h-3 w-3 rounded-full" 
                    :style="{ backgroundColor: getColor(dataset.name) }"
                />
                <span class="text-sm text-muted-foreground">{{ dataset.name }}</span>
            </div>
        </div>

        <!-- Chart Area -->
        <div class="flex-1">
            <!-- Loading State -->
            <div v-if="loading" class="flex h-full items-center justify-center">
                <div class="space-y-3 w-full h-full p-4">
                    <div class="h-full w-full animate-pulse rounded-lg bg-muted"></div>
                </div>
            </div>

            <!-- Empty State -->
            <div v-else-if="!chartData.labels.length" class="flex h-full items-center justify-center text-muted-foreground">
                <p>No data available</p>
            </div>

            <!-- Chart -->
            <div v-else class="relative h-full w-full">
                <svg 
                    :viewBox="`0 0 ${CHART_WIDTH} ${CHART_HEIGHT}`"
                    class="h-full w-full"
                    preserveAspectRatio="xMidYMid meet"
                >
                    <!-- Grid Lines -->
                    <g class="grid-lines">
                        <line
                            v-for="(line, index) in gridLines"
                            :key="index"
                            :x1="CHART_PADDING.left"
                            :y1="line.y"
                            :x2="CHART_WIDTH - CHART_PADDING.right"
                            :y2="line.y"
                            stroke="hsl(var(--border))"
                            stroke-width="1"
                            stroke-dasharray="4 4"
                            opacity="0.3"
                        />
                    </g>

                    <!-- Y-axis Labels -->
                    <g class="y-axis-labels">
                        <text
                            v-for="(line, index) in gridLines"
                            :key="index"
                            :x="CHART_PADDING.left - 10"
                            :y="line.y + 4"
                            text-anchor="end"
                            class="text-xs fill-muted-foreground"
                            style="font-size: 11px;"
                        >
                            {{ Math.round(line.value) }}
                        </text>
                    </g>

                    <!-- X-axis Labels -->
                    <g class="x-axis-labels">
                        <text
                            v-for="(label, index) in chartData.labels"
                            :key="index"
                            :x="getPointPosition(index, 0).x"
                            :y="CHART_HEIGHT - CHART_PADDING.bottom + 20"
                            text-anchor="middle"
                            class="text-xs fill-muted-foreground"
                            style="font-size: 10px;"
                        >
                            {{ label.split(' ')[0] }}
                        </text>
                    </g>

                    <!-- Area fills -->
                    <g class="areas">
                        <path
                            v-for="(dataset, datasetIndex) in datasets"
                            :key="`area-${datasetIndex}`"
                            :d="getAreaPath(dataset)"
                            :fill="getColor(dataset.name)"
                            opacity="0.1"
                        />
                    </g>

                    <!-- Lines -->
                    <g class="lines">
                        <path
                            v-for="(dataset, datasetIndex) in datasets"
                            :key="`line-${datasetIndex}`"
                            :d="getLinePath(dataset)"
                            :stroke="getColor(dataset.name)"
                            stroke-width="3"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="transition-all duration-200"
                            :class="{
                                'opacity-30': hoveredPoint && hoveredPoint.datasetIndex !== datasetIndex,
                                'opacity-100': !hoveredPoint || hoveredPoint.datasetIndex === datasetIndex,
                            }"
                        />
                    </g>

                    <!-- Data Points -->
                    <g class="points">
                        <g
                            v-for="(dataset, datasetIndex) in datasets"
                            :key="`points-${datasetIndex}`"
                        >
                            <circle
                                v-for="(value, pointIndex) in dataset.data"
                                :key="`point-${datasetIndex}-${pointIndex}`"
                                :cx="getPointPosition(pointIndex, value).x"
                                :cy="getPointPosition(pointIndex, value).y"
                                :r="hoveredPoint?.datasetIndex === datasetIndex && hoveredPoint?.pointIndex === pointIndex ? 6 : 4"
                                :fill="getColor(dataset.name)"
                                :stroke="'hsl(var(--background))'"
                                stroke-width="2"
                                class="cursor-pointer transition-all duration-200"
                                :class="{
                                    'opacity-30': hoveredPoint && (hoveredPoint.datasetIndex !== datasetIndex || hoveredPoint.pointIndex !== pointIndex),
                                    'opacity-100': !hoveredPoint || (hoveredPoint.datasetIndex === datasetIndex && hoveredPoint.pointIndex === pointIndex),
                                }"
                                @mouseenter="handlePointHover(datasetIndex, pointIndex)"
                                @mouseleave="clearHover"
                            />
                        </g>
                    </g>
                </svg>

                <!-- Tooltip -->
                <div 
                    v-if="tooltipData"
                    class="pointer-events-none absolute z-10 rounded-lg border bg-background p-3 shadow-lg"
                    :style="{
                        left: `${(tooltipData.position.x / CHART_WIDTH) * 100}%`,
                        top: `${(tooltipData.position.y / CHART_HEIGHT) * 100 - 15}%`,
                        transform: 'translate(-50%, -100%)',
                    }"
                >
                    <p class="mb-2 text-sm font-semibold">{{ tooltipData.label }}</p>
                    <div class="space-y-1">
                        <div 
                            v-for="valueData in tooltipData.values"
                            :key="valueData.name"
                            class="flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-2">
                                <div 
                                    class="h-2 w-2 rounded-full" 
                                    :style="{ backgroundColor: getColor(valueData.name) }"
                                />
                                <span class="text-xs">{{ valueData.name }}</span>
                            </div>
                            <Badge variant="outline" class="text-xs">{{ valueData.value }}</Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
