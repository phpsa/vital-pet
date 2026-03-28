<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Lunar\Models\Product;
use Lunar\Models\ProductVariant;

class CleanupProductsSeeder extends Seeder
{
    /**
     * Remove seeded product domain data.
     */
    public function run(): void
    {
        $productModel = new Product;
        $variantModel = new ProductVariant;

        $productMorph = $productModel->getMorphClass();
        $variantMorph = $variantModel->getMorphClass();

        $productIds = Product::query()->pluck('id');
        $variantIds = ProductVariant::query()->pluck('id');

        DB::transaction(function () use ($productIds, $variantIds, $productMorph, $variantMorph) {
            if ($variantIds->isNotEmpty()) {
                if (Schema::hasTable('lunar_order_lines')) {
                    DB::table('lunar_order_lines')
                        ->where('purchasable_type', $variantMorph)
                        ->whereIn('purchasable_id', $variantIds)
                        ->delete();
                }

                if (Schema::hasTable('lunar_cart_lines')) {
                    $cartLineIds = DB::table('lunar_cart_lines')
                        ->where('purchasable_type', $variantMorph)
                        ->whereIn('purchasable_id', $variantIds)
                        ->pluck('id');

                    if ($cartLineIds->isNotEmpty() && Schema::hasTable('lunar_cart_line_discount')) {
                        DB::table('lunar_cart_line_discount')->whereIn('cart_line_id', $cartLineIds)->delete();
                    }

                    DB::table('lunar_cart_lines')->whereIn('id', $cartLineIds)->delete();
                }

                if (Schema::hasTable('lunar_prices')) {
                    DB::table('lunar_prices')
                        ->where('priceable_type', $variantMorph)
                        ->whereIn('priceable_id', $variantIds)
                        ->delete();
                }

                if (Schema::hasTable('lunar_product_option_value_product_variant')) {
                    DB::table('lunar_product_option_value_product_variant')
                        ->whereIn('variant_id', $variantIds)
                        ->delete();
                }

                if (Schema::hasTable('lunar_media_product_variant')) {
                    DB::table('lunar_media_product_variant')
                        ->whereIn('product_variant_id', $variantIds)
                        ->delete();
                }
            }

            if ($productIds->isNotEmpty()) {
                if (Schema::hasTable('lunar_collection_product')) {
                    DB::table('lunar_collection_product')->whereIn('product_id', $productIds)->delete();
                }

                if (Schema::hasTable('lunar_customer_group_product')) {
                    DB::table('lunar_customer_group_product')->whereIn('product_id', $productIds)->delete();
                }

                if (Schema::hasTable('lunar_product_product_option')) {
                    DB::table('lunar_product_product_option')->whereIn('product_id', $productIds)->delete();
                }

                if (Schema::hasTable('lunar_product_associations')) {
                    DB::table('lunar_product_associations')
                        ->whereIn('product_parent_id', $productIds)
                        ->orWhereIn('product_target_id', $productIds)
                        ->delete();
                }

                if (Schema::hasTable('lunar_urls')) {
                    DB::table('lunar_urls')
                        ->where('element_type', $productMorph)
                        ->whereIn('element_id', $productIds)
                        ->delete();
                }
            }

            if (Schema::hasTable('media')) {
                DB::table('media')
                    ->whereIn('model_type', [$productMorph, $variantMorph])
                    ->delete();
            }

            if ($variantIds->isNotEmpty()) {
                ProductVariant::query()->whereIn('id', $variantIds)->delete();
            }

            if ($productIds->isNotEmpty()) {
                Product::query()->whereIn('id', $productIds)->delete();
            }

            if (Schema::hasTable('lunar_product_option_values')) {
                DB::table('lunar_product_option_values')->delete();
            }

            if (Schema::hasTable('lunar_product_options')) {
                DB::table('lunar_product_options')->delete();
            }

            if (Schema::hasTable('lunar_brands')) {
                $orphanBrandIds = DB::table('lunar_brands')
                    ->leftJoin('lunar_products', 'lunar_brands.id', '=', 'lunar_products.brand_id')
                    ->whereNull('lunar_products.id')
                    ->pluck('lunar_brands.id');

                if ($orphanBrandIds->isNotEmpty()) {
                    DB::table('lunar_brands')->whereIn('id', $orphanBrandIds)->delete();
                }
            }
        });
    }
}
