<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            // Nullable target customer group for staff invitations.
            // When set, the invited user is automatically assigned to this group on registration.
            $table->unsignedBigInteger('customer_group_id')->nullable()->after('is_staff_invite');
            $table->foreign('customer_group_id')
                ->references('id')
                ->on(config('lunar.database.table_prefix', 'lunar_').'customer_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign(['customer_group_id']);
            $table->dropColumn('customer_group_id');
        });
    }
};
