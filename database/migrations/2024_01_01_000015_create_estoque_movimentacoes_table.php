<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('estoque_movimentacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('venda_id')->nullable()->constrained('vendas')->nullOnDelete();
            $table->enum('tipo', ['entrada', 'saida', 'ajuste', 'devolucao']);
            $table->integer('quantidade');
            $table->integer('estoque_anterior');
            $table->integer('estoque_atual');
            $table->decimal('custo_unitario', 10, 2)->nullable();
            $table->string('motivo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('estoque_movimentacoes');
    }
};
