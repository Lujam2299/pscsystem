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
        Schema::create('comprobante_vales', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vale_comida_id');
            $table->string('archivo');
            $table->decimal('monto', 10, 2);
            $table->timestamps();

            $table->foreign('vale_comida_id')->references('id')->on('vales_comidas')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobante_vales');
    }
};
