<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gps_alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('traccar_event_id')->unique();
            $table->unsignedBigInteger('device_id')->index();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('geofence_id')->nullable()->index();
            $table->string('type', 80)->index();
            $table->string('priority', 20)->default('info')->index();
            $table->timestampTz('event_time')->index();
            $table->json('attributes')->nullable();
            $table->timestamps();
        });

        Schema::create('gps_alert_reads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gps_alert_id')->constrained('gps_alerts')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('read_at');
            $table->unique(['gps_alert_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_alert_reads');
        Schema::dropIfExists('gps_alerts');
    }
};
