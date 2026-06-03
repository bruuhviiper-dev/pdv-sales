<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_venda')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('desconto', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->string('forma_pagamento')->default('dinheiro'); // dinheiro, pix, cartao_debito, cartao_credito, fiado
            $table->decimal('valor_pago', 10, 2)->default(0);
            $table->decimal('troco', 10, 2)->default(0);
            $table->integer('parcelas')->default(1);
            $table->enum('status', ['concluida', 'cancelada', 'pendente'])->default('concluida');
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendas');
    }
};
