<?php

use App\Services\OrderService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote()); 
});

Schedule::call(function (OrderService $orders) {
    $orders->cancelPendingOrders();
})->dailyAt('00:00')->name('daily:cancel-orders')->withoutOverlapping();