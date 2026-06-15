<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ReviewResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'restaurantId' => (string) $this->restaurant_id,
            'author' => $this->author,
            'avatar' => $this->avatar,
            'rating' => (int) $this->rating,
            'comment' => $this->comment,
            'date' => optional($this->created_at)->toDateString(),
            'photos' => $this->photos ?? [],
            'tags' => $this->tags ?? [],
        ];
    }
}
