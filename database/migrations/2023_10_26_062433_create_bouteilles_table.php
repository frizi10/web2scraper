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
        Schema::create('bouteilles', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->decimal('prix', 7, 2);
            $table->string('pays');
            $table->string('format');
            $table->string('lienProduit');
            $table->string('srcImage', 512);
            $table->string('srcsetImage', 512);
            $table->string('designation');
            $table->string('degre')->nullable();
            $table->string('tauxSucre')->nullable();
            $table->string('couleur')->nullable();
            $table->string('producteur')->nullable();
            $table->string('agentPromotion')->nullable();

            $table->string('type')->nullable();
            $table->string('millesime')->nullable();
            $table->string('cepage')->nullable();
            $table->string('region')->nullable();
            $table->string('produitQuebec')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bouteilles');
    }
};
