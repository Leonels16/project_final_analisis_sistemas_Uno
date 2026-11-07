<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

{
    public function up(): void
{
    Schema::create('asii_27_reportes_operativos', function (Blueprint $table) {
        $table->id();
        $table->string('codigo_reporte', 20)->unique();
        $table->string('area_servicio', 100); // Ej: Emergencias, Laboratorio, Quirofano
        $table->string('titulo', 150);
        $table->text('descripcion');
        $table->enum('nivel_prioridad', ['baja', 'media', 'alta', 'critica'])->default('media');
        $table->enum('estado', ['pendiente', 'en_revision', 'resuelto'])->default('pendiente');
        $table->date('fecha_incidente');
        $table->timestamps();
    });
}
    });
}
        Schema::create('asii_27_reportes_operativos', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asii_27_reportes_operativos');
    }
};
