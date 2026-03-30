<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_addresses')) {
            return;
        }

        Schema::create('user_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name');
            $table->string('company_name')->nullable();

            $table->string('line_one');
            $table->string('line_two')->nullable();
            $table->string('line_three')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postcode');

            $table->foreignId('country_id')->nullable()->constrained('lunar_countries')->nullOnDelete();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();

            $table->boolean('is_default')->default(false);
            $table->timestamps();

            $table->index(['user_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_addresses');
    }
};
