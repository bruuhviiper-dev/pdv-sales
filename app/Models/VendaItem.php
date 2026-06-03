<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VendaItem extends Model
{
    protected $table = 'venda_itens';

    protected $fillable = [
        'venda_id', 'produto_id', 'produto_nome', 'preco_unitario',
        'preco_custo', 'quantidade', 'desconto', 'subtotal',
    ];

    protected $casts = [
        'preco_unitario' => 'float',
        'preco_custo' => 'float',
        'desconto' => 'float',
        'subtotal' => 'float',
    ];

    public function venda()
    {
        return $this->belongsTo(Venda::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }
}
