<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venda extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'numero_venda', 'cliente_id', 'user_id', 'subtotal', 'desconto',
        'total', 'forma_pagamento', 'valor_pago', 'troco', 'parcelas',
        'status', 'observacoes',
    ];

    protected $casts = [
        'subtotal' => 'float',
        'desconto' => 'float',
        'total' => 'float',
        'valor_pago' => 'float',
        'troco' => 'float',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function itens()
    {
        return $this->hasMany(VendaItem::class);
    }

    public static function gerarNumero(): string
    {
        $ultimo = static::withTrashed()->max('id') ?? 0;
        return 'VD' . str_pad($ultimo + 1, 6, '0', STR_PAD_LEFT);
    }

    public function formaPagamentoLabel(): string
    {
        return match($this->forma_pagamento) {
            'dinheiro' => 'Dinheiro',
            'pix' => 'PIX',
            'cartao_debito' => 'Cartão Débito',
            'cartao_credito' => 'Cartão Crédito',
            'fiado' => 'Fiado',
            default => ucfirst($this->forma_pagamento),
        };
    }
}
