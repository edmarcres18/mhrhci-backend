<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

class DashboardController extends Controller
{
    /**
     * Cache duration in seconds (5 minutes for real-time feel).
     */
    private const CACHE_DURATION = 300;

    /**
     * Get dashboard statistics.
     */
    public function index(): JsonResponse
    {
        try {
            $stats = Cache::remember('dashboard_stats', self::CACHE_DURATION, function () {
                return [
                    'users' => $this->getUserStats(),
                    'blogs' => $this->getBlogStats(),
                    'products' => $this->getProductStats(),
                    'activity' => $this->getActivityStats(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $stats,
                'cached_at' => now()->toIso8601String(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch dashboard statistics',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get overview chart data (monthly trends).
     */
    public function overview(): JsonResponse
    {
        try {
            $data = Cache::remember('dashboard_overview', self::CACHE_DURATION, function () {
                $months = $this->getLast12Months();

                return [
                    'labels' => $months->pluck('label')->toArray(),
                    'datasets' => [
                        [
                            'name' => 'Users',
                            'data' => $this->getMonthlyData(User::class, $months),
                        ],
                        [
                            'name' => 'Blogs',
                            'data' => $this->getMonthlyData(Blog::class, $months),
                        ],
                        [
                            'name' => 'Products',
                            'data' => $this->getMonthlyData(Product::class, $months),
                        ],
                    ],
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch overview data',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get recent activities (latest created items).
     */
    public function recentActivity(): JsonResponse
    {
        try {
            $data = Cache::remember('dashboard_recent_activity', self::CACHE_DURATION, function () {
                $recentUsers = User::latest()
                    ->take(5)
                    ->get(['id', 'name', 'email', 'created_at'])
                    ->map(function ($user) {
                        return [
                            'id' => $user->id,
                            'name' => $user->name,
                            'email' => $user->email,
                            'avatar' => $this->getAvatarUrl($user->email),
                            'created_at' => $user->created_at->diffForHumans(),
                            'type' => 'user',
                        ];
                    });

                $recentBlogs = Blog::latest()
                    ->take(5)
                    ->get(['id', 'title', 'created_at'])
                    ->map(function ($blog) {
                        return [
                            'id' => $blog->id,
                            'title' => $blog->title,
                            'created_at' => $blog->created_at->diffForHumans(),
                            'type' => 'blog',
                        ];
                    });

                $recentProducts = Product::latest()
                    ->take(5)
                    ->get(['id', 'name', 'created_at'])
                    ->map(function ($product) {
                        return [
                            'id' => $product->id,
                            'name' => $product->name,
                            'created_at' => $product->created_at->diffForHumans(),
                            'type' => 'product',
                        ];
                    });

                return [
                    'users' => $recentUsers,
                    'blogs' => $recentBlogs,
                    'products' => $recentProducts,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch recent activity',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Clear dashboard cache manually.
     */
    public function clearCache(): JsonResponse
    {
        try {
            Cache::forget('dashboard_stats');
            Cache::forget('dashboard_overview');
            Cache::forget('dashboard_recent_activity');

            return response()->json([
                'success' => true,
                'message' => 'Dashboard cache cleared successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to clear dashboard cache',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Get user statistics.
     */
    private function getUserStats(): array
    {
        $total = User::count();
        $lastMonth = User::where('created_at', '>=', now()->subMonth())->count();
        $lastWeek = User::where('created_at', '>=', now()->subWeek())->count();

        // Calculate percentage change from previous month
        $twoMonthsAgo = User::whereBetween('created_at', [
            now()->subMonths(2),
            now()->subMonth(),
        ])->count();

        $percentageChange = $twoMonthsAgo > 0
            ? round((($lastMonth - $twoMonthsAgo) / $twoMonthsAgo) * 100, 1)
            : ($lastMonth > 0 ? 100 : 0);

        return [
            'total' => $total,
            'last_month' => $lastMonth,
            'last_week' => $lastWeek,
            'percentage_change' => $percentageChange,
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get blog statistics.
     */
    private function getBlogStats(): array
    {
        $total = Blog::count();
        $lastMonth = Blog::where('created_at', '>=', now()->subMonth())->count();
        $lastWeek = Blog::where('created_at', '>=', now()->subWeek())->count();

        // Calculate percentage change from previous month
        $twoMonthsAgo = Blog::whereBetween('created_at', [
            now()->subMonths(2),
            now()->subMonth(),
        ])->count();

        $percentageChange = $twoMonthsAgo > 0
            ? round((($lastMonth - $twoMonthsAgo) / $twoMonthsAgo) * 100, 1)
            : ($lastMonth > 0 ? 100 : 0);

        return [
            'total' => $total,
            'last_month' => $lastMonth,
            'last_week' => $lastWeek,
            'percentage_change' => $percentageChange,
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get product statistics.
     */
    private function getProductStats(): array
    {
        $total = Product::count();
        $lastMonth = Product::where('created_at', '>=', now()->subMonth())->count();
        $lastWeek = Product::where('created_at', '>=', now()->subWeek())->count();

        // Calculate percentage change from previous month
        $twoMonthsAgo = Product::whereBetween('created_at', [
            now()->subMonths(2),
            now()->subMonth(),
        ])->count();

        $percentageChange = $twoMonthsAgo > 0
            ? round((($lastMonth - $twoMonthsAgo) / $twoMonthsAgo) * 100, 1)
            : ($lastMonth > 0 ? 100 : 0);

        return [
            'total' => $total,
            'last_month' => $lastMonth,
            'last_week' => $lastWeek,
            'percentage_change' => $percentageChange,
            'trend' => $percentageChange >= 0 ? 'up' : 'down',
        ];
    }

    /**
     * Get activity statistics (combined recent activity).
     */
    private function getActivityStats(): array
    {
        $now = now();
        $lastHour = $now->copy()->subHour();

        $usersLastHour = User::where('created_at', '>=', $lastHour)->count();
        $blogsLastHour = Blog::where('created_at', '>=', $lastHour)->count();
        $productsLastHour = Product::where('created_at', '>=', $lastHour)->count();

        $totalActivity = $usersLastHour + $blogsLastHour + $productsLastHour;

        // Calculate activity from previous hour for comparison
        $twoHoursAgo = $now->copy()->subHours(2);
        $previousHourActivity =
            User::whereBetween('created_at', [$twoHoursAgo, $lastHour])->count() +
            Blog::whereBetween('created_at', [$twoHoursAgo, $lastHour])->count() +
            Product::whereBetween('created_at', [$twoHoursAgo, $lastHour])->count();

        return [
            'total' => $totalActivity,
            'users' => $usersLastHour,
            'blogs' => $blogsLastHour,
            'products' => $productsLastHour,
            'previous_hour' => $previousHourActivity,
            'change' => $totalActivity - $previousHourActivity,
        ];
    }

    /**
     * Get last 12 months data structure.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getLast12Months()
    {
        return collect(range(11, 0))->map(function ($monthsAgo) {
            $date = now()->subMonths($monthsAgo);

            return [
                'label' => $date->format('M Y'),
                'month' => $date->month,
                'year' => $date->year,
                'start' => $date->startOfMonth()->toDateString(),
                'end' => $date->endOfMonth()->toDateString(),
            ];
        });
    }

    /**
     * Get monthly data for a specific model.
     *
     * @param  \Illuminate\Support\Collection  $months
     */
    private function getMonthlyData(string $model, $months): array
    {
        return $months->map(function ($month) use ($model) {
            return $model::whereBetween('created_at', [
                $month['start'],
                $month['end'],
            ])->count();
        })->toArray();
    }

    /**
     * Generate Gravatar URL for email.
     */
    private function getAvatarUrl(string $email): string
    {
        $hash = md5(strtolower(trim($email)));

        return "https://www.gravatar.com/avatar/{$hash}?d=mp&s=40";
    }

    /**
     * Get latest database backup info (System Admin only).
     */
    public function latestBackup(): JsonResponse
    {
        try {
            // Check if user is system admin
            $currentUser = auth()->user();
            if (! $currentUser || ! $currentUser->isSystemAdmin()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. System Admin access required.',
                ], 403);
            }

            $data = Cache::remember('dashboard_latest_backup', self::CACHE_DURATION, function () {
                $backupPath = storage_path('app'.DIRECTORY_SEPARATOR.'backups'.DIRECTORY_SEPARATOR.'database');

                if (! File::exists($backupPath)) {
                    return null;
                }

                $files = File::files($backupPath);
                $backups = [];

                foreach ($files as $file) {
                    if (str_ends_with($file->getFilename(), '.sql')) {
                        $backups[] = [
                            'filename' => $file->getFilename(),
                            'size' => $this->formatBytes($file->getSize()),
                            'size_bytes' => $file->getSize(),
                            'created_at' => date('Y-m-d H:i:s', $file->getMTime()),
                            'created_at_human' => \Carbon\Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
                            'timestamp' => $file->getMTime(),
                        ];
                    }
                }

                // Sort by timestamp descending (newest first)
                usort($backups, function ($a, $b) {
                    return $b['timestamp'] <=> $a['timestamp'];
                });

                return $backups[0] ?? null;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch latest backup',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Format bytes to human-readable format.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision).' '.$units[$i];
    }
}
