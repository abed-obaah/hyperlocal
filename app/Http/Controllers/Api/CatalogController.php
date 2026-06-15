<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\MenuItemResource;
use App\Http\Resources\RestaurantResource;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function categories()
    {
        return CategoryResource::collection(Category::orderBy('id')->get());
    }

    public function restaurants(Request $request)
    {
        $query = Restaurant::query();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('cuisines', 'like', "%{$search}%");
            });
        }
        if ($category = $request->query('category')) {
            $query->where('categories', 'like', "%\"{$category}\"%");
        }
        if ($request->boolean('open_now')) {
            $query->where('is_open', true);
        }
        if ($request->boolean('promotions')) {
            $query->where('has_promotion', true);
        }
        if ($min = $request->query('min_rating')) {
            $query->where('rating', '>=', (float) $min);
        }

        match ($request->query('sort_by', 'distance')) {
            'eta' => $query->orderBy('eta_minutes'),
            'rating' => $query->orderByDesc('rating'),
            default => $query->orderBy('distance_km'),
        };

        return RestaurantResource::collection($query->get());
    }

    public function restaurant(Restaurant $restaurant)
    {
        $restaurant->load(['menuItems' => fn ($q) => $q->orderBy('section')]);

        return new RestaurantResource($restaurant);
    }

    public function menuItem(MenuItem $menuItem)
    {
        return new MenuItemResource($menuItem);
    }
}
