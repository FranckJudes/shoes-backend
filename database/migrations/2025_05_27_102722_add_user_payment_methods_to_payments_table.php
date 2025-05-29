<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Pour SQLite, nous devons utiliser une approche différente car il ne supporte pas ALTER COLUMN
        // Nous allons ajouter les nouvelles colonnes sans modifier les existantes
        Schema::table('payments', function (Blueprint $table) {
            // Ajouter user_id pour lier les méthodes de paiement aux utilisateurs
            $table->foreignId('user_id')->nullable()->after('id');
            
            // Ajouter payment_details pour stocker les détails de la méthode de paiement (numéro de carte masqué, etc.)
            $table->text('payment_details')->nullable()->after('payment_method');
            
            // Ajouter is_default pour indiquer la méthode de paiement par défaut
            $table->boolean('is_default')->default(false)->after('status');
            
            // Ajouter une contrainte de clé étrangère pour user_id
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Supprimer la contrainte de clé étrangère pour user_id
            $table->dropForeign(['user_id']);
            
            // Supprimer les colonnes ajoutées
            $table->dropColumn(['user_id', 'payment_details', 'is_default']);
        });
    }
};
