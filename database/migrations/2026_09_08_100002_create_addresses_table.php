<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('addressable_type');
            $table->unsignedBigInteger('addressable_id');
            $table->string('street');
            $table->string('house_number', 32)->nullable();
            $table->string('postal_code', 20);
            $table->string('city');
            $table->string('country_code', 2)->default('DE');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // One address per owner (morphOne); the unique index also serves lookups.
            $table->unique(['addressable_type', 'addressable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
