<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\NewsletterSubscription;
use App\Notifications\NewsletterAnnouncementCreated;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of the resource (Web).
     */
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $allowed = [10, 25, 50, 100];
        if (! in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

        $query = Announcement::query();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $announcements = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($announcement) {
                return [
                    'id' => $announcement->id,
                    'title' => $announcement->title,
                    'description' => $announcement->description,
                    'created_at' => optional($announcement->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Announcements/Index', [
            'announcements' => $announcements,
            'filters' => [
                'search' => $search,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Announcements/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $announcement = Announcement::create([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        $this->notifySubscribersAboutAnnouncement($announcement);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Announcement $announcement): Response
    {
        return Inertia::render('Announcements/Show', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => $announcement->description,
                'created_at' => optional($announcement->created_at)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Announcement $announcement): Response
    {
        return Inertia::render('Announcements/Edit', [
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'description' => $announcement->description,
                'created_at' => optional($announcement->created_at)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $announcement->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
        ]);

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Announcement $announcement)
    {
        $user = auth()->user();
        if (! $user || ! $user->hasAdminPrivileges()) {
            abort(403, 'You do not have permission to delete announcements.');
        }

        $announcement->delete();

        return redirect()
            ->route('announcements.index')
            ->with('success', 'Announcement deleted successfully.');
    }

    /**
     * API: List announcements (public).
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            ]);

            $query = Announcement::query()->latest('created_at');

            if (isset($validated['limit'])) {
                $query->limit($validated['limit']);
            }

            $items = $query->get()->map(function (Announcement $item) {
                return [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'created_at' => $item->created_at?->toIso8601String(),
                    'updated_at' => $item->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'count' => $items->count(),
                    'limit' => $validated['limit'] ?? null,
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Announcement apiIndex error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching announcements',
            ], 500);
        }
    }

    /**
     * API: Get the latest 10 announcements (public).
     */
    public function apiLatest(): JsonResponse
    {
        try {
            $items = Announcement::query()
                ->latest('created_at')
                ->limit(10)
                ->get()
                ->map(function (Announcement $item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'description' => $item->description,
                        'created_at' => $item->created_at?->toIso8601String(),
                        'updated_at' => $item->updated_at?->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'count' => $items->count(),
                    'limit' => 10,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Announcement apiLatest error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching latest announcements',
            ], 500);
        }
    }

    /**
     * API: Show a single announcement (public).
     */
    public function showApi(int $id): JsonResponse
    {
        try {
            $item = Announcement::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Announcement not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'title' => $item->title,
                    'description' => $item->description,
                    'created_at' => $item->created_at?->toIso8601String(),
                    'updated_at' => $item->updated_at?->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('Announcement showApi error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the announcement',
            ], 500);
        }
    }

    /**
     * Notify newsletter subscribers when a new announcement is created.
     */
    protected function notifySubscribersAboutAnnouncement(Announcement $announcement): void
    {
        try {
            NewsletterSubscription::chunk(200, function ($subscribers) use ($announcement) {
                Notification::send($subscribers, new NewsletterAnnouncementCreated($announcement));
            });
        } catch (\Throwable $e) {
            Log::error('Newsletter announcement notify failed', [
                'announcement_id' => $announcement->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
