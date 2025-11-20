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
        Schema::create('customer_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('entry_number', 10)->index();
            $table->string('name', 100);
            $table->string('hospital', 120);
            $table->string('address', 200);
            $table->string('position', 80);
            $table->string('contact_number', 11);
            $table->string('email');
            $table->text('products_interest');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_registrations');
    }
};