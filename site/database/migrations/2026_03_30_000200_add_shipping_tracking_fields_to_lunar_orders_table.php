<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tableName = config('lunar.database.table_prefix', 'lunar_').'orders';

        Schema::table($tableName, function (Blueprint $table) {
            if (! Schema::hasColumn($table->getTable(), 'shipping_tracking_number')) {
                $table->string('shipping_tracking_number')->nullable()->after('status');
            }

            if (! Schema::hasColumn($table->getTable(), 'tracking_company')) {
                $table->string('tracking_company')->nullable()->after('shipping_tracking_number');
            }

            if (! Schema::hasColumn($table->getTable(), 'shipped_at')) {
                $table->timestamp('shipped_at')->nullable()->after('tracking_company');
            }
        });
    }

    public function down(): void
    {
        $tableName = config('lunar.database.table_prefix', 'lunar_').'orders';

        Schema::table($tableName, function (Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'shipped_at')) {
                $table->dropColumn('shipped_at');
            }

            if (Schema::hasColumn($table->getTable(), 'tracking_company')) {
                $table->dropColumn('tracking_company');
            }

            if (Schema::hasColumn($table->getTable(), 'shipping_tracking_number')) {
                $table->dropColumn('shipping_tracking_number');
            }
        });
    }
};
