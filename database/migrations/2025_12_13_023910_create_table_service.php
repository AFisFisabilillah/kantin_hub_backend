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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('service_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('laptop_brand');
            $table->string('laptop_model');
            $table->text('complaint');
            $table->unsignedBigInteger('service_cost')->default(0); // jasa
            $table->unsignedBigInteger('total_cost')->default(0);   // jasa + sparepart
            $table->enum('status', ['received','process','done','taken'])->default('received');
            $table->json('images')->nullable(); // ARRAY gambar laptop
            $table->timestamps();
            $table->softDeletes();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('table_service');
    }
};
