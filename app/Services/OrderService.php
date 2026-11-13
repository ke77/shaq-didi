<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Exceptions\OrderException;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderCancelledMail;
use App\Mail\OrderAssignedMail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Mail;

class OrderService
{
     public function place(User $staff, array $data): Order
     {
          // the client only sends id. name and price come from the db
          $product = Product::findOrFail($data['product_id']);

          $personnel = User::findOrFail($data['personnel_id']);

          $order = $staff->orders()->create([
               'product_id' => $product->id,
               'personnel_id' => $data['personnel_id'],
               // snapshot so later product edits don't rewrite order history
               'product_name' => $product->name,
               'product_price' => $product->price,
               'amount' => $data['amount'],
               'total_price' => $product->price * $data['amount'],
               'order_note' => $data['order_note'] ?? null,
               'status' => OrderStatus::Pending,
          ]);

          Mail::to($staff)->send(new OrderConfirmationMail($order));
          Mail::to($personnel)->send(new OrderAssignedMail($order));
          
          // creating through the relation sets user_id from $staff
          return $order;

     }


     public function listFor(User $user, array $filters): Collection
     {
          $base = $user->role === UserRole::PermanentStaff
              ? Order::where('user_id', $user->id)->with(['product', 'personnel'])
              : Order::where('personnel_id', $user->id)->with(['product', 'user']);

          return $base
               ->when($filters['date'] ?? null, fn ($q, $date) => $q->whereDate('created_at', $date))
               ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
               ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($group) use ($search) {
                    $group->where('product_name', 'LIKE', "%{$search}%")
                    ->orWhere('order_note', 'LIKE', "%{$search}%");
          }))->latest()->get();
     }


     public function todayFor(User $user): Collection
     {

          if ($user->role !== UserRole::NationalService) {
              throw OrderException::onlyNationalService();
          }

          return Order::where('personnel_id', $user->id)
              ->whereDate('created_at', today())
              ->with(['product', 'user'])
              ->latest()
              ->get();
     }



     public function complete(Order $order, User $user): Order
     {
          if ($user->role !== UserRole::NationalService) {
               throw OrderException::onlyNationalService();
          }

          if ($order->personnel_id !== $user->id) {
               throw OrderException::notAssigned();
          }

          if ($order->status === OrderStatus::Completed) {
               throw OrderException::alreadyCompleted();
          }

          if ($order->status === OrderStatus::Cancelled) {
               throw OrderException::alreadyCancelled();
          }

          $order->update([
               'status' => OrderStatus::Completed
          ]);

          return $order;
     }


     // cancels pending orders(returns how many)
     public function cancelPendingOrders(): int
     {
          // return Order::where('status', OrderStatus::Pending->value)
          //      ->update([
          //           'status' => OrderStatus::Cancelled
          //      ]);

          $cancelled = 0;

          Order::where('status', OrderStatus::Pending->value)
               ->with(['user', 'personnel'])
               ->chunkById(100, function ($orders) use (&$cancelled) {
                    foreach ($orders as $order) {
                         // the status check inside the update keeps this safe if an NSP completes it meanwhile
                         $changed = Order::whereKey($order->id)
                              ->where('status', OrderStatus::Pending->value)
                              ->update(['status' => OrderStatus::Cancelled->value]);

                         if ($changed === 1) {
                              $cancelled++;
                              $this->notifyCancelled($order);
                         }
                    }
               });

          return $cancelled;
     }

     private function notifyCancelled(Order $order): void
     {
          Mail::to($order->user)->send(new OrderCancelledMail($order));

          // personnel_id is nullable (set null on delete), so guard it
          if ($order->personnel) {
               Mail::to($order->personnel)->send(new OrderCancelledMail($order));
          }
     }
}