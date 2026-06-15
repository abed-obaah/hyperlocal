<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => (string) $this->id,
            'restaurantId' => (string) $this->restaurant_id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => (float) $this->price,
            'image' => $this->image,
            'section' => $this->section,
            'ingredients' => $this->ingredients ?? [],
            'prepTimeMinutes' => $this->prep_time_minutes,
            'calories' => $this->calories,
            'popular' => (bool) $this->popular,
            'isAvailable' => (bool) $this->is_available,
            'optionGroups' => $this->option_groups ?? [],
        ];
    }
}
