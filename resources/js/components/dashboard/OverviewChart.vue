<script setup lang="ts">
import { computed, ref } from 'vue';
import type { DashboardOverview } from '@/types';
import { Badge } from '@/components/ui/badge';

interface Props {
    data?: DashboardOverview | null;
    loading?: boolean;
}

const props = defineProps<Props>();
const hoveredBar = ref<number | null>(null);

const chartData = computed(() => {
    if (!props.data) return [];

    const { labels, datasets } = props.data;
    
    return labels.map((label, index) => ({
        label,
        values: datasets.map(dataset => ({
            name: dataset.name,
            value: dataset.data[index] || 0,
        })),
        total: datasets.reduce((sum, dataset) => sum + (dataset.data[index] || 0), 0),
    }));
});

const maxValue = computed(() => {
    if (chartData.value.length === 0) return 1;
    return Math.max(...chartData.value.map(d => d.total), 1);
});

const datasets = computed(() => props.data?.datasets || []);

const colorMap = {
    'Users': 'hsl(var(--primary))',
    'Blogs': 'hsl(var(--chart-2))',
    'Products': 'hsl(var(--chart-3))',
};

const getColor = (name: string) => colorMap[name as keyof typeof colorMap] || 'hsl(var(--muted))';
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
                    class="h-3 w-3 rounded-sm" 
                    :style="{ backgroundColor: getColor(dataset.name) }"
                />
                <span class="text-sm text-muted-foreground">{{ dataset.name }}</span>
            </div>
        </div>

        <!-- Chart Area -->
        <div class="flex-1">
            <div v-if="loading" class="flex h-full items-end justify-between gap-2">
                <div
                    v-for="i in 12"
                    :key="i"
                    class="flex flex-1 flex-col items-center gap-2"
                >
                    <div class="relative flex h-full w-full items-end">
                        <div class="w-full animate-pulse rounded-t-sm bg-muted" :style="{ height: `${Math.random() * 60 + 20}%` }" />
                    </div>
                    <div class="h-3 w-8 animate-pulse rounded bg-muted"></div>
                </div>
            </div>
            <div v-else-if="chartData.length === 0" class="flex h-full items-center justify-center text-muted-foreground">
                <p>No data available</p>
            </div>
            <div v-else class="relative flex h-full items-end justify-between gap-2">
                <!-- Hover Tooltip -->
                <div 
                    v-if="hoveredBar !== null && chartData[hoveredBar]"
                    class="absolute left-1/2 top-4 z-10 -translate-x-1/2 rounded-lg border bg-background p-3 shadow-lg"
                >
                    <p class="mb-2 font-semibold">{{ chartData[hoveredBar].label }}</p>
                    <div class="space-y-1">
                        <div 
                            v-for="valueData in chartData[hoveredBar].values"
                            :key="valueData.name"
                            class="flex items-center justify-between gap-4"
                        >
                            <div class="flex items-center gap-2">
                                <div 
                                    class="h-2 w-2 rounded-full" 
                                    :style="{ backgroundColor: getColor(valueData.name) }"
                                />
                                <span class="text-sm">{{ valueData.name }}</span>
                            </div>
                            <Badge variant="outline">{{ valueData.value }}</Badge>
                        </div>
                    </div>
                    <div class="mt-2 border-t pt-2">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium">Total</span>
                            <Badge>{{ chartData[hoveredBar].total }}</Badge>
                        </div>
                    </div>
                </div>

                <!-- Bars -->
                <div
                    v-for="(data, index) in chartData"
                    :key="data.label"
                    class="group flex flex-1 flex-col items-center gap-2"
                    @mouseenter="hoveredBar = index"
                    @mouseleave="hoveredBar = null"
                >
                    <div class="relative flex h-full w-full items-end">
                        <div class="flex h-full w-full flex-col justify-end gap-0.5">
                            <div
                                v-for="(valueData, vIndex) in data.values"
                                :key="valueData.name"
                                class="w-full cursor-pointer transition-all duration-200"
                                :class="{
                                    'rounded-t-sm': vIndex === data.values.length - 1,
                                    'opacity-80 shadow-md': hoveredBar === index,
                                    'opacity-100': hoveredBar !== index,
                                }"
                                :style="{
                                    height: `${(valueData.value / maxValue) * 100}%`,
                                    backgroundColor: getColor(valueData.name),
                                }"
                            />
                        </div>
                    </div>
                    <span 
                        class="text-xs transition-colors"
                        :class="hoveredBar === index ? 'font-semibold text-foreground' : 'text-muted-foreground'"
                    >
                        {{ data.label.split(' ')[0] }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
