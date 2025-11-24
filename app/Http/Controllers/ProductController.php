<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\ProductType;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        $search = (string) $request->query('search', '');
        $productType = (string) $request->query('product_type', '');
        $perPage = (int) $request->query('perPage', 10);
        $allowed = [10, 25, 50, 100];
        if (! in_array($perPage, $allowed, true)) {
            $perPage = 10;
        }

        $query = Product::query();

        // Apply search filter
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Apply product type filter
        if ($productType !== '' && in_array($productType, ProductType::values(), true)) {
            $query->where('product_type', $productType);
        }

        $products = $query
            ->latest('created_at')
            ->paginate($perPage)
            ->withQueryString()
            ->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_type' => $product->product_type->value,
                    'product_type_label' => $product->product_type->displayName(),
                    'description' => $product->description,
                    'images' => $product->images,
                    'features' => $product->features,
                    'is_featured' => (bool) $product->is_featured,
                    'created_at' => optional($product->created_at)->toDateTimeString(),
                ];
            });

        return Inertia::render('Products/Index', [
            'products' => $products,
            'filters' => [
                'search' => $search,
                'product_type' => $productType,
                'perPage' => $perPage,
            ],
            'productTypes' => collect(ProductType::all())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->displayName(),
            ])->toArray(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Products/Create', [
            'productTypes' => collect(ProductType::all())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->displayName(),
            ])->toArray(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', Rule::in(ProductType::values())],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $imagePaths = [];
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $product = Product::create([
            'name' => $validated['name'],
            'product_type' => $validated['product_type'],
            'description' => $validated['description'] ?? null,
            'features' => $validated['features'] ?? [],
            'images' => $imagePaths,
            'is_featured' => (bool) ($validated['is_featured'] ?? false),
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product): Response
    {
        return Inertia::render('Products/Show', [
            'product' => $product,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product): Response
    {
        return Inertia::render('Products/Edit', [
            'product' => $product,
            'productTypes' => collect(ProductType::all())->map(fn ($type) => [
                'value' => $type->value,
                'label' => $type->displayName(),
            ])->toArray(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'product_type' => ['required', 'string', Rule::in(ProductType::values())],
            'description' => ['nullable', 'string'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
            'images' => ['nullable', 'array', 'max:5'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp,avif', 'max:5120'],
            'keepExistingImages' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ]);

        $keepExisting = filter_var($request->input('keepExistingImages', true), FILTER_VALIDATE_BOOLEAN);
        $imagePaths = $keepExisting ? ($product->images ?? []) : [];

        if ($request->hasFile('images')) {
            if (! $keepExisting) {
                foreach (($product->images ?? []) as $old) {
                    if ($old && Storage::disk('public')->exists($old)) {
                        Storage::disk('public')->delete($old);
                    }
                }
            }
            foreach ($request->file('images') as $image) {
                if (count($imagePaths) >= 5) {
                    break;
                }
                $imagePaths[] = $image->store('products', 'public');
            }
        }

        $product->update([
            'name' => $validated['name'],
            'product_type' => $validated['product_type'],
            'description' => $validated['description'] ?? null,
            'features' => $validated['features'] ?? [],
            'images' => array_values($imagePaths),
            'is_featured' => (bool) ($validated['is_featured'] ?? $product->is_featured),
        ]);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        // Only System Admin and Admin can delete products
        $user = auth()->user();
        if (! $user || ! $user->hasAdminPrivileges()) {
            abort(403, 'You do not have permission to delete products.');
        }

        foreach (($product->images ?? []) as $old) {
            if ($old && Storage::disk('public')->exists($old)) {
                Storage::disk('public')->delete($old);
            }
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }

    /**
     * API: Get all products with pagination, filtering, and caching.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            // Validate input parameters
            $validated = $request->validate([
                'search' => ['nullable', 'string', 'max:255'],
                'product_type' => ['nullable', 'string', Rule::in(ProductType::values())],
                'perPage' => ['nullable', 'integer', 'min:1', 'max:100'],
                'page' => ['nullable', 'integer', 'min:1'],
                'sortBy' => ['nullable', 'string', 'in:created_at,updated_at,name'],
                'sortOrder' => ['nullable', 'string', 'in:asc,desc'],
            ]);

            $search = $validated['search'] ?? '';
            $productType = $validated['product_type'] ?? '';
            $perPage = $validated['perPage'] ?? 10;
            $sortBy = $validated['sortBy'] ?? 'created_at';
            $sortOrder = $validated['sortOrder'] ?? 'desc';

            // Create cache key based on request parameters
            $cacheKey = 'products_api_'.md5(json_encode([
                'search' => $search,
                'product_type' => $productType,
                'perPage' => $perPage,
                'page' => $request->query('page', 1),
                'sortBy' => $sortBy,
                'sortOrder' => $sortOrder,
            ]));

            // Cache for 5 minutes
            $products = Cache::remember($cacheKey, 300, function () use ($search, $productType, $perPage, $sortBy, $sortOrder) {
                $query = Product::query();

                // Apply search filter
                if (! empty($search)) {
                    $query->where(function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('description', 'like', "%{$search}%");
                    });
                }

                // Apply product type filter
                if (! empty($productType)) {
                    $query->where('product_type', $productType);
                }

                // Apply sorting
                $query->orderBy($sortBy, $sortOrder);

                return $query->paginate($perPage);
            });

            // Transform data to include full image URLs
            $transformedProducts = $products->through(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_type' => $product->product_type->value,
                    'product_type_label' => $product->product_type->displayName(),
                    'description' => $product->description,
                    'excerpt' => $this->generateExcerpt($product->description, 150),
                    'images' => $this->formatImageUrls($product->images),
                    'features' => $product->features ?? [],
                    'created_at' => $product->created_at?->toIso8601String(),
                    'updated_at' => $product->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'from' => $products->firstItem(),
                    'to' => $products->lastItem(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                    'last_page' => $products->lastPage(),
                ],
                'links' => [
                    'first' => $products->url(1),
                    'last' => $products->url($products->lastPage()),
                    'prev' => $products->previousPageUrl(),
                    'next' => $products->nextPageUrl(),
                ],
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            \Log::error('API Product Index Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products',
            ], 500);
        }
    }

    /**
     * API: Get latest N products with caching.
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
            $cacheKey = "products_latest_{$limit}";

            // Cache for 10 minutes
            $products = Cache::remember($cacheKey, 600, function () use ($limit) {
                return Product::query()
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
            });

            // Transform data
            $transformedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_type' => $product->product_type->value,
                    'product_type_label' => $product->product_type->displayName(),
                    'description' => $product->description,
                    'excerpt' => $this->generateExcerpt($product->description, 150),
                    'images' => $this->formatImageUrls($product->images),
                    'features' => $product->features ?? [],
                    'created_at' => $product->created_at?->toIso8601String(),
                    'updated_at' => $product->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts,
                'meta' => [
                    'count' => $transformedProducts->count(),
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
            \Log::error('API Product Latest Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching latest products',
            ], 500);
        }
    }

    public function apiFeatured(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
            ]);

            $limit = $validated['limit'] ?? 3;

            $cacheKey = "products_featured_{$limit}";

            $products = Cache::remember($cacheKey, 600, function () use ($limit) {
                return Product::query()
                    ->where('is_featured', true)
                    ->latest('created_at')
                    ->limit($limit)
                    ->get();
            });

            $transformedProducts = $products->map(function ($product) {
                return [
                    'id' => $product->id,
                    'name' => $product->name,
                    'product_type' => $product->product_type->value,
                    'product_type_label' => $product->product_type->displayName(),
                    'description' => $product->description,
                    'excerpt' => $this->generateExcerpt($product->description, 150),
                    'images' => $this->formatImageUrls($product->images),
                    'features' => $product->features ?? [],
                    'is_featured' => (bool) $product->is_featured,
                    'created_at' => $product->created_at?->toIso8601String(),
                    'updated_at' => $product->updated_at?->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedProducts,
                'meta' => [
                    'count' => $transformedProducts->count(),
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
            \Log::error('API Product Featured Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching featured products',
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
