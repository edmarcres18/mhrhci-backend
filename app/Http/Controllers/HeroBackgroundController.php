<?php

namespace App\Http\Controllers;

use App\Http\Requests\HeroBackgroundUploadRequest;
use App\Http\Resources\HeroBackgroundResource;
use App\Models\HeroBackground;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class HeroBackgroundController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $items = HeroBackground::orderBy('created_at', 'asc')->get();

            return response()->json([
                'success' => true,
                'data' => HeroBackgroundResource::collection($items),
                'meta' => [
                    'count' => $items->count(),
                    'max' => 5,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('HeroBackground index error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching hero backgrounds',
            ], 500);
        }
    }

    public function store(HeroBackgroundUploadRequest $request): JsonResponse
    {
        try {
            $this->ensureStorageSymlink();
            Storage::disk('public')->makeDirectory('hero-bg');

            $existingCount = HeroBackground::count();
            $newCount = count($request->file('images', []));

            if ($existingCount + $newCount > 5) {
                return response()->json([
                    'success' => false,
                    'message' => 'Upload limit exceeded: a maximum of 5 images is allowed',
                ], 422);
            }

            $savedFiles = [];

            DB::beginTransaction();
            try {
                foreach ($request->file('images', []) as $file) {
                    $ext = strtolower($file->getClientOriginalExtension());
                    $filename = Str::uuid()->toString().'.'.$ext;

                    $storedPath = Storage::disk('public')->putFileAs('hero-bg', $file, $filename);
                    if (! $storedPath) {
                        throw new \RuntimeException('Failed to store image');
                    }

                    $publicPath = 'storage/'.ltrim($storedPath, '/');
                    $record = HeroBackground::create([
                        'image_path' => $publicPath,
                    ]);

                    $savedFiles[] = [
                        'disk_path' => $storedPath,
                        'db_id' => $record->id,
                    ];
                }

                DB::commit();

                Cache::forget('hero_backgrounds_frontend');

                $items = HeroBackground::orderBy('created_at', 'asc')->get();

                return response()->json([
                    'success' => true,
                    'message' => 'Images uploaded successfully',
                    'data' => HeroBackgroundResource::collection($items),
                    'meta' => [
                        'count' => $items->count(),
                        'max' => 5,
                    ],
                ], 201);
            } catch (\Throwable $t) {
                DB::rollBack();
                foreach ($savedFiles as $sf) {
                    try {
                        Storage::disk('public')->delete($sf['disk_path']);
                    } catch (\Throwable $ignored) {
                    }
                }
                throw $t;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('HeroBackground store error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while uploading images',
            ], 500);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $item = HeroBackground::find($id);
            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Image not found',
                ], 404);
            }

            $relative = ltrim(str_replace('storage/', '', $item->image_path), '/');
            Storage::disk('public')->delete($relative);

            $item->delete();

            $items = HeroBackground::orderBy('created_at', 'asc')->get();

            Cache::forget('hero_backgrounds_frontend');

            return response()->json([
                'success' => true,
                'message' => 'Image deleted successfully',
                'data' => HeroBackgroundResource::collection($items),
                'meta' => [
                    'count' => $items->count(),
                    'max' => 5,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('HeroBackground destroy error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the image',
            ], 500);
        }
    }

    public function frontend(): JsonResponse
    {
        try {
            $images = Cache::remember('hero_backgrounds_frontend', 3600, function () {
                return HeroBackground::orderBy('created_at', 'asc')
                    ->get()
                    ->map(function (HeroBackground $item) {
                        return $item->url;
                    })
                    ->values()
                    ->toArray();
            });

            return response()->json([
                'success' => true,
                'data' => $images,
                'meta' => [
                    'count' => count($images),
                    'max' => 5,
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('HeroBackground frontend error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching hero backgrounds',
            ], 500);
        }
    }

    protected function ensureStorageSymlink(): void
    {
        $link = public_path('storage');
        $target = storage_path('app/public');
        if (! is_link($link) && ! file_exists($link)) {
            try {
                @mkdir($target, 0775, true);
                @app('files')->link($target, $link);
            } catch (\Throwable $e) {
                Log::warning('Unable to create storage symlink automatically', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
