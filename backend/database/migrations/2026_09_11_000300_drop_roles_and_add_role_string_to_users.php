<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('cashier')->after('password');
            });
        }

        if (Schema::hasColumn('users', 'role_id')) {
            DB::statement('UPDATE users u SET role = COALESCE((SELECT r.role::text FROM roles r WHERE r.id = u.role_id), \'cashier\') WHERE u.role_id IS NOT NULL');

            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['role_id']);
                $table->dropColumn('role_id');
            });
        }

        Schema::dropIfExists('dashboard_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }

    public function down(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('role')->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        if (!Schema::hasColumn('users', 'role_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('role_id')->nullable()->after('password')->constrained()->nullOnDelete();
            });
        }

        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};