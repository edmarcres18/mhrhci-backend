# Dashboard Quick Start Guide

## 🚀 Quick Setup

The dashboard is **production-ready** and requires no additional configuration. It automatically connects to your existing User, Blog, and Product models.

## 📊 What You Get

### Real-Time Statistics
- **Total Users** - with monthly growth percentage
- **Total Blogs** - with monthly growth percentage  
- **Total Products** - with monthly growth percentage
- **Active Now** - activity in the last hour

### Visual Analytics
- **Overview Chart** - 12-month trend visualization for all entities
- **Recent Activity** - Latest 5 additions across all models

### Auto-Refresh
- Stats refresh every **30 seconds**
- Overview refreshes every **60 seconds**
- Recent activity refreshes every **15 seconds**

## 🔗 API Endpoints

All endpoints require authentication via Laravel Sanctum:

```
GET  /api/dashboard/stats            - Main statistics
GET  /api/dashboard/overview         - Monthly trends
GET  /api/dashboard/recent-activity  - Recent additions
POST /api/dashboard/clear-cache      - Clear server cache
```

## 💻 Frontend Usage

### In Dashboard Page (Already Implemented)

```vue
<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard';

const {
    stats,          // Dashboard statistics
    overview,       // Chart data
    recentActivity, // Recent items
    isLoadingStats, // Loading state
    refresh,        // Manual refresh function
} = useDashboard({ autoRefresh: true });
</script>
```

### Use in Other Components

```vue
<script setup lang="ts">
import { useDashboard } from '@/composables/useDashboard';

// Disable auto-refresh if not needed
const { stats } = useDashboard({ autoRefresh: false });
</script>

<template>
    <div v-if="stats">
        Total Users: {{ stats.users.total }}
    </div>
</template>
```

## 🎨 Component Usage

### StatCard

```vue
<StatCard
    title="Total Users"
    :value="stats?.users.total ?? 0"
    :change="`${stats?.users.percentage_change ?? 0}% from last month`"
    :trend="stats?.users.trend"
    :loading="isLoadingStats"
    :icon="Users"
/>
```

### OverviewChart

```vue
<OverviewChart 
    :data="overview" 
    :loading="isLoadingOverview"
/>
```

### RecentSales (Activity)

```vue
<RecentSales 
    :data="recentActivity" 
    :loading="isLoadingActivity"
/>
```

## 🔧 Customization

### Change Refresh Intervals

Edit `resources/js/composables/useDashboard.ts`:

```typescript
const STATS_REFRESH_INTERVAL = 30000;    // 30 seconds
const OVERVIEW_REFRESH_INTERVAL = 60000; // 60 seconds
const ACTIVITY_REFRESH_INTERVAL = 15000; // 15 seconds
```

### Change Cache Duration

Edit `app/Http/Controllers/DashboardController.php`:

```php
private const CACHE_DURATION = 300; // 5 minutes (300 seconds)
```

## 🐛 Troubleshooting

### Dashboard shows zeros
- Ensure you have data in your database (users, blogs, products)
- Check authentication is working
- Run seeders: `php artisan db:seed`

### Data not refreshing
- Check browser console for errors
- Verify API endpoints are accessible
- Clear cache: Click refresh button or call `/api/dashboard/clear-cache`

### Performance issues
- Increase `CACHE_DURATION` to 600 (10 minutes)
- Decrease auto-refresh frequency
- Enable Redis for production caching

## 📦 Files Created/Modified

### Backend
- ✅ `app/Http/Controllers/DashboardController.php` - Main controller
- ✅ `routes/api.php` - API routes added

### Frontend
- ✅ `resources/js/composables/useDashboard.ts` - Data fetching logic
- ✅ `resources/js/types/index.d.ts` - TypeScript types
- ✅ `resources/js/pages/Dashboard.vue` - Dashboard page
- ✅ `resources/js/components/dashboard/StatCard.vue` - Updated
- ✅ `resources/js/components/dashboard/OverviewChart.vue` - Updated
- ✅ `resources/js/components/dashboard/RecentSales.vue` - Updated

## 🧪 Testing

### Manual Test
1. Login to your application
2. Navigate to `/dashboard`
3. Verify all stats show correct numbers
4. Click refresh button
5. Watch for loading states
6. Wait 30 seconds for auto-refresh

### API Test (Postman/cURL)

```bash
# Get stats (replace YOUR_TOKEN)
curl http://localhost/api/dashboard/stats \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## 🚀 Production Checklist

- ✅ Authentication working
- ✅ Database has sample data
- ✅ Cache driver configured (Redis recommended)
- ✅ Rate limiting configured
- ✅ HTTPS enabled
- ✅ Error logging enabled

## 🎯 Next Steps

The dashboard is complete and production-ready! You can now:

1. **Add more metrics** - Extend the controller with custom statistics
2. **Customize styling** - Modify Tailwind classes in components
3. **Add filters** - Implement date range filtering
4. **Export data** - Add CSV/PDF export functionality
5. **WebSocket updates** - Replace polling with real-time WebSocket updates

---

**Status**: ✅ Production Ready  
**Auto-refresh**: ✅ Enabled  
**Caching**: ✅ Enabled (5 min)  
**Error Handling**: ✅ Implemented  
**Loading States**: ✅ Implemented  
**Type Safety**: ✅ Full TypeScript support
