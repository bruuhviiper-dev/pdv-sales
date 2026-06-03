<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caixa extends Model
{
    protected $fillable = [
        'user_id', 'saldo_abertura', 'saldo_fechamento',
        'total_vendas', 'total_dinheiro', 'total_pix', 'total_cartao',
        'total_fiado', 'aberto_em', 'fechado_em', 'status', 'observacoes',
    ];

    protected $casts = [
        'saldo_abertura' => 'float',
        'saldo_fechamento' => 'float',
        'total_vendas' => 'float',
        'total_dinheiro' => 'float',
        'total_pix' => 'float',
        'total_cartao' => 'float',
        'total_fiado' => 'float',
        'aberto_em' => 'datetime',
        'fechado_em' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
