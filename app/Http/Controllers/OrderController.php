<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // post request to place order
    // endpoint: POST /api/place-order
    public function store(Request $request)
    {
        if ($request->user()->role !== 'permanent_staff') {
            return Response()->json([
                'message' => 'Unauthorized. Only Permanent Staff can place orders.'
            ], 403);
        }

        $userData = $request->validate([
            'product_id' => 'required|exists:products,id',
            'amount' => 'required|integer|min:1',
            'order_note' => 'nullable|string',
            'personnel_id' => 'required|exists:users,id' //must be a valid user ID
        ]);

        // find personnel and check if personnel is NSP
        $personnel = User::find($userData['personnel_id']);

        if ($personnel->role !== 'national_service') {
            return Response()->json([
                    'message' => 'The selected personnel must be a National Service Personnel.'
                ], 422);
        }

        // fetch product - capture its name and price right now(to create a copy)
        $product = Product::findorFail($userData['product_id']);

        $totalPrice = $product->price * $userData['amount']; //user this product's price instead of original product from the db

        $order = Order::create([
            'user_id' => $request->user()->id, //logged-in staff member
            'product_id' => $userData['product_id'],
            'personnel_id' => $userData['personnel_id'],
            'product_name' => $product->name,
            'product_price' => $product->price,
            'amount' => $userData['amount'],
            'total_price' => $totalPrice,
            'order_note' => $userData['order_note'],
            'status' => 'placed'
        ]);

        return Response()->json([
            'message' => 'Order placed successfully',
            'order' => $order
        ], 201);
    }


    // view order history - /api/orders (filters for both PS and NSPs)
    // endpoint: GET /api/orders
    // GET /api/orders?date=2026-10-30&search=pepper   
    public function index(Request $request)
    {
        $user = $request->user();

        // both are null unless client sends them. thus, they're optional filters
        $dateFilter = $request->query('date'); //format - 2026-10-30
        $search = $request->query('search');
        
        
        // determine query method based on role
        // 'with' segment is eager loading: attaches product and user objects to each order object, so that the client can access them without making additional requests
        if ($user->role === 'permanent_staff') {
            // fetches orders placed by logged in PS
            $query = Order::where('user_id', $user->id)->with(['product', 'personnel']);
        } else {
            // fetches logged in NSP's assigned orders
            $query = Order::where('personnel_id', $user->id)->with(['product', 'user']);
        }
        // $query is only a query builder. nothing hits db still at this point]
        // query only runs when get() is called, which is after the optional filters are applied below

        // if date is passed, filter rows matching only that calendar date
        if ($dateFilter) {
            $query->whereDate('created_at', $dateFilter);
        }

        //
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'LIKE', "%{$search}%")
                  ->orWhere('order_note', 'LIKE', "%{$search}%");
            });
        }


        // sorts and returns latest items first
        $orders = $query->latest()->get();

        return Response()->json($orders, 200);
    }


    // fetch today's orders lists(for NSPs screen)
    // Endpoint: GET /api/orders/today 
    public function today(Request $request)
    {
        $user = $request->user();

        if ($user->role !== 'national_service') {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        // Carbon handles dates
        $orders = Order::where('personnel_id', $user->id)
            ->whereDate('created_at', Carbon::today())
            ->with(['product', 'user'])
            ->latest()
            ->get();

        return response()->json($orders, 200);
    }


    // mark order as completed(for assigned NSPs)
    // endpoint: PATCH /api/orders/{order}/complete
    public function complete(Request $request, Order $order)
    {
        // checks agains PS marking orders as complete
        if ($request->user()->role !== 'national_service') {
            return response()->json(['message' => 'Unauthorized. Only NSPs can mark orders as complete.'], 403);
        }

        // ensure this NSP is actually the one assigned to this order
        if ($order->personnel_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized. This order is not assigned to you.'], 403);
        }

        // update status string field
        $order->update([
            'status' => 'completed'
        ]);

        return response()->json([
            'message' => 'Order marked as completed!',
            'order' => $order
        ], 200);
    }
}