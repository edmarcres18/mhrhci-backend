<?php

namespace App\Http\Controllers;

use App\Models\NewsletterSubscription;
use App\Notifications\NewsletterSubscribed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\View\View;

class NewsletterSubscriptionController extends Controller
{
    /**
     * Public: Subscribe a guest user to the newsletter.
     */
    public function subscribe(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'first_name' => ['required', 'string', 'min:2', 'max:100'],
                'last_name' => ['required', 'string', 'min:2', 'max:100'],
                // Reject obvious disposable/sample domains and ensure real DNS
                'email' => [
                    'required',
                    'email:rfc,dns',
                    'max:255',
                    'unique:newsletter_subscriptions,email',
                    'not_regex:/@(example\.com|test\.com|sample\.com|mailinator\.com|10minutemail\.com|tempmail\.|yopmail\.com)$/i',
                ],
            ]);

            $payload = [
                'first_name' => trim($validated['first_name']),
                'last_name' => trim($validated['last_name']),
                'email' => strtolower(trim($validated['email'])),
            ];

            $subscription = NewsletterSubscription::create($payload);

            // Send confirmation email
            Notification::route('mail', $subscription->email)
                ->notify(new NewsletterSubscribed($subscription));

            return response()->json([
                'success' => true,
                'data' => $subscription,
                'message' => 'Thank you for subscribing! A confirmation email has been sent.',
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Newsletter subscribe error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while subscribing',
            ], 500);
        }
    }

    /**
     * Admin: List newsletter subscriptions.
     */
    public function apiIndex(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'limit' => ['nullable', 'integer', 'min:1'],
            ]);

            $query = NewsletterSubscription::query()->latest('created_at');

            if (isset($validated['limit'])) {
                $query->limit($validated['limit']);
            }

            $items = $query->get()->map(function (NewsletterSubscription $item) {
                return [
                    'id' => $item->id,
                    'first_name' => $item->first_name,
                    'last_name' => $item->last_name,
                    'email' => $item->email,
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
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Newsletter apiIndex error: '.$e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching subscriptions',
            ], 500);
        }
    }

    /**
     * Public: Unsubscribe via emailed token.
     */
    public function unsubscribe(Request $request, NewsletterSubscription $subscription): View
    {
        $token = (string) $request->query('token');

        if (! $token || $token !== $subscription->unsubscribe_token) {
            return view('newsletter-unsubscribed', [
                'success' => false,
                'message' => 'This unsubscribe link is invalid or has already been used.',
            ]);
        }

        if ($subscription->unsubscribed_at) {
            return view('newsletter-unsubscribed', [
                'success' => true,
                'message' => 'You are already unsubscribed from our newsletter.',
            ]);
        }

        $subscription->forceFill([
            'unsubscribed_at' => now(),
        ])->save();

        return view('newsletter-unsubscribed', [
            'success' => true,
            'message' => 'You have been unsubscribed from our newsletter. You can subscribe again anytime.',
        ]);
    }
}
