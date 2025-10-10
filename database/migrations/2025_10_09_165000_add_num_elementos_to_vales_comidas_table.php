<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vales_comidas', function (Blueprint $table) {
            $table->integer('num_elementos')->after('monto')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('vales_comidas', function (Blueprint $table) {
            $table->dropColumn('num_elementos');
        });
    }
};
