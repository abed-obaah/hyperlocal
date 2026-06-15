<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RestaurantResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'name' => $this->name,
            'coverImage' => $this->cover_image,
            'logo' => $this->logo,
            'cuisines' => $this->cuisines ?? [],
            'categories' => $this->categories ?? [],
            'rating' => (float) $this->rating,
            'reviewCount' => $this->review_count,
            'distanceKm' => (float) $this->distance_km,
            'etaMinutes' => $this->eta_minutes,
            'deliveryFee' => (float) $this->delivery_fee,
            'minOrder' => (float) $this->min_order,
            // Computed live from the restaurant's opening hours, not the stored flag.
            'isOpen' => $this->isOpenNow(),
            'isFavorite' => $this->when(
                $request->user() !== null,
                fn () => $request->user()->favorites()->where('restaurant_id', $this->id)->exists(),
            ),
            'hasPromotion' => (bool) $this->has_promotion,
            'promotionText' => $this->promotion_text,
            'priceLevel' => $this->price_level,
            'address' => $this->address,
            'openingHours' => $this->opening_hours ?? [],
            // Included on the details endpoint.
            'menu' => MenuItemResource::collection($this->whenLoaded('menuItems')),
        ];
    }
}
