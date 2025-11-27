<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('invitations') && !Schema::hasColumn('invitations', 'role')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->string('role')->default('staff')->after('email');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('invitations') && Schema::hasColumn('invitations', 'role')) {
            Schema::table('invitations', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
