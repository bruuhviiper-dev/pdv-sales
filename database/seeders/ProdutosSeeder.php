<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Produto;
use App\Models\Categoria;

class ProdutosSeeder extends Seeder
{
    public function run(): void
    {
        $mercearia = Categoria::where('nome', 'Mercearia')->first()->id;
        $bebidas = Categoria::where('nome', 'Bebidas')->first()->id;
        $limpeza = Categoria::where('nome', 'Limpeza')->first()->id;
        $papelaria = Categoria::where('nome', 'Papelaria')->first()->id;
        $higiene = Categoria::where('nome', 'Higiene')->first()->id;

        $produtos = [
            // Mercearia
            ['nome' => 'Arroz Tipo 1 5kg', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567001', 'preco_custo' => 18.50, 'preco_venda' => 28.90, 'estoque_atual' => 45, 'estoque_minimo' => 10, 'unidade' => 'PC'],
            ['nome' => 'Feijão Carioca 1kg', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567002', 'preco_custo' => 7.20, 'preco_venda' => 11.90, 'estoque_atual' => 38, 'estoque_minimo' => 10, 'unidade' => 'PC'],
            ['nome' => 'Açúcar Refinado 1kg', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567003', 'preco_custo' => 3.80, 'preco_venda' => 5.90, 'estoque_atual' => 52, 'estoque_minimo' => 15, 'unidade' => 'PC'],
            ['nome' => 'Óleo de Soja 900ml', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567004', 'preco_custo' => 6.50, 'preco_venda' => 9.90, 'estoque_atual' => 30, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            ['nome' => 'Macarrão Espaguete 500g', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567005', 'preco_custo' => 2.80, 'preco_venda' => 4.50, 'estoque_atual' => 60, 'estoque_minimo' => 20, 'unidade' => 'PC'],
            ['nome' => 'Sal Refinado 1kg', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567006', 'preco_custo' => 1.50, 'preco_venda' => 2.90, 'estoque_atual' => 40, 'estoque_minimo' => 10, 'unidade' => 'PC'],
            ['nome' => 'Farinha de Trigo 1kg', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567007', 'preco_custo' => 4.20, 'preco_venda' => 6.50, 'estoque_atual' => 25, 'estoque_minimo' => 10, 'unidade' => 'PC'],
            ['nome' => 'Café Torrado 250g', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567008', 'preco_custo' => 8.90, 'preco_venda' => 13.90, 'estoque_atual' => 22, 'estoque_minimo' => 8, 'unidade' => 'PC'],
            ['nome' => 'Biscoito Cream Cracker 200g', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567009', 'preco_custo' => 2.20, 'preco_venda' => 3.80, 'estoque_atual' => 48, 'estoque_minimo' => 15, 'unidade' => 'PC'],
            ['nome' => 'Leite Integral UHT 1L', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567010', 'preco_custo' => 4.80, 'preco_venda' => 7.50, 'estoque_atual' => 72, 'estoque_minimo' => 20, 'unidade' => 'UN'],
            ['nome' => 'Margarina 500g', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567011', 'preco_custo' => 5.50, 'preco_venda' => 8.90, 'estoque_atual' => 18, 'estoque_minimo' => 8, 'unidade' => 'PC'],
            ['nome' => 'Molho de Tomate 340g', 'categoria_id' => $mercearia, 'codigo_barras' => '7891234567012', 'preco_custo' => 2.50, 'preco_venda' => 4.20, 'estoque_atual' => 35, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            // Bebidas
            ['nome' => 'Refrigerante Cola 2L', 'categoria_id' => $bebidas, 'codigo_barras' => '7891234567020', 'preco_custo' => 5.80, 'preco_venda' => 8.90, 'estoque_atual' => 36, 'estoque_minimo' => 12, 'unidade' => 'UN'],
            ['nome' => 'Água Mineral 500ml', 'categoria_id' => $bebidas, 'codigo_barras' => '7891234567021', 'preco_custo' => 0.80, 'preco_venda' => 2.00, 'estoque_atual' => 120, 'estoque_minimo' => 30, 'unidade' => 'UN'],
            ['nome' => 'Suco de Laranja 1L', 'categoria_id' => $bebidas, 'codigo_barras' => '7891234567022', 'preco_custo' => 4.50, 'preco_venda' => 7.90, 'estoque_atual' => 24, 'estoque_minimo' => 8, 'unidade' => 'UN'],
            ['nome' => 'Cerveja Lata 350ml', 'categoria_id' => $bebidas, 'codigo_barras' => '7891234567023', 'preco_custo' => 2.80, 'preco_venda' => 4.50, 'estoque_atual' => 48, 'estoque_minimo' => 24, 'unidade' => 'UN'],
            ['nome' => 'Energético 250ml', 'categoria_id' => $bebidas, 'codigo_barras' => '7891234567024', 'preco_custo' => 4.00, 'preco_venda' => 7.00, 'estoque_atual' => 20, 'estoque_minimo' => 6, 'unidade' => 'UN'],
            // Limpeza
            ['nome' => 'Detergente Neutro 500ml', 'categoria_id' => $limpeza, 'codigo_barras' => '7891234567030', 'preco_custo' => 1.80, 'preco_venda' => 3.50, 'estoque_atual' => 40, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            ['nome' => 'Sabão em Pó 1kg', 'categoria_id' => $limpeza, 'codigo_barras' => '7891234567031', 'preco_custo' => 8.50, 'preco_venda' => 13.90, 'estoque_atual' => 22, 'estoque_minimo' => 8, 'unidade' => 'PC'],
            ['nome' => 'Água Sanitária 1L', 'categoria_id' => $limpeza, 'codigo_barras' => '7891234567032', 'preco_custo' => 2.80, 'preco_venda' => 4.90, 'estoque_atual' => 30, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            ['nome' => 'Esponja de Louça c/3', 'categoria_id' => $limpeza, 'codigo_barras' => '7891234567033', 'preco_custo' => 2.50, 'preco_venda' => 4.50, 'estoque_atual' => 3, 'estoque_minimo' => 5, 'unidade' => 'PC'],
            ['nome' => 'Multiuso Spray 500ml', 'categoria_id' => $limpeza, 'codigo_barras' => '7891234567034', 'preco_custo' => 5.50, 'preco_venda' => 9.90, 'estoque_atual' => 15, 'estoque_minimo' => 6, 'unidade' => 'UN'],
            // Papelaria
            ['nome' => 'Caderno 100 folhas', 'categoria_id' => $papelaria, 'codigo_barras' => '7891234567040', 'preco_custo' => 8.00, 'preco_venda' => 14.90, 'estoque_atual' => 20, 'estoque_minimo' => 5, 'unidade' => 'UN'],
            ['nome' => 'Caneta Esferográfica Azul', 'categoria_id' => $papelaria, 'codigo_barras' => '7891234567041', 'preco_custo' => 0.80, 'preco_venda' => 2.00, 'estoque_atual' => 60, 'estoque_minimo' => 20, 'unidade' => 'UN'],
            ['nome' => 'Lápis Preto HB c/12', 'categoria_id' => $papelaria, 'codigo_barras' => '7891234567042', 'preco_custo' => 5.50, 'preco_venda' => 9.90, 'estoque_atual' => 12, 'estoque_minimo' => 5, 'unidade' => 'CX'],
            ['nome' => 'Borracha Branca', 'categoria_id' => $papelaria, 'codigo_barras' => '7891234567043', 'preco_custo' => 0.50, 'preco_venda' => 1.50, 'estoque_atual' => 0, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            ['nome' => 'Tesoura Escolar', 'categoria_id' => $papelaria, 'codigo_barras' => '7891234567044', 'preco_custo' => 4.00, 'preco_venda' => 7.90, 'estoque_atual' => 8, 'estoque_minimo' => 3, 'unidade' => 'UN'],
            // Higiene
            ['nome' => 'Shampoo 400ml', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567050', 'preco_custo' => 8.50, 'preco_venda' => 14.90, 'estoque_atual' => 18, 'estoque_minimo' => 6, 'unidade' => 'UN'],
            ['nome' => 'Sabonete 90g', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567051', 'preco_custo' => 1.20, 'preco_venda' => 2.50, 'estoque_atual' => 45, 'estoque_minimo' => 15, 'unidade' => 'UN'],
            ['nome' => 'Creme Dental 90g', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567052', 'preco_custo' => 2.80, 'preco_venda' => 5.50, 'estoque_atual' => 32, 'estoque_minimo' => 10, 'unidade' => 'UN'],
            ['nome' => 'Desodorante Aerosol 150ml', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567053', 'preco_custo' => 8.00, 'preco_venda' => 13.90, 'estoque_atual' => 2, 'estoque_minimo' => 5, 'unidade' => 'UN'],
            ['nome' => 'Papel Higiênico c/4', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567054', 'preco_custo' => 5.50, 'preco_venda' => 9.90, 'estoque_atual' => 28, 'estoque_minimo' => 10, 'unidade' => 'PC'],
            ['nome' => 'Absorvente c/8', 'categoria_id' => $higiene, 'codigo_barras' => '7891234567055', 'preco_custo' => 3.80, 'preco_venda' => 6.90, 'estoque_atual' => 15, 'estoque_minimo' => 6, 'unidade' => 'PC'],
        ];

        foreach ($produtos as $produto) {
            $margem = $produto['preco_custo'] > 0
                ? round((($produto['preco_venda'] - $produto['preco_custo']) / $produto['preco_custo']) * 100, 2)
                : 0;

            Produto::firstOrCreate(
                ['codigo_barras' => $produto['codigo_barras']],
                array_merge($produto, ['margem_lucro' => $margem, 'ativo' => true, 'controla_estoque' => true])
            );
        }
    }
}
