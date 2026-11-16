<?php

use App\Enums\OrderStatus;
use App\Enums\UserRole;
use App\Models\Order;
use App\Models\Product;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\Sanctum;

function makeApiUser(UserRole $role, string $email): User
{
    return User::factory()->create([
        'name' => ucfirst(strstr($email, '@', true)),
        'email' => $email,
        'phone_number' => fake()->unique()->numerify('02########'),
        'role' => $role,
    ]);
}

test('only authenticated staff can place orders and NSP users can complete assigned orders', function () {
    Mail::fake();
    $staff = makeApiUser(UserRole::PermanentStaff, 'staff-orders@example.com');
    $personnel = makeApiUser(UserRole::NationalService, 'nsp-orders@example.com');
    $restaurant = Restaurant::factory()->create();
    $product = Product::factory()->create([
        'restaurant_id' => $restaurant->id,
        'price' => 2500,
    ]);

    $this->postJson('/api/place-order', [
        'product_id' => $product->id,
        'amount' => 2,
        'order_note' => 'Call on arrival',
        'personnel_id' => $personnel->id,
    ])->assertUnauthorized();

    Sanctum::actingAs($staff);
    $this->getJson('/api/restaurants')
        ->assertOk()
        ->assertJsonFragment(['id' => $restaurant->id]);
    $this->getJson('/api/products')
        ->assertOk()
        ->assertJsonFragment(['id' => $product->id]);

    $orderResponse = $this->postJson('/api/place-order', [
        'product_id' => $product->id,
        'amount' => 2,
        'order_note' => 'Call on arrival',
        'personnel_id' => $personnel->id,
    ])->assertCreated()
        ->assertJsonPath('order.product_name', $product->name)
        ->assertJsonPath('order.total_price', 5000)
        ->assertJsonPath('order.personnel.id', $personnel->id)
        ->assertJsonPath('order.product.restaurant.id', $restaurant->id);

    $orderId = $orderResponse->json('order.id');

    Sanctum::actingAs($personnel);
    $this->getJson('/api/orders/today')
        ->assertOk()
        ->assertJsonFragment(['id' => $orderId]);
    $this->patchJson("/api/orders/{$orderId}/complete")
        ->assertOk()
        ->assertJsonPath('order.status', OrderStatus::Completed->value);
});

test('registration accepts frontend role and profile fields and issues a token', function () {
    Mail::fake();

    $this->postJson('/api/auth/register', [
        'name' => 'New Permanent Staff',
        'email' => 'new-staff@example.com',
        'password' => 'secure-password',
        'phone_number' => '0243334444',
        'role' => UserRole::PermanentStaff->value,
    ])->assertCreated()
        ->assertJsonPath('user.name', 'New Permanent Staff')
        ->assertJsonPath('user.role', UserRole::PermanentStaff->value)
        ->assertJsonStructure(['user' => ['id', 'email'], 'token']);
});

test('login issues a bearer token that can access and end an authenticated session', function () {
    $user = makeApiUser(UserRole::PermanentStaff, 'staff-auth-flow@example.com');
    $user->forceFill(['password' => bcrypt('secure-password')])->save();

    $token = $this->postJson('/api/auth/login', [
        'email' => $user->email,
        'password' => 'secure-password',
    ])->assertOk()
        ->assertJsonPath('user.id', $user->id)
        ->json('token');

    $this->withToken($token)
        ->getJson('/api/me')
        ->assertOk()
        ->assertJsonPath('email', $user->email);

    $this->withToken($token)->postJson('/api/auth/logout')->assertOk();
    $this->assertDatabaseCount('personal_access_tokens', 0);
});

test('personnel list contains only national service users and is protected', function () {
    $personnel = makeApiUser(UserRole::NationalService, 'nsp-list@example.com');
    makeApiUser(UserRole::PermanentStaff, 'staff-list@example.com');

    $this->getJson('/api/personnel')->assertUnauthorized();

    Sanctum::actingAs(makeApiUser(UserRole::PermanentStaff, 'staff-auth@example.com'));
    $this->getJson('/api/personnel')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', $personnel->id)
        ->assertJsonPath('0.role', UserRole::NationalService->value);

    Sanctum::actingAs($personnel);
    $this->getJson('/api/personnel')->assertForbidden();
});

test('staff can cancel only their own pending orders', function () {
    $staff = makeApiUser(UserRole::PermanentStaff, 'staff-cancel@example.com');
    $otherStaff = makeApiUser(UserRole::PermanentStaff, 'other-staff-cancel@example.com');
    $personnel = makeApiUser(UserRole::NationalService, 'nsp-cancel@example.com');
    $product = Product::factory()->create();
    $order = $staff->orders()->create([
        'product_id' => $product->id,
        'personnel_id' => $personnel->id,
        'product_name' => $product->name,
        'product_price' => $product->price,
        'amount' => 1,
        'total_price' => $product->price,
        'status' => OrderStatus::Pending,
    ]);

    Sanctum::actingAs($otherStaff);
    $this->patchJson("/api/orders/{$order->id}/cancel")->assertForbidden();

    Sanctum::actingAs($staff);
    $this->patchJson("/api/orders/{$order->id}/cancel")
        ->assertOk()
        ->assertJsonPath('order.status', OrderStatus::Cancelled->value);

    expect(Order::findOrFail($order->id)->status)->toBe(OrderStatus::Cancelled);
});
