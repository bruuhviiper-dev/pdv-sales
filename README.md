# Sistema PDV + Gestão Comercial

Sistema completo de ponto de venda e gestão comercial desenvolvido em Laravel 11. Pronto para pequenos comércios: mercadinhos, lojas, papelarias, padarias e qualquer tipo de negócio.

## Módulos Incluídos

- **PDV (Frente de Caixa)** — Vendas em tempo real com Livewire, 5 formas de pagamento, troco automático
- **Dashboard** — Gráficos de vendas (Chart.js), receita do dia/mês, alertas de estoque crítico
- **Produtos** — Cadastro com foto, código de barras, margens de lucro automáticas
- **Estoque** — Entrada/saída, histórico de movimentações, alertas de mínimo
- **Clientes** — Cadastro, histórico de compras, controle de fiado
- **Financeiro** — Contas a pagar/receber, fechamento de caixa
- **Relatórios** — Vendas por período, produtos mais vendidos, inventário
- **Configurações** — Dados da empresa, usuários com 3 perfis de acesso

## Stack Tecnológica

| Tecnologia | Versão | Uso |
|-----------|--------|-----|
| Laravel | 11.x | Framework PHP |
| PHP | 8.2+ | Backend |
| MySQL | 5.7+ | Banco de dados |
| Bootstrap | 5.3 | Interface (CDN) |
| Livewire | 3.x | PDV em tempo real |
| Chart.js | 4.x | Gráficos do dashboard |
| Spatie Permission | 6.x | Controle de acesso |

## Requisitos

- PHP 8.2 ou superior
- Composer 2.x
- MySQL 5.7 ou superior
- Node.js (apenas para compilar assets em desenvolvimento)

## Instalação para Desenvolvimento

```bash
# Clone o repositório
git clone https://github.com/bruuhviiper-dev/template-monster-1.git sistema-pdv
cd sistema-pdv

# Instale as dependências
composer install

# Configure o ambiente
cp .env.example .env
php artisan key:generate

# Configure o banco no .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)

# Execute as migrations com dados de exemplo
php artisan migrate --seed

# Inicie o servidor
php artisan serve
```

Acesse: http://localhost:8000

**Login padrão:** `admin@admin.com` / `admin123`

## Usuários Padrão

| Usuário | E-mail | Senha | Permissões |
|---------|--------|-------|-----------|
| Administrador | admin@admin.com | admin123 | Total |
| Operador de Caixa | operador@sistema.com | operador123 | PDV, Caixa |
| Estoquista | estoque@sistema.com | estoque123 | Produtos, Estoque |

## Screenshots

> Screenshots do sistema em funcionamento:
>
> 1. `docs/screenshots/dashboard.png` — Dashboard com gráficos
> 2. `docs/screenshots/pdv.png` — Tela do PDV
> 3. `docs/screenshots/produtos.png` — Lista de produtos
> 4. `docs/screenshots/relatorios.png` — Relatórios

## Instalação em Hospedagem

Consulte o arquivo `INSTALAR.txt` para instruções detalhadas de instalação em hospedagem compartilhada (Hostinger, Locaweb, etc.).

O sistema inclui um **instalador web** (`install.php`) que guia o usuário por toda a configuração sem necessidade de terminal.

## Estrutura do Projeto

```
sistema-pdv/
├── app/
│   ├── Http/Controllers/    # Controllers dos módulos
│   ├── Livewire/Pdv.php     # Componente PDV em tempo real
│   └── Models/              # Modelos Eloquent
├── database/
│   ├── migrations/          # Estrutura do banco
│   └── seeders/             # Dados de exemplo
├── resources/views/         # Templates Blade
│   ├── layouts/app.blade.php
│   ├── pdv/, produtos/, clientes/...
├── routes/web.php           # Rotas da aplicação
├── install.php              # Instalador web
├── INSTALAR.txt             # Guia hospedagem
├── INSTALAR-LOCALHOST.txt   # Guia local
└── documentacao/            # Manual do usuário
```

## Licença

Licença Comercial — para uso em projetos próprios ou de clientes.
Redistribuição ou revenda do código fonte não é permitida sem autorização.

---

*Desenvolvido com Laravel 11 | Bootstrap 5 | Livewire 3*
