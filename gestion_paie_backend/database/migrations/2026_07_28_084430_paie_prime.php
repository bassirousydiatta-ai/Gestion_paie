<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paie_prime', function (Blueprint $table) {
            $table->foreignId('paie_id')
                ->constrained('paies')
                ->cascadeOnDelete();
            $table->foreignId('prime_id')
                ->constrained('primes')
                ->cascadeOnDelete();
            $table->decimal('montant_applique', 10, 2);
            $table->timestamps();

            $table->primary(['paie_id', 'prime_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paie_prime');
    }
};
