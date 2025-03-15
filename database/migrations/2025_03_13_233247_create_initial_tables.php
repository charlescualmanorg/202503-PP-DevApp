<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInitialTables extends Migration
{
    public function up()
    {
        // Modificar la tabla de usuarios de Laravel
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['cliente', 'conductor', 'admin'])->default('cliente');
        });

        // Tabla de conductores
        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('license_number')->unique();
            $table->enum('vehicle_type', ['sedan', 'suv', 'moto']);
            $table->timestamps();
        });

        // Tabla de vehículos
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('driver_id')->constrained('drivers')->onDelete('cascade');
            $table->string('brand');
            $table->string('model');
            $table->string('plate_number')->unique();
            $table->timestamps();
        });

        // Tabla de solicitudes de viaje
        Schema::create('rides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('driver_id')->nullable()->constrained('drivers')->onDelete('set null');
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('set null');
            $table->string('pickup_location');
            $table->string('dropoff_location');
            $table->enum('status', ['pendiente', 'aceptado', 'en_curso', 'completado', 'cancelado']);
            $table->decimal('fare', 10, 2);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('rides');
        Schema::dropIfExists('vehicles');
        Schema::dropIfExists('drivers');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('role');
        });
    }
}
