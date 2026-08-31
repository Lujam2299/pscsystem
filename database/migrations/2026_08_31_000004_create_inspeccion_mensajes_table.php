<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeccion_mensajes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('caso_id')->constrained('inspeccion_revision_casos')->cascadeOnDelete();
            $table->string('origen', 30)->default('manual');
            $table->string('external_id')->nullable();
            $table->string('conversacion')->nullable();
            $table->string('remitente')->nullable();
            $table->dateTime('fecha_mensaje');
            $table->string('tipo', 20)->default('texto');
            $table->text('texto')->nullable();
            $table->boolean('incluido')->default(true);
            $table->string('estado', 30)->default('pendiente');
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['origen', 'external_id']);
            $table->index(['caso_id', 'fecha_mensaje']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion_mensajes');
    }
};
