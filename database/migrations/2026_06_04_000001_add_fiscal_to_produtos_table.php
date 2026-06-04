<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->string('ncm', 8)->nullable()->after('unidade');                 // Nomenclatura Comum do Mercosul
            $table->string('cfop', 4)->nullable()->after('ncm');                    // Código Fiscal de Operações
            $table->string('cest', 7)->nullable()->after('cfop');                   // Código Especificador da Subst. Trib.
            $table->string('origem', 1)->nullable()->after('cest');                 // Origem da mercadoria (0-8)
            $table->string('situacao_tributaria', 4)->nullable()->after('origem');  // CST (normal) ou CSOSN (Simples)
        });
    }

    public function down(): void
    {
        Schema::table('produtos', function (Blueprint $table) {
            $table->dropColumn(['ncm', 'cfop', 'cest', 'origem', 'situacao_tributaria']);
        });
    }
};
