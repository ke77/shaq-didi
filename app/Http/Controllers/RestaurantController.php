<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use Illuminate\Http\Request;

class RestaurantController extends Controller
{
    // fetch restaurants
    public function index(Request $request)
    {
        // check if theres a search query param
        $search = $request->query('search');

        if ($search) {
            $restaurants = Restaurant::where('name', 'LIKE', "%{$search}")->get();
        } else {
            $restaurants = Restaurant::all(); //fetch all records
        }

        return Response()->json($restaurants, 200);
    }


    // fetch single restaurant's products
    public function products(Restaurant $restaurant)
    {
        return Response()->json($restaurant->products, 200);
    }
}
