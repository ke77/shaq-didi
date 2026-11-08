<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRestaurantsRequest;
use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // fetch restaurants
    public function index(SearchRestaurantsRequest $request)
    {
        // check if theres a search query param
        $search = $request->validated('search');

        $restaurants = Restaurant::query()
            ->when($search, fn ($q) => $q->where('name', 'LIKE', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return response()->json($restaurants);
    }


    // fetch single restaurant's products
    public function products(Restaurant $restaurant)
    {
        return response()->json($restaurant->products);
    }
}
