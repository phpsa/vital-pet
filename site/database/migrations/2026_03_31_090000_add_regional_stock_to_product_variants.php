<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('lunar.database.table_prefix');

        Schema::table($prefix.'product_variants', function (Blueprint $table) {
            $table->boolean('regional_stock_enabled')->default(false)->after('backorder')->index();
        });

        Schema::create($prefix.'product_variant_regional_stocks', function (Blueprint $table) use ($prefix) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained($prefix.'product_variants')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained($prefix.'countries')->cascadeOnDelete();
            $table->integer('stock')->default(0);
            $table->integer('backorder')->default(0);
            $table->timestamps();

            $table->unique(['product_variant_id', 'country_id'], 'lunar_variant_country_unique');
            $table->index(['country_id']);
        });
    }

    public function down(): void
    {
        $prefix = config('lunar.database.table_prefix');

        Schema::dropIfExists($prefix.'product_variant_regional_stocks');

        Schema::table($prefix.'product_variants', function (Blueprint $table) {
            $table->dropColumn('regional_stock_enabled');
        });
    }
};
