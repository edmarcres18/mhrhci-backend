<?php

namespace App\Http\Controllers;

use App\Models\Principal;
use App\Models\Product;
use App\Models\NewsletterSubscription;
use App\Notifications\NewsletterPrincipalCreated;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PrincipalController extends Controller
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

        $query = Principal::query();

        if ($search !== '') {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        }

        $principals = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function (Principal $principal) {
                return [
                    'id' => $principal->id,
                    'name' => $principal->name,
                    'description' => $principal->description,
                    'logo' => $principal->logo ? Storage::url($principal->logo) : null,
                    'is_featured' => (bool) $principal->is_featured,
                    'created_at' => optional($principal->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Principals/Index', [
            'principals' => $principals,
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
        return Inertia::render('Principals/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('principals', 'public');
        }

        $principal = Principal::create($data);

        $this->notifySubscribersAboutPrincipal($principal);

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal created successfully.');
    }

    /**
     * Notify newsletter subscribers when a new principal is created.
     */
    protected function notifySubscribersAboutPrincipal(Principal $principal): void
    {
        try {
            NewsletterSubscription::chunk(200, function ($subscribers) use ($principal) {
                Notification::send($subscribers, new NewsletterPrincipalCreated($principal));
            });
        } catch (\Throwable $e) {
            Log::error('Newsletter principal notify failed', [
                'principal_id' => $principal->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Principal $principal): Response
    {
        return Inertia::render('Principals/Edit', [
            'principal' => [
                'id' => $principal->id,
                'name' => $principal->name,
                'description' => $principal->description,
                'logo' => $principal->logo,
                'logo_url' => $principal->logo ? Storage::url($principal->logo) : null,
                'is_featured' => (bool) $principal->is_featured,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Principal $principal): Response
    {
        return Inertia::render('Principals/Show', [
            'principal' => [
                'id' => $principal->id,
                'name' => $principal->name,
                'description' => $principal->description,
                'logo' => $principal->logo,
                'logo_url' => $principal->logo ? Storage::url($principal->logo) : null,
                'is_featured' => (bool) $principal->is_featured,
                'created_at' => optional($principal->created_at)->toDateTimeString(),
                'updated_at' => optional($principal->updated_at)->toDateTimeString(),
            ],
        ]);
    }

    /**
     * API: Get all principals with name, description, and logo URL (cached).
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'sortBy' => ['nullable', 'string', 'in:name,created_at,updated_at'],
                'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            ]);

            $sortBy = $validated['sortBy'] ?? 'name';
            $sortOrder = $validated['sortOrder'] ?? 'asc';

            $cacheKey = 'principals_api_'.md5(json_encode([
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
            ]));

            $principals = Cache::remember($cacheKey, 300, function () use ($sortBy, $sortOrder) {
                return Principal::query()
                    ->orderBy($sortBy, $sortOrder)
                    ->get();
            });

            $transformed = $principals->map(function (Principal $principal) {
                return [
                    'id' => $principal->id,
                    'name' => $principal->name,
                    'description' => $principal->description,
                    'logo' => $principal->logo ? Storage::url($principal->logo) : null,
                    'is_featured' => (bool) $principal->is_featured,
                    'created_at' => $principal->created_at?->toIso8601String(),
                    'updated_at' => $principal->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformed,
                'meta' => [
                    'count' => $transformed->count(),
                ],
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Principal Index Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching principals',
            ], 500);
        }
    }

    /**
     * API: Get featured principals only.
     */
    public function apiFeatured(): JsonResponse
    {
        try {
            $cacheKey = 'principals_featured_api_v1';

            $principals = Cache::remember($cacheKey, 300, function () {
                return Principal::query()
                    ->where('is_featured', true)
                    ->orderBy('name')
                    ->get();
            });

            $transformed = $principals->map(function (Principal $principal) {
                return [
                    'id' => $principal->id,
                    'name' => $principal->name,
                    'description' => $principal->description,
                    'logo' => $principal->logo ? Storage::url($principal->logo) : null,
                    'is_featured' => (bool) $principal->is_featured,
                    'created_at' => $principal->created_at?->toIso8601String(),
                    'updated_at' => $principal->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformed,
                'meta' => [
                    'count' => $transformed->count(),
                ],
            ], 200);
        } catch (\Exception $e) {
            \Log::error('API Principal Featured Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching featured principals',
            ], 500);
        }
    }

    /**
     * API: Get all products that belong to a specific principal.
     */
    public function apiProducts(Request $request, int $id): JsonResponse
    {
        try {
            $principal = Principal::findOrFail($id);

            $cacheKey = "principal_{$id}_products";
            $products = Cache::remember($cacheKey, 300, function () use ($id) {
                return Product::query()
                    ->where('principal_id', $id)
                    ->orderBy('name')
                    ->get();
            });

            $transformedProducts = $products->map(function (Product $product) {
                $images = collect($product->images ?? [])
                    ->filter()
                    ->map(fn ($path) => Storage::url($path))
                    ->values()
                    ->all();

                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_type' => $product->product_type?->value,
                    'product_type_label' => $product->product_type?->displayName(),
                    'description' => $product->description,
                    'images' => $images,
                    'features' => $product->features ?? [],
                    'is_featured' => (bool) $product->is_featured,
                    'principal_id' => $product->principal_id,
                    'created_at' => $product->created_at?->toIso8601String(),
                    'updated_at' => $product->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'principal' => [
                    'id' => $principal->id,
                    'name' => $principal->name,
                    'description' => $principal->description,
                    'logo' => $principal->logo ? Storage::url($principal->logo) : null,
                    'created_at' => $principal->created_at?->toIso8601String(),
                    'updated_at' => $principal->updated_at?->toIso8601String(),
                ],
                'data' => $transformedProducts,
                'meta' => [
                    'count' => $transformedProducts->count(),
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Principal not found',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('API Principal Products Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products for this principal',
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Principal $principal)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:2048'],
            'remove_logo' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $data = [
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_featured' => (bool) ($validated['is_featured'] ?? $principal->is_featured),
        ];

        $removeLogo = $request->boolean('remove_logo', false);

        if ($removeLogo && $principal->logo) {
            Storage::disk('public')->delete($principal->logo);
            $data['logo'] = null;
        }

        if ($request->hasFile('logo')) {
            if ($principal->logo) {
                Storage::disk('public')->delete($principal->logo);
            }
            $data['logo'] = $request->file('logo')->store('principals', 'public');
        }

        $principal->update($data);

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Principal $principal)
    {
        if ($principal->logo) {
            Storage::disk('public')->delete($principal->logo);
        }

        $principal->delete();

        return redirect()
            ->route('principals.index')
            ->with('success', 'Principal deleted successfully.');
    }
}
