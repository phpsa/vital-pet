<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invitations', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->string('token', 64)->unique();
            // Null when the invite was sent by a staff member via the admin panel.
            $table->unsignedBigInteger('invited_by_user_id')->nullable();
            $table->foreign('invited_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->boolean('is_staff_invite')->default(false);
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
