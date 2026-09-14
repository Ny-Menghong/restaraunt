<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('orders')
            ->whereIn('status', ['preparing', 'ready', 'cancelled'])
            ->update(['status' => 'pending']);

        DB::table('orders')
            ->where('status', 'completed')
            ->update(['status' => 'confirmed']);
    }

    public function down(): void
    {
        DB::table('orders')
            ->where('status', 'confirmed')
            ->update(['status' => 'completed']);
    }
};