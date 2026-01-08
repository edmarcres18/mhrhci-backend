<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
    * Run the migrations.
    */
    public function up(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->string('unsubscribe_token', 64)->nullable()->unique()->after('email');
            $table->timestamp('unsubscribed_at')->nullable()->after('updated_at');
        });

        DB::table('newsletter_subscriptions')
            ->whereNull('unsubscribe_token')
            ->orderBy('id')
            ->lazy()
            ->each(function ($row) {
                DB::table('newsletter_subscriptions')
                    ->where('id', $row->id)
                    ->update([
                        'unsubscribe_token' => Str::uuid()->toString(),
                    ]);
            });
    }

    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('newsletter_subscriptions', function (Blueprint $table) {
            $table->dropColumn(['unsubscribe_token', 'unsubscribed_at']);
        });
    }
};
