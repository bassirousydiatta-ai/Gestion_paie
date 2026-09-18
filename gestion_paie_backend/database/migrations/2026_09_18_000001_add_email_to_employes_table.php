<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employes', function (Blueprint $table): void {
            $table->string('email', 150)->nullable()->unique()->after('prenom');
        });
    }

    public function down(): void
    {
        Schema::table('employes', function (Blueprint $table): void {
            $table->dropUnique(['email']);
            $table->dropColumn('email');
        });
    }
};
