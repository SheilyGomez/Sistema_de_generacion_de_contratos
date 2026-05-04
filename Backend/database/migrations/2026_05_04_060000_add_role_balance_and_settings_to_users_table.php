<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['freelancer', 'abogado'])->after('password');
            $table->decimal('balance', 10, 2)->default(0.00)->after('role');
            $table->json('settings')->nullable()->after('balance');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'balance', 'settings']);
        });
    }
};
