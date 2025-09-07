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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('sku')->unique();
            $table->foreignId('category_id')->constrained();
            $table->string('unit')->default('pcs');
            $table->decimal('cost_price', 10, 2)->default(0);
            $table->decimal('sell_price', 10, 2)->default(0);
            $table->integer('reorder_level')->default(0);
            $table->json('metadata')->nullable();
            $table->text('description')->nullable();
            $table->string('barcode')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
