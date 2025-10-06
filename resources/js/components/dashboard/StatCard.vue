<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { TrendingUp, TrendingDown } from 'lucide-vue-next';
import { Component, computed } from 'vue';

interface Props {
    title: string;
    value: string | number;
    change?: string;
    icon?: Component;
    trend?: 'up' | 'down';
    loading?: boolean;
}

const props = defineProps<Props>();

const formattedValue = computed(() => {
    return typeof props.value === 'number' ? props.value.toLocaleString() : props.value;
});
</script>

<template>
    <Card>
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
                {{ title }}
            </CardTitle>
            <component v-if="icon" :is="icon" class="size-4 text-muted-foreground" />
        </CardHeader>
        <CardContent>
            <div v-if="loading" class="space-y-2">
                <div class="h-8 w-24 animate-pulse rounded bg-muted"></div>
                <div class="h-4 w-32 animate-pulse rounded bg-muted"></div>
            </div>
            <div v-else>
                <div class="text-2xl font-bold">{{ formattedValue }}</div>
                <div v-if="change" class="mt-1 flex items-center gap-1 text-xs">
                    <TrendingUp
                        v-if="trend === 'up'"
                        class="size-3 text-green-600"
                    />
                    <TrendingDown
                        v-else-if="trend === 'down'"
                        class="size-3 text-red-600"
                    />
                    <span
                        :class="{
                            'text-green-600': trend === 'up',
                            'text-red-600': trend === 'down',
                            'text-muted-foreground': !trend,
                        }"
                    >
                        {{ change }}
                    </span>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
