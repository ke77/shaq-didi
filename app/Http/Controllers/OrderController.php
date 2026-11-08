<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListOrdersRequest;
use App\Http\Requests\PlaceOrderRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orders) {}

    // post request to place order
    // endpoint: POST /api/place-order
    public function store(PlaceOrderRequest $request)
    {
        $order = $this->orders->place($request->user(), $request->validated());

        return response()->json([
            'message' => 'Order placed successfully',
            'order' => $order,
        ], 201);
    }


    // view order history - /api/orders (filters for both PS and NSPs)
    // endpoint: GET /api/orders
    // GET /api/orders?date=2026-10-30&search=pepper   
    public function index(ListOrdersRequest $request)
    {
        return response()->json(
            $this->orders->listFor($request->user(), $request->validated())
        );
    }


    // fetch today's orders lists(for NSPs screen)
    // Endpoint: GET /api/orders/today 
    public function today(ListOrdersRequest $request)
    {   
        return response()->json($this->orders->todayFor($request->user()));
    }


    // mark order as completed(for assigned NSPs)
    // endpoint: PATCH /api/orders/{order}/complete
    public function complete(Order $order, PlaceOrderRequest $request)
    {
        $order = $this->orders->complete($order, $request->user());

        return response()->json([
            'message' => 'Order marked as completed!',
            'order' => $order,
        ]);
    }

         
}