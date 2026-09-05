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
        Schema::create('bulletin_paies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paie_id')
                ->unique()
                ->constrained('paies')
                ->cascadeOnDelete();
            $table->string('fichier_pdf', 255);
            $table->dateTime('date_generation');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bulletin_paies');
    }
};
