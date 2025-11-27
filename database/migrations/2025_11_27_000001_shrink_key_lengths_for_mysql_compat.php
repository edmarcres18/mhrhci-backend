<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'])) {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
            DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(191) NOT NULL');
        }

        if (Schema::hasTable('password_reset_tokens') && Schema::hasColumn('password_reset_tokens', 'email')) {
            DB::statement('ALTER TABLE `password_reset_tokens` MODIFY `email` VARCHAR(191) NOT NULL');
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'id')) {
            DB::statement('ALTER TABLE `sessions` MODIFY `id` VARCHAR(191) NOT NULL');
        }

        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'queue')) {
            DB::statement('ALTER TABLE `jobs` MODIFY `queue` VARCHAR(191) NOT NULL');
        }

        if (Schema::hasTable('invitations')) {
            if (Schema::hasColumn('invitations', 'email')) {
                DB::statement('ALTER TABLE `invitations` MODIFY `email` VARCHAR(191) NOT NULL');
            }
            if (Schema::hasColumn('invitations', 'token')) {
                DB::statement('ALTER TABLE `invitations` MODIFY `token` VARCHAR(191) NOT NULL');
            }
        }
    }

    public function down(): void
    {
        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'])) {
            return;
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'email')) {
            DB::statement('ALTER TABLE `users` MODIFY `email` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('password_reset_tokens') && Schema::hasColumn('password_reset_tokens', 'email')) {
            DB::statement('ALTER TABLE `password_reset_tokens` MODIFY `email` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('sessions') && Schema::hasColumn('sessions', 'id')) {
            DB::statement('ALTER TABLE `sessions` MODIFY `id` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('jobs') && Schema::hasColumn('jobs', 'queue')) {
            DB::statement('ALTER TABLE `jobs` MODIFY `queue` VARCHAR(255) NOT NULL');
        }

        if (Schema::hasTable('invitations')) {
            if (Schema::hasColumn('invitations', 'email')) {
                DB::statement('ALTER TABLE `invitations` MODIFY `email` VARCHAR(255) NOT NULL');
            }
            if (Schema::hasColumn('invitations', 'token')) {
                DB::statement('ALTER TABLE `invitations` MODIFY `token` VARCHAR(255) NOT NULL');
            }
        }
    }
};

