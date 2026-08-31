<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeccion_mensaje_archivos', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mensaje_id')->constrained('inspeccion_mensajes')->cascadeOnDelete();
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->timestamps();

            $table->index(['mensaje_id', 'orden']);
            $table->index('sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion_mensaje_archivos');
    }
};
