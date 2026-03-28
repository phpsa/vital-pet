<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CleanupCustomersOrdersSeeder extends Seeder
{
    /**
     * Remove seeded customer and order domain data.
     */
    public function run(): void
    {
        $customerUserIds = collect();

        if (Schema::hasTable('lunar_customer_user')) {
            $customerUserIds = DB::table('lunar_customer_user')
                ->pluck('user_id')
                ->filter()
                ->unique();
        }

        DB::transaction(function () use ($customerUserIds) {
            $this->deleteIfTableExists('lunar_cart_line_discount');
            $this->deleteIfTableExists('lunar_cart_lines');
            $this->deleteIfTableExists('lunar_cart_addresses');

            $this->deleteIfTableExists('lunar_order_lines');
            $this->deleteIfTableExists('lunar_order_addresses');
            $this->deleteIfTableExists('lunar_order_shipping_zone');
            $this->deleteIfTableExists('lunar_transactions');
            $this->deleteIfTableExists('lunar_stripe_payment_intents');
            $this->deleteIfTableExists('lunar_orders');
            $this->deleteIfTableExists('lunar_carts');

            $this->deleteIfTableExists('lunar_customer_discount');
            $this->deleteIfTableExists('lunar_customer_customer_group');
            $this->deleteIfTableExists('lunar_customer_user');
            $this->deleteIfTableExists('lunar_addresses');
            $this->deleteIfTableExists('lunar_customers');

            if ($customerUserIds->isNotEmpty() && Schema::hasTable('users')) {
                DB::table('users')->whereIn('id', $customerUserIds)->delete();
            }
        });
    }

    protected function deleteIfTableExists(string $table): void
    {
        if (Schema::hasTable($table)) {
            DB::table($table)->delete();
        }
    }
}
