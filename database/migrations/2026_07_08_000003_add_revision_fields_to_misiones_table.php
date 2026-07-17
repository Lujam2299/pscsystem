<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('misiones', function (Blueprint $table): void {
            $table->string('revision_estado')->default('Pendiente de revisión')->after('estatus');
            $table->text('revision_observaciones')->nullable()->after('revision_estado');
            $table->foreignId('revision_user_id')->nullable()->after('revision_observaciones')->constrained('users')->nullOnDelete();
            $table->timestamp('revision_at')->nullable()->after('revision_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('misiones', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('revision_user_id');
            $table->dropColumn([
                'revision_estado',
                'revision_observaciones',
                'revision_at',
            ]);
        });
    }
};
