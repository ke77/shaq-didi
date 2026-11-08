<?php

namespace App\Console\Commands;

use App\Services\OrderService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:cancel-pending-orders')]
#[Description('Command description')]
class CancelPendingOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        protected $signature = 'orders:cancel-pending';

        protected $description = 'Cancel every order that is still pending';

        public function handle(OrderService $orders): int
        {
            $count = $orders->cancelPending();

            Log::info('pending orders cancelled', ['count' => $count]);
            $this->info('Cancelled ' . $count . ' pending orders.');

            return self::SUCCESS;

            $this->info("Cancelled {$cancelledOrdersCount} pending orders.");
        }
    }
}
