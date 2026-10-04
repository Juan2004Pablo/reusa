<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('community', 120)->nullable()->after('phone');
            $table->string('role', 20)->default('user')->index()->after('community');
            $table->boolean('is_active')->default(true)->index()->after('role');
            $table->timestamp('terms_accepted_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropColumn(['phone', 'community', 'role', 'is_active', 'terms_accepted_at']);
        });
    }
};
