<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'categoria_id', 'nome', 'codigo_barras', 'sku', 'descricao', 'foto',
        'preco_custo', 'preco_venda', 'margem_lucro', 'estoque_atual',
        'estoque_minimo', 'unidade', 'controla_estoque', 'ativo',
        'ncm', 'cfop', 'cest', 'origem', 'situacao_tributaria',
    ];

    protected $casts = [
        'preco_custo' => 'float',
        'preco_venda' => 'float',
        'margem_lucro' => 'float',
        'controla_estoque' => 'boolean',
        'ativo' => 'boolean',
    ];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function movimentacoes()
    {
        return $this->hasMany(EstoqueMovimentacao::class);
    }

    public function vendaItens()
    {
        return $this->hasMany(VendaItem::class);
    }

    public function isEstoqueCritico(): bool
    {
        return $this->controla_estoque && $this->estoque_atual <= $this->estoque_minimo;
    }

    public function calcularMargem(): float
    {
        if ($this->preco_custo <= 0) return 0;
        return round((($this->preco_venda - $this->preco_custo) / $this->preco_custo) * 100, 2);
    }
}
