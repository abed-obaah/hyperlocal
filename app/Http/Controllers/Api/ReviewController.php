<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ReviewResource;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Restaurant $restaurant)
    {
        return ReviewResource::collection($restaurant->reviews()->latest()->get());
    }

    public function store(Request $request, Restaurant $restaurant)
    {
        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'tags' => 'nullable|array',
            'photos' => 'nullable|array',
            'orderId' => 'nullable|exists:orders,id',
        ]);

        $user = $request->user();

        // If the review is tied to an order, only accept that link when the
        // order belongs to this customer AND this restaurant, so the dashboard
        // can safely show "what was ordered" alongside the review.
        $orderId = null;
        if (! empty($data['orderId'])) {
            $orderId = $restaurant->orders()
                ->where('id', $data['orderId'])
                ->where('customer_id', $user->id)
                ->value('id');
        }

        $review = $restaurant->reviews()->create([
            'user_id' => $user->id,
            'order_id' => $orderId,
            'author' => $user->name,
            'avatar' => $user->avatar,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'tags' => $data['tags'] ?? [],
            'photos' => $data['photos'] ?? [],
        ]);

        // Keep the restaurant's aggregate rating roughly in sync.
        $restaurant->update([
            'review_count' => $restaurant->reviews()->count(),
            'rating' => round($restaurant->reviews()->avg('rating'), 1),
        ]);

        return new ReviewResource($review);
    }
}
