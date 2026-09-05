<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')
                ->constrained('employes')
                ->cascadeOnDelete();
            $table->foreignId('poste_id')
                ->constrained('postes')
                ->restrictOnDelete();
            $table->enum('type_contrat', ['CDI', 'CDD', 'Stage', 'Interim']);
            $table->decimal('salaire_base', 10, 2);
            $table->date('date_debut');
            $table->date('date_fin')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contrats');
    }
};
