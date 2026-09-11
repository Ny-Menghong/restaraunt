<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE orders DROP CONSTRAINT IF EXISTS orders_status_check');
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE orders ADD CONSTRAINT orders_status_check CHECK ((status)::text = ANY (ARRAY[('pending'::character varying)::text, ('ready'::character varying)::text]))");
    }
};