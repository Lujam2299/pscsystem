<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeccion_evidencias', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('inspeccion_id')->constrained('inspecciones_unidades')->cascadeOnDelete();
            $table->string('disk', 30)->default('local');
            $table->string('path');
            $table->string('nombre_original');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size');
            $table->char('sha256', 64);
            $table->unsignedSmallInteger('orden')->default(1);
            $table->string('clasificacion', 40)->default('general');
            $table->timestamps();

            $table->index(['inspeccion_id', 'orden']);
            $table->index('sha256');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion_evidencias');
    }
};
