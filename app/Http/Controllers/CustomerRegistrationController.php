<?php

namespace App\Http\Controllers;

use App\Models\CustomerRegistration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CustomerRegistrationController extends Controller
{
    /**
     * API: List recent customer registrations.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1'],
            ]);

            $limit = $validated['limit'] ?? null; // Unlimited by default

            $query = CustomerRegistration::query()
                ->latest('created_at');

            if ($limit !== null) {
                $query->limit($limit);
            }

            $items = $query
                ->get()
                ->map(function (CustomerRegistration $item) {
                    return [
                        'id' => $item->id,
                        'entry_number' => $item->entry_number,
                        'name' => $item->name,
                        'hospital' => $item->hospital,
                        'address' => $item->address,
                        'position' => $item->position,
                        'contact_number' => $item->contact_number,
                        'email' => $item->email,
                        'products_interest' => $item->products_interest,
                        'created_at' => $item->created_at?->toIso8601String(),
                        'updated_at' => $item->updated_at?->toIso8601String(),
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $items,
                'meta' => [
                    'count' => $items->count(),
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
            Log::error('API CustomerRegistration Index Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching registrations',
            ], 500);
        }
    }

    /**
     * API: Store a new customer registration.
     */
    public function storeApi(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'entry_number' => ['required', 'string', 'regex:/^[0-9]{1,10}$/'],
                'name' => ['required', 'string', 'min:2', 'max:100'],
                'hospital' => ['required', 'string', 'min:2', 'max:120'],
                'address' => ['required', 'string', 'min:5', 'max:200'],
                'position' => ['required', 'string', 'min:2', 'max:80'],
                'contact_number' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
                'email' => ['required', 'email:rfc,dns'],
                'products_interest' => ['nullable', 'string'],
                'remarks' => ['nullable', 'string'], // Accept frontend field name as alias
            ]);

            $payload = [
                'entry_number' => trim($validated['entry_number']),
                'name' => trim($validated['name']),
                'hospital' => trim($validated['hospital']),
                'address' => trim($validated['address']),
                'position' => trim($validated['position']),
                'contact_number' => trim($validated['contact_number']),
                'email' => strtolower(trim($validated['email'])),
                'products_interest' => isset($validated['products_interest'])
                    ? trim($validated['products_interest'])
                    : (isset($validated['remarks']) ? trim($validated['remarks']) : null),
            ];

            $item = CustomerRegistration::create($payload);

            return response()->json([
                'success' => true,
                'data' => $item,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API CustomerRegistration Store Error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving registration',
            ], 500);
        }
    }

    /**
     * API: Show a registration by ID.
     */
    public function showApi(int $id): JsonResponse
    {
        try {
            $item = CustomerRegistration::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $item->id,
                    'entry_number' => $item->entry_number,
                    'name' => $item->name,
                    'hospital' => $item->hospital,
                    'address' => $item->address,
                    'position' => $item->position,
                    'contact_number' => $item->contact_number,
                    'email' => $item->email,
                    'products_interest' => $item->products_interest,
                    'created_at' => $item->created_at?->toIso8601String(),
                    'updated_at' => $item->updated_at?->toIso8601String(),
                ],
            ], 200);
        } catch (\Exception $e) {
            Log::error('API CustomerRegistration Show Error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching the registration',
            ], 500);
        }
    }

    /**
     * API: Delete a registration by ID.
     */
    public function destroyApi(int $id): JsonResponse
    {
        try {
            // Ensure only ADMIN or SYSTEM_ADMIN can delete
            $user = auth()->user();
            if (! $user || ! $user->hasAdminPrivileges()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Forbidden: insufficient privileges',
                ], 403);
            }

            $item = CustomerRegistration::find($id);

            if (! $item) {
                return response()->json([
                    'success' => false,
                    'message' => 'Registration not found',
                ], 404);
            }

            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registration deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('API CustomerRegistration Destroy Error: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the registration',
            ], 500);
        }
    }
}
