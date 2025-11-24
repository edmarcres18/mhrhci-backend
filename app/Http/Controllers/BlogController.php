<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $perPage = (int) $request->query('perPage', 10);
        $allowed = [10, 25, 50, 100];
        if (! in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

        $query = Blog::query();
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            });
        }

        $blogs = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'content' => $blog->content,
                    'images' => $blog->images,
                    'created_at' => optional($blog->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Blogs/Index', [
            'blogs' => $blogs,
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
        return Inertia::render('Blogs/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('blogs', 'public');
            }
        }

        $blog = Blog::create([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'images' => $imagePaths,
        ]);

        return redirect()
            ->route('blogs.index')
            ->with('success', 'Blog created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): Response
    {
        return Inertia::render('Blogs/Show', [
            'blog' => $blog,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog): Response
    {
        return Inertia::render('Blogs/Edit', [
            'blog' => $blog,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'keepExistingImages' => ['nullable', 'boolean'],
        ]);

        $keepExisting = filter_var($request->input('keepExistingImages', true), FILTER_VALIDATE_BOOLEAN);
        $imagePaths = $keepExisting ? ($blog->images ?? []) : [];

        if ($request->hasFile('images')) {
            if (! $keepExisting) {
                foreach (($blog->images ?? []) as $old) {
                    if ($old && Storage::disk('public')->exists($old)) {
                        Storage::disk('public')->delete($old);
                    }
                }
            }
            foreach ($request->file('images') as $image) {
                if (count($imagePaths) >= 5) {
                    break;
                }
                $imagePaths[] = $image->store('blogs', 'public');
            }
        }

        $blog->update([
            'title' => $validated['title'],
            'content' => $validated['content'] ?? null,
            'images' => array_values($imagePaths),
        ]);

        return redirect()
            ->route('blogs.index')
            ->with('success', 'Blog updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog)
    {
        // Only System Admin and Admin can delete blogs
        $user = auth()->user();
        if (! $user || ! $user->hasAdminPrivileges()) {
            abort(403, 'You do not have permission to delete blogs.');
        }

        foreach (($blog->images ?? []) as $old) {
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        $blog->delete();

        return redirect()
            ->route('blogs.index')
            ->with('success', 'Blog deleted successfully.');
    }

    /**
     * API: Get all blogs with pagination, filtering, and caching.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            // Validate input parameters
            $validated = $request->validate([
                'search' => ['nullable', 'string', 'max:255'],
                'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
                'page' => ['nullable', 'integer', 'min:1'],
                'sortBy' => ['nullable', 'string', 'in:created_at,updated_at,title'],
                'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            ]);

            $search = $validated['search'] ?? '';
            $perPage = $validated['perPage'] ?? 10;
            $sortBy = $validated['sortBy'] ?? 'created_at';
            $sortOrder = $validated['sortOrder'] ?? 'desc';

            // Create cache key based on request parameters
            $cacheKey = 'blogs_api_'.md5(json_encode([
                'search' => $search,
                'perPage' => $perPage,
                'page' => $request->query('page', 1),
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
            ]));

            // Cache for 5 minutes
            $blogs = Cache::remember($cacheKey, 300, function () use ($search, $perPage, $sortBy, $sortOrder) {
                $query = Blog::query();

                // Apply search filter
                if (! empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('title', 'like', "%{$search}%")
                            ->orWhere('content', 'like', "%{$search}%");
                    });
                }

                // Apply sorting
                $query->orderBy($sortBy, $sortOrder);

                return $query->paginate($perPage);
            });

            // Transform data to include full image URLs
            $transformedBlogs = $blogs->through(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'content' => $blog->content,
                    'excerpt' => $this->generateExcerpt($blog->content, 150),
                    'images' => $this->formatImageUrls($blog->images),
                    'created_at' => $blog->created_at?->toIso8601String(),
                    'updated_at' => $blog->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedBlogs->items(),
                'meta' => [
                    'current_page' => $blogs->currentPage(),
                    'from' => $blogs->firstItem(),
                    'to' => $blogs->lastItem(),
                    'per_page' => $blogs->perPage(),
                    'total' => $blogs->total(),
                    'last_page' => $blogs->lastPage(),
                ],
                'links' => [
                    'first' => $blogs->url(1),
                    'last' => $blogs->url($blogs->lastPage()),
                    'prev' => $blogs->previousPageUrl(),
                    'next' => $blogs->nextPageUrl(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Blog Index Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching blogs',
            ], 500);
        }
    }

    /**
     * API: Get latest N blogs with caching.
     */
    public function apiLatest(Request $request): JsonResponse
    {
        try {
            // Validate limit parameter
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $limit = $validated['limit'] ?? 3;

            // Cache key based on limit
            $cacheKey = "blogs_latest_{$limit}";

            // Cache for 10 minutes
            $blogs = Cache::remember($cacheKey, 600, function () use ($limit) {
                return Blog::query()
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
            });

            // Transform data
            $transformedBlogs = $blogs->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'content' => $blog->content,
                    'excerpt' => $this->generateExcerpt($blog->content, 150),
                    'images' => $this->formatImageUrls($blog->images),
                    'created_at' => $blog->created_at?->toIso8601String(),
                    'updated_at' => $blog->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedBlogs,
                'meta' => [
                    'count' => $transformedBlogs->count(),
                    'limit' => $limit,
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Blog Latest Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching latest blogs',
            ], 500);
        }
    }

    /**
     * API: Get a single blog by ID with caching.
     */
    public function showApi(int $id): JsonResponse
    {
        try {
            // Cache key based on blog ID
            $cacheKey = "blog_show_api_{$id}";

            // Cache for 10 minutes
            $blog = Cache::remember($cacheKey, 600, function () use ($id) {
                return Blog::find($id);
            });

            // Check if blog exists
            if (! $blog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found',
                ], 404);
            }

            // Transform and return full blog data
            $transformedBlog = [
                'id' => $blog->id,
                'title' => $blog->title,
                'content' => $blog->content,
                'excerpt' => $this->generateExcerpt($blog->content, 150),
                'images' => $this->formatImageUrls($blog->images),
                'image_count' => count($blog->images ?? []),
                'created_at' => $blog->created_at?->toIso8601String(),
                'updated_at' => $blog->updated_at?->toIso8601String(),
                'created_at_human' => $blog->created_at?->diffForHumans(),
                'updated_at_human' => $blog->updated_at?->diffForHumans(),
            ];

            return response()->json([
                'success' => true,
                'data' => $transformedBlog,
            ], 200);

        } catch (\Exception $e) {
            \Log::error('API Blog Show Error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the blog',
            ], 500);
        }
    }

    /**
     * API: Get related blogs based on title and content similarity.
     */
    public function relatedBlogs(int $id): JsonResponse
    {
        try {
            // Check if the blog exists first
            $currentBlog = Blog::find($id);

            if (! $currentBlog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Blog not found',
                ], 404);
            }

            // Cache key based on blog ID
            $cacheKey = "blog_related_{$id}";

            // Cache for 15 minutes
            $relatedBlogs = Cache::remember($cacheKey, 900, function () use ($currentBlog, $id) {
                // Extract keywords from title (remove common words)
                $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'is', 'are', 'was', 'were'];
                $titleWords = array_filter(
                    preg_split('/\s+/', strtolower($currentBlog->title)),
                    fn ($word) => strlen($word) > 2 && ! in_array($word, $commonWords)
                );

                // Build query to find related blogs
                $query = Blog::where('id', '!=', $id);

                // Search for blogs with similar titles or content
                if (! empty($titleWords)) {
                    $query->where(function ($q) use ($titleWords) {
                        foreach ($titleWords as $word) {
                            $q->orWhere('title', 'like', "%{$word}%")
                                ->orWhere('content', 'like', "%{$word}%");
                        }
                    });
                }

                return $query
                    ->latest('created_at')
                    ->limit(3)
                    ->get();
            });

            // If no related blogs found, get latest 3 blogs instead
            if ($relatedBlogs->isEmpty()) {
                $relatedBlogs = Blog::where('id', '!=', $id)
                    ->latest('created_at')
                    ->limit(3)
                    ->get();
            }

            // Transform data
            $transformedBlogs = $relatedBlogs->map(function ($blog) {
                return [
                    'id' => $blog->id,
                    'title' => $blog->title,
                    'excerpt' => $this->generateExcerpt($blog->content, 150),
                    'images' => $this->formatImageUrls($blog->images),
                    'created_at' => $blog->created_at?->toIso8601String(),
                    'created_at_human' => $blog->created_at?->diffForHumans(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedBlogs,
                'meta' => [
                    'blog_id' => $id,
                    'count' => $transformedBlogs->count(),
                    'limit' => 3,
                ],
            ], 200);

        } catch (\Exception $e) {
            \Log::error('API Blog Related Error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching related blogs',
            ], 500);
        }
    }

    /**
     * Helper: Generate excerpt from content
     */
    private function generateExcerpt(?string $content, int $length = 150): ?string
    {
        if (empty($content)) {
            return null;
        }

        $content = strip_tags($content);

        if (mb_strlen($content) <= $length) {
            return $content;
        }

        return mb_substr($content, 0, $length).'...';
    }

    /**
     * Helper: Format image paths to full URLs
     */
    private function formatImageUrls(?array $images): array
    {
        if (empty($images)) {
            return [];
        }

        return array_map(function ($image) {
            if (filter_var($image, FILTER_VALIDATE_URL)) {
                return $image;
            }

            return asset('storage/'.$image);
        }, $images);
    }
}
