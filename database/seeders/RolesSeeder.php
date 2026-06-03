<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'pdv.acessar', 'vendas.ver', 'vendas.cancelar',
            'produtos.ver', 'produtos.criar', 'produtos.editar', 'produtos.excluir',
            'estoque.ver', 'estoque.movimentar',
            'clientes.ver', 'clientes.criar', 'clientes.editar', 'clientes.excluir',
            'caixa.ver', 'caixa.abrir', 'caixa.fechar',
            'financeiro.ver', 'financeiro.gerenciar',
            'relatorios.ver',
            'configuracoes.ver', 'configuracoes.editar',
            'usuarios.gerenciar',
        ];

        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $operador = Role::firstOrCreate(['name' => 'operador']);
        $operador->givePermissionTo([
            'pdv.acessar', 'vendas.ver',
            'produtos.ver',
            'clientes.ver', 'clientes.criar',
            'caixa.ver', 'caixa.abrir', 'caixa.fechar',
            'estoque.ver',
        ]);

        $estoquista = Role::firstOrCreate(['name' => 'estoquista']);
        $estoquista->givePermissionTo([
            'produtos.ver', 'produtos.criar', 'produtos.editar',
            'estoque.ver', 'estoque.movimentar',
            'relatorios.ver',
        ]);
    }
}
