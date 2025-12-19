<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import type { RecentActivity } from '@/types';
import { computed } from 'vue';

interface Props {
    data?: RecentActivity | null;
    loading?: boolean;
}

const props = defineProps<Props>();

const activities = computed(() => {
    if (!props.data) return [];
    
    // Combine all activities, sort by timestamp desc, and take top 5
    const allActivities = [
        ...props.data.users,
        ...props.data.blogs,
        ...props.data.products,
        ...props.data.principals,
    ];
    
    return allActivities
        .sort((a, b) => (b.timestamp || 0) - (a.timestamp || 0))
        .slice(0, 5);
});

const getInitials = (name: string) => {
    return name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .toUpperCase();
};

const getBadgeVariant = (type: string) => {
    switch (type) {
        case 'user':
            return 'default';
        case 'blog':
            return 'secondary';
        case 'product':
            return 'outline';
        case 'principal':
            return 'default';
        default:
            return 'default';
    }
};

const getDisplayName = (item: any) => {
    if (item.type === 'user') return item.name;
    if (item.type === 'blog') return item.title;
    if (item.type === 'product') return item.name;
    if (item.type === 'principal') return item.name;
    return 'Unknown';
};

const getSubtitle = (item: any) => {
    if (item.type === 'user') return item.email;
    if (item.type === 'principal') return item.action ? `Principal ${item.action}` : 'Principal';
    return null;
};
</script>

<template>
    <div class="space-y-8">
        <div v-if="loading" class="space-y-8">
            <div v-for="i in 5" :key="i" class="flex items-center">
                <div class="size-9 animate-pulse rounded-full bg-muted"></div>
                <div class="ml-4 flex-1 space-y-2">
                    <div class="h-4 w-32 animate-pulse rounded bg-muted"></div>
                    <div class="h-3 w-24 animate-pulse rounded bg-muted"></div>
                </div>
                <div class="h-5 w-16 animate-pulse rounded bg-muted"></div>
            </div>
        </div>
        <div v-else-if="activities.length === 0" class="flex items-center justify-center py-8 text-muted-foreground">
            <p>No recent activity</p>
        </div>
        <div v-else class="space-y-8">
            <div
                v-for="item in activities"
                :key="`${item.type}-${item.id}`"
                class="flex items-center"
            >
                <Avatar class="size-9">
                    <AvatarImage 
                        v-if="item.type === 'user' && item.avatar" 
                        :src="item.avatar" 
                        :alt="item.name" 
                    />
                    <AvatarFallback>
                        {{ getInitials(getDisplayName(item)) }}
                    </AvatarFallback>
                </Avatar>
                <div class="ml-4 flex-1 space-y-1">
                    <p class="text-sm font-medium leading-none">
                        {{ getDisplayName(item) }}
                    </p>
                    <div class="flex items-center gap-2">
                        <Badge :variant="getBadgeVariant(item.type)" class="text-xs">
                            {{ item.type }}
                        </Badge>
                        <p class="text-xs text-muted-foreground" v-if="getSubtitle(item)">
                            {{ getSubtitle(item) }}
                        </p>
                    </div>
                </div>
                <div class="ml-auto text-xs text-muted-foreground">
                    {{ item.created_at }}
                </div>
            </div>
        </div>
    </div>
</template>
