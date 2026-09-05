<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paies', function (Blueprint $table) {
            $table->decimal('nombre_heures_supplementaires', 6, 2)->default(0)->after('salaire_brut');
            $table->decimal('taux_majoration_heures_sup', 4, 2)->default(0.25)->after('nombre_heures_supplementaires');
            $table->decimal('montant_heures_supplementaires', 10, 2)->default(0)->after('taux_majoration_heures_sup');
        });
    }

    public function down(): void
    {
        Schema::table('paies', function (Blueprint $table) {
            $table->dropColumn([
                'nombre_heures_supplementaires',
                'taux_majoration_heures_sup',
                'montant_heures_supplementaires',
            ]);
        });
    }
};
