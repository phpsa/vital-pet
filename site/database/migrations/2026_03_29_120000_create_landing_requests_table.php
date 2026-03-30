<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('landing_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_id')->unique();
            $table->string('merchant_order_id')->nullable()->index();
            $table->text('return_url');
            $table->text('gateway_return_url')->nullable();
            $table->string('status')->default('received')->index();
            $table->json('payload')->nullable();
            $table->json('gateway_payload')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('gateway_posted_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_requests');
    }
};