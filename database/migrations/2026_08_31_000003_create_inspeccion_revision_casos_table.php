<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inspeccion_revision_casos', function (Blueprint $table): void {
            $table->id();
            $table->string('estado', 30)->default('pendiente');
            $table->foreignId('unidad_sugerida_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->foreignId('unidad_confirmada_id')->nullable()->constrained('unidades')->nullOnDelete();
            $table->foreignId('inspeccion_id')->nullable()->constrained('inspecciones_unidades')->nullOnDelete();
            $table->json('placas_candidatas')->nullable();
            $table->unsignedTinyInteger('confianza')->default(0);
            $table->text('notas_revision')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            $table->index(['estado', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inspeccion_revision_casos');
    }
};
