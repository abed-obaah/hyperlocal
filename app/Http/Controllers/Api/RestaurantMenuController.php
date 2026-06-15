<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MenuItemResource;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantMenuController extends Controller
{
    private function restaurant(Request $request): Restaurant
    {
        $restaurant = $request->user()->restaurant;
        abort_if(! $restaurant, 403, 'No restaurant linked to this account.');

        return $restaurant;
    }

    public function index(Request $request)
    {
        return MenuItemResource::collection($this->restaurant($request)->menuItems()->get());
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);
        $item = $this->restaurant($request)->menuItems()->create($this->toColumns($data));

        return new MenuItemResource($item);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->restaurant_id === $this->restaurant($request)->id, 403);
        $data = $this->validateItem($request, false);
        $menuItem->update($this->toColumns($data));

        return new MenuItemResource($menuItem);
    }

    public function destroy(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->restaurant_id === $this->restaurant($request)->id, 403);
        $menuItem->delete();

        return response()->json(['message' => 'Deleted']);
    }

    /** Toggle item availability ("Set item availability"). */
    public function toggleAvailability(Request $request, MenuItem $menuItem)
    {
        abort_unless($menuItem->restaurant_id === $this->restaurant($request)->id, 403);
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return new MenuItemResource($menuItem);
    }

    private function validateItem(Request $request, bool $required = true): array
    {
        $rule = $required ? 'required' : 'sometimes';

        return $request->validate([
            'name' => "{$rule}|string|max:255",
            'price' => "{$rule}|numeric|min:0",
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'section' => 'nullable|string',
            'ingredients' => 'nullable|array',
            'prepTimeMinutes' => 'nullable|integer',
            'calories' => 'nullable|integer',
            'popular' => 'boolean',
            'isAvailable' => 'boolean',
            'optionGroups' => 'nullable|array',
        ]);
    }

    private function toColumns(array $data): array
    {
        return array_filter([
            'name' => $data['name'] ?? null,
            'price' => $data['price'] ?? null,
            'description' => $data['description'] ?? null,
            'image' => $data['image'] ?? null,
            'section' => $data['section'] ?? null,
            'ingredients' => $data['ingredients'] ?? null,
            'prep_time_minutes' => $data['prepTimeMinutes'] ?? null,
            'calories' => $data['calories'] ?? null,
            'popular' => $data['popular'] ?? null,
            'is_available' => $data['isAvailable'] ?? null,
            'option_groups' => $data['optionGroups'] ?? null,
        ], fn ($v) => $v !== null);
    }
}
