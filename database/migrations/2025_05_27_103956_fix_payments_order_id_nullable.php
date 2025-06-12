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
        // Pour SQLite, nous devons recréer la table pour modifier une contrainte NOT NULL
        // Créer une table temporaire avec la structure souhaitée
        Schema::create('payments_new', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id')->nullable(); // Rendre order_id nullable
            $table->enum('payment_method', ['mtn', 'orange', 'paypal', 'stripe', 'card']);
            $table->text('payment_details')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Ajouter les contraintes de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
        });
        
        // Copier les données de l'ancienne table vers la nouvelle
        DB::statement('INSERT INTO payments_new (id, user_id, order_id, payment_method, payment_details, amount, transaction_id, status, is_default, created_at, updated_at) 
                       SELECT id, user_id, order_id, payment_method, payment_details, amount, transaction_id, status, is_default, created_at, updated_at FROM payments');
        
        // Supprimer l'ancienne table
        Schema::drop('payments');
        
        // Renommer la nouvelle table
        Schema::rename('payments_new', 'payments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Pour revenir en arrière, nous devons recréer la table avec order_id NOT NULL
        Schema::create('payments_old', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // order_id NOT NULL
            $table->enum('payment_method', ['mtn', 'orange', 'paypal', 'stripe', 'card']);
            $table->text('payment_details')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('transaction_id')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            
            // Ajouter les contraintes de clé étrangère
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
        
        // Copier les données de l'ancienne table vers la nouvelle (seulement celles avec order_id non NULL)
        DB::statement('INSERT INTO payments_old (id, user_id, order_id, payment_method, payment_details, amount, transaction_id, status, is_default, created_at, updated_at) 
                       SELECT id, user_id, order_id, payment_method, payment_details, amount, transaction_id, status, is_default, created_at, updated_at FROM payments WHERE order_id IS NOT NULL');
        
        // Supprimer l'ancienne table
        Schema::drop('payments');
        
        // Renommer la nouvelle table
        Schema::rename('payments_old', 'payments');
    }
};
