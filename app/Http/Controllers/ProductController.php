<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // fetch all products
    public function index() {
        return Response()->json(Product::all(), 200);
    }


    // public function product
}
