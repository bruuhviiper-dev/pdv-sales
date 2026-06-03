<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Conta extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'tipo', 'descricao', 'fornecedor_cliente',
        'valor', 'valor_pago', 'vencimento', 'pago_em',
        'status', 'categoria', 'observacoes',
    ];

    protected $casts = [
        'valor' => 'float',
        'valor_pago' => 'float',
        'vencimento' => 'date',
        'pago_em' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isVencida(): bool
    {
        return $this->status === 'pendente' && $this->vencimento->isPast();
    }
}
