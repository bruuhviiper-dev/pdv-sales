<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias')->nullOnDelete();
            $table->string('nome');
            $table->string('codigo_barras')->nullable()->unique();
            $table->string('sku')->nullable()->unique();
            $table->text('descricao')->nullable();
            $table->string('foto')->nullable();
            $table->decimal('preco_custo', 10, 2)->default(0);
            $table->decimal('preco_venda', 10, 2);
            $table->decimal('margem_lucro', 5, 2)->default(0);
            $table->integer('estoque_atual')->default(0);
            $table->integer('estoque_minimo')->default(5);
            $table->string('unidade', 10)->default('UN');
            $table->boolean('controla_estoque')->default(true);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
