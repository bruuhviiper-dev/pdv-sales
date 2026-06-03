<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Categoria;

class CategoriasSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nome' => 'Mercearia', 'descricao' => 'Alimentos não perecíveis', 'cor' => '#f59e0b'],
            ['nome' => 'Bebidas', 'descricao' => 'Refrigerantes, sucos, água', 'cor' => '#3b82f6'],
            ['nome' => 'Limpeza', 'descricao' => 'Produtos de limpeza doméstica', 'cor' => '#10b981'],
            ['nome' => 'Papelaria', 'descricao' => 'Materiais de escritório e escola', 'cor' => '#8b5cf6'],
            ['nome' => 'Higiene', 'descricao' => 'Cuidados pessoais e beleza', 'cor' => '#ec4899'],
        ];

        foreach ($categorias as $cat) {
            Categoria::firstOrCreate(['nome' => $cat['nome']], array_merge($cat, ['ativo' => true]));
        }
    }
}
