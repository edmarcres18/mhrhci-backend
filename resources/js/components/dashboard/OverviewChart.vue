<script setup lang="ts">
import { computed, ref, onMounted, onUnmounted } from 'vue';
import type { DashboardOverview } from '@/types';
import { Badge } from '@/components/ui/badge';
import { BarChart3 } from 'lucide-vue-next';

interface Props {
    data?: DashboardOverview | null;
    loading?: boolean;
}

const props = defineProps<Props>();
const hoveredPoint = ref<{ datasetIndex: number; pointIndex: number } | null>(null);
const isMobile = ref(false);

// Chart dimensions - responsive
const CHART_WIDTH = 800;
const CHART_HEIGHT = 350;

// Responsive padding
const CHART_PADDING = computed(() => ({
    top: 20,
    right: isMobile.value ? 10 : 20,
    bottom: 40,
    left: isMobile.value ? 40 : 50
}));

// Check if mobile on mount and window resize
const checkMobile = () => {
    isMobile.value = window.innerWidth < 768;
};

onMounted(() => {
    checkMobile();
    window.addEventListener('resize', checkMobile);
});

onUnmounted(() => {
    window.removeEventListener('resize', checkMobile);
});

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
    const padding = CHART_PADDING.value;
    for (let i = 0; i <= 4; i++) {
        lines.push({
            value: minValue.value + step * i,
            y: CHART_HEIGHT - padding.bottom - (step * i / (maxValue.value - minValue.value)) * (CHART_HEIGHT - padding.top - padding.bottom)
        });
    }
    return lines.reverse();
});

// Convert data point to coordinates
const getPointPosition = (dataIndex: number, value: number) => {
    const labels = chartData.value.labels;
    const padding = CHART_PADDING.value;
    const chartWidth = CHART_WIDTH - padding.left - padding.right;
    const chartHeight = CHART_HEIGHT - padding.top - padding.bottom;
    
    const x = padding.left + (dataIndex / Math.max(labels.length - 1, 1)) * chartWidth;
    const y = padding.top + chartHeight - ((value - minValue.value) / (maxValue.value - minValue.value)) * chartHeight;
    
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
    
    const bottomY = CHART_HEIGHT - CHART_PADDING.value.bottom;
    
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

// Touch event handlers for mobile
const handleTouch = (event: TouchEvent, datasetIndex: number, pointIndex: number) => {
    event.preventDefault();
    handlePointHover(datasetIndex, pointIndex);
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
    
    // Calculate smart tooltip positioning to prevent overflow
    const xPercent = (position.x / CHART_WIDTH) * 100;
    const yPercent = (position.y / CHART_HEIGHT) * 100;
    
    // Adjust position if too close to edges
    const adjustedX = xPercent < 15 ? 15 : xPercent > 85 ? 85 : xPercent;
    const adjustedY = yPercent < 20 ? yPercent + 20 : yPercent - 15;
    
    return { 
        label, 
        values, 
        position,
        xPercent: adjustedX,
        yPercent: adjustedY
    };
});

// Computed padding values for template usage
const paddingLeft = computed(() => CHART_PADDING.value.left);
const paddingRight = computed(() => CHART_PADDING.value.right);
const paddingBottom = computed(() => CHART_PADDING.value.bottom);
</script>

<template>
    <div class="flex h-full w-full flex-col gap-3 sm:gap-4">
        <!-- Legend - Responsive wrapping -->
        <div v-if="!loading && datasets.length > 0" class="flex flex-wrap items-center justify-center gap-3 sm:gap-4 px-2">
            <div 
                v-for="dataset in datasets" 
                :key="dataset.name"
                class="flex items-center gap-1.5 sm:gap-2 transition-opacity hover:opacity-70"
            >
                <div 
                    class="h-2.5 w-2.5 sm:h-3 sm:w-3 rounded-full flex-shrink-0 ring-2 ring-background shadow-sm" 
                    :style="{ backgroundColor: getColor(dataset.name) }"
                />
                <span class="text-xs sm:text-sm text-muted-foreground font-medium whitespace-nowrap">
                    {{ dataset.name }}
                </span>
            </div>
        </div>

        <!-- Chart Area -->
        <div class="flex-1 min-h-0">
            <!-- Loading State -->
            <div v-if="loading" class="flex h-full items-center justify-center p-4">
                <div class="w-full h-full space-y-2 sm:space-y-3">
                    <!-- Skeleton loader with animation -->
                    <div class="h-8 w-3/4 mx-auto animate-pulse rounded bg-muted/50"></div>
                    <div class="h-full w-full animate-pulse rounded-lg bg-gradient-to-br from-muted/30 via-muted/50 to-muted/30"></div>
                </div>
            </div>

            <!-- Empty State with icon -->
            <div v-else-if="!chartData.labels.length" class="flex h-full flex-col items-center justify-center gap-3 text-muted-foreground p-4">
                <BarChart3 class="h-12 w-12 sm:h-16 sm:w-16 opacity-20" />
                <div class="text-center space-y-1">
                    <p class="text-sm sm:text-base font-medium">No data available</p>
                    <p class="text-xs sm:text-sm opacity-70">Chart data will appear here once available</p>
                </div>
            </div>

            <!-- Chart -->
            <div v-else class="relative h-full w-full" role="img" :aria-label="`Overview chart showing trends for ${datasets.map(d => d.name).join(', ')}`">
                <svg 
                    :viewBox="`0 0 ${CHART_WIDTH} ${CHART_HEIGHT}`"
                    class="h-full w-full select-none"
                    preserveAspectRatio="xMidYMid meet"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <!-- Grid Lines -->
                    <g class="grid-lines">
                        <line
                            v-for="(line, index) in gridLines"
                            :key="index"
                            :x1="paddingLeft"
                            :y1="line.y"
                            :x2="CHART_WIDTH - paddingRight"
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
                            :x="paddingLeft - 10"
                            :y="line.y + 4"
                            text-anchor="end"
                            class="text-xs fill-muted-foreground"
                            :style="{ fontSize: isMobile ? '9px' : '11px' }"
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
                            :y="CHART_HEIGHT - paddingBottom + 20"
                            text-anchor="middle"
                            class="text-xs fill-muted-foreground"
                            :style="{ fontSize: isMobile ? '8px' : '10px' }"
                        >
                            {{ isMobile ? label.substring(0, 3) : label.split(' ')[0] }}
                        </text>
                    </g>

                    <!-- Area fills -->
                    <g class="areas">
                        <path
                            v-for="(dataset, datasetIndex) in datasets"
                            :key="`area-${datasetIndex}`"
                            :d="getAreaPath(dataset)"
                            :fill="getColor(dataset.name)"
                            :opacity="hoveredPoint?.datasetIndex === datasetIndex ? 0.15 : 0.08"
                            class="transition-opacity duration-300"
                        />
                    </g>

                    <!-- Lines -->
                    <g class="lines">
                        <path
                            v-for="(dataset, datasetIndex) in datasets"
                            :key="`line-${datasetIndex}`"
                            :d="getLinePath(dataset)"
                            :stroke="getColor(dataset.name)"
                            :stroke-width="isMobile ? 2.5 : 3"
                            fill="none"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="transition-all duration-300 ease-in-out"
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
                                :r="hoveredPoint?.datasetIndex === datasetIndex && hoveredPoint?.pointIndex === pointIndex ? (isMobile ? 7 : 6) : (isMobile ? 5 : 4)"
                                :fill="getColor(dataset.name)"
                                stroke="hsl(var(--background))"
                                :stroke-width="isMobile ? 2.5 : 2"
                                class="cursor-pointer transition-all duration-300 ease-out touch-none select-none"
                                role="button"
                                :aria-label="`${dataset.name}: ${value} at ${chartData.labels[pointIndex]}`"
                                :class="{
                                    'opacity-30': hoveredPoint && (hoveredPoint.datasetIndex !== datasetIndex || hoveredPoint.pointIndex !== pointIndex),
                                    'opacity-100 drop-shadow-lg': !hoveredPoint || (hoveredPoint.datasetIndex === datasetIndex && hoveredPoint.pointIndex === pointIndex),
                                }"
                                @mouseenter="handlePointHover(datasetIndex, pointIndex)"
                                @mouseleave="clearHover"
                                @touchstart="(e) => handleTouch(e, datasetIndex, pointIndex)"
                                @touchend="clearHover"
                            />
                        </g>
                    </g>
                </svg>

                <!-- Tooltip - Responsive and Smart Positioning -->
                <div 
                    v-if="tooltipData"
                    role="tooltip"
                    class="pointer-events-none absolute z-20 rounded-lg border border-border/50 bg-background/95 backdrop-blur-sm p-2.5 sm:p-3 shadow-xl transition-all duration-200 ease-out max-w-[200px] sm:max-w-none animate-in fade-in-0 zoom-in-95"
                    :style="{
                        left: `${tooltipData.xPercent}%`,
                        top: `${tooltipData.yPercent}%`,
                        transform: 'translate(-50%, -100%)',
                    }"
                >
                    <p class="mb-1.5 sm:mb-2 text-xs sm:text-sm font-semibold truncate">{{ tooltipData.label }}</p>
                    <div class="space-y-1">
                        <div 
                            v-for="valueData in tooltipData.values"
                            :key="valueData.name"
                            class="flex items-center justify-between gap-2 sm:gap-4"
                        >
                            <div class="flex items-center gap-1.5 sm:gap-2 min-w-0">
                                <div 
                                    class="h-2 w-2 rounded-full flex-shrink-0 ring-1 ring-background" 
                                    :style="{ backgroundColor: getColor(valueData.name) }"
                                />
                                <span class="text-xs truncate">{{ valueData.name }}</span>
                            </div>
                            <Badge variant="outline" class="text-xs flex-shrink-0 font-semibold">{{ valueData.value }}</Badge>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
