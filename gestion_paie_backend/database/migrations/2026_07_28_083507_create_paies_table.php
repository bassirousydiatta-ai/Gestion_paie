<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employe_id')
                ->constrained('employes')
                ->cascadeOnDelete();
            $table->unsignedTinyInteger('mois');
            $table->unsignedSmallInteger('annee');
            $table->decimal('salaire_brut', 10, 2)->default(0);
            $table->decimal('nombre_heures_supplementaires', 6, 2)->default(0);
            $table->decimal('taux_majoration_heures_sup', 4, 2)->default(0.25);
            $table->decimal('montant_heures_supplementaires', 10, 2)->default(0);
            $table->decimal('total_cotisations', 10, 2)->default(0);
            $table->decimal('total_retenues', 10, 2)->default(0);
            $table->decimal('impot_revenu', 10, 2)->default(0);
            $table->decimal('salaire_net', 10, 2)->default(0);
            $table->enum('statut', ['brouillon', 'validee'])->default('brouillon');
            $table->timestamps();

            $table->unique(['employe_id', 'mois', 'annee']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paies');
    }
};
