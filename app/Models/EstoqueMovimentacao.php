<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EstoqueMovimentacao extends Model
{
    protected $table = 'estoque_movimentacoes';

    protected $fillable = [
        'produto_id', 'user_id', 'venda_id', 'tipo',
        'quantidade', 'estoque_anterior', 'estoque_atual',
        'custo_unitario', 'motivo',
    ];

    protected $casts = [
        'custo_unitario' => 'float',
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }
}
