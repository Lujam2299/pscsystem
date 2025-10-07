<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('eventuales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('fecha');
            $table->unsignedBigInteger('subpunto_id'); // Relación con tabla `puntos`
            $table->json('turnos'); // ['dia', 'tarde', 'noche']
            $table->enum('tipo_pago', ['nomina', 'efectivo']);
            $table->text('observaciones');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('eventuales');
    }
};
