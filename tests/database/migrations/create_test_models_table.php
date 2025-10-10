<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->id();
            $table->integer('price')->default(0);  // Stores cents: 1999 = $19.99
            $table->integer('cost')->default(0);   // Stores cents: 1500 = $15.00
            $table->integer('tax')->default(0);    // Stores cents: 250 = $2.50
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('test_models');
    }
};
