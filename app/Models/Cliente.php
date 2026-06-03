<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nome', 'cpf_cnpj', 'telefone', 'email', 'cep',
        'endereco', 'bairro', 'cidade', 'estado',
        'limite_fiado', 'saldo_fiado', 'observacoes', 'ativo',
    ];

    protected $casts = [
        'limite_fiado' => 'float',
        'saldo_fiado' => 'float',
        'ativo' => 'boolean',
    ];

    public function vendas()
    {
        return $this->hasMany(Venda::class);
    }

    public function totalCompras(): float
    {
        return $this->vendas()->where('status', 'concluida')->sum('total');
    }
}
