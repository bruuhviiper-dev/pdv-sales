<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->string('nfce_status')->nullable()->after('status'); // null, processando, autorizada, erro
            $table->string('nfce_chave', 60)->nullable()->after('nfce_status');
            $table->string('nfce_url')->nullable()->after('nfce_chave');
            $table->text('nfce_mensagem')->nullable()->after('nfce_url');
        });
    }

    public function down(): void
    {
        Schema::table('vendas', function (Blueprint $table) {
            $table->dropColumn(['nfce_status', 'nfce_chave', 'nfce_url', 'nfce_mensagem']);
        });
    }
};
