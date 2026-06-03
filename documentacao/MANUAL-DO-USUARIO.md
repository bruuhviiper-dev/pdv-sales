# Manual do Usuário — Sistema PDV

## Bem-vindo ao Sistema PDV!

Este manual explica como usar o sistema de forma simples e rápida. Não é necessário nenhum conhecimento de informática avançado.

---

## Índice

1. [Como fazer login](#login)
2. [Dashboard — Visão geral](#dashboard)
3. [Como fazer uma venda no PDV](#pdv)
4. [Como cadastrar produtos](#produtos)
5. [Como dar entrada no estoque](#estoque)
6. [Como fechar o caixa](#caixa)
7. [Como gerar relatórios](#relatorios)
8. [Como fazer backup](#backup)
9. [Usuários e perfis](#usuarios)

---

## 1. Como fazer login {#login}

1. Abra o navegador (Chrome, Firefox, Edge)
2. Digite o endereço do sistema
3. Na tela de login, informe seu **e-mail** e **senha**
4. Clique em **Entrar**

**Credenciais padrão:**
- Administrador: `admin@admin.com` / `admin123`
- Operador: `operador@sistema.com` / `operador123`

> ⚠️ **IMPORTANTE:** Altere a senha padrão após o primeiro acesso!

---

## 2. Dashboard — Visão geral {#dashboard}

Ao entrar no sistema, você verá o **Dashboard** com:

- **Receita Hoje** — total vendido no dia
- **Vendas Hoje** — quantas vendas foram feitas
- **Receita do Mês** — total do mês atual
- **Gráfico de vendas** — dos últimos 30 dias
- **Estoque crítico** — produtos com pouco estoque
- **Contas a vencer** — contas que vencem nos próximos 7 dias

---

## 3. Como fazer uma venda no PDV {#pdv}

O **PDV (Ponto de Venda)** é a tela principal de vendas.

### Passo a passo:

**1. Abra o caixa** (se ainda não estiver aberto)
- Vá em **Caixa** no menu
- Informe o saldo inicial (dinheiro em caixa)
- Clique em **Abrir Caixa**

**2. Acesse o PDV**
- Clique em **PDV — Caixa** no menu lateral

**3. Adicione produtos**
- Digite o nome ou código de barras no campo de busca
- Clique no produto para adicioná-lo ao carrinho
- Use os botões **+** e **−** para ajustar a quantidade

**4. Selecione o cliente** (opcional)
- Clique em "— Consumidor Final —" e selecione o cliente

**5. Escolha a forma de pagamento**
- Dinheiro, PIX, Cartão Débito, Cartão Crédito ou Fiado
- Se dinheiro: informe o valor recebido para calcular o troco

**6. Finalize a venda**
- Clique em **Finalizar Venda**
- O sistema deduz o estoque automaticamente

---

## 4. Como cadastrar produtos {#produtos}

1. Clique em **Produtos** no menu
2. Clique em **Novo Produto**
3. Preencha os dados:
   - **Nome** (obrigatório)
   - **Código de barras** (opcional, mas recomendado)
   - **Categoria** (organiza seus produtos)
   - **Preço de custo** e **Preço de venda**
   - **Estoque atual** e **Estoque mínimo** (para alertas)
4. Clique em **Salvar Produto**

### Dica — Estoque Mínimo:
Configure um estoque mínimo para receber alertas quando o produto estiver acabando. Por exemplo: se você sempre mantém 5 unidades de um produto, defina o mínimo como 5.

---

## 5. Como dar entrada no estoque {#estoque}

Sempre que receber mercadorias, registre a entrada no sistema:

1. Clique em **Estoque** no menu
2. Clique em **Registrar Movimentação**
3. Selecione o produto
4. Tipo: **Entrada**
5. Informe a quantidade recebida
6. Informe o custo unitário (opcional, mas ajuda nos relatórios de lucro)
7. Clique em **Registrar**

O estoque será atualizado automaticamente.

---

## 6. Como fechar o caixa {#caixa}

No final do dia (ou turno):

1. Clique em **Caixa** no menu
2. Veja o caixa aberto no topo da tela
3. Adicione observações se quiser (opcional)
4. Clique em **Fechar Caixa**
5. O sistema calculará automaticamente:
   - Total de vendas em dinheiro
   - Total em PIX
   - Total em cartão
   - Saldo final em caixa

> O relatório do caixa mostra todas as vendas do turno.

---

## 7. Como gerar relatórios {#relatorios}

Clique em **Relatórios** no menu e escolha:

- **Relatório de Vendas** — vendas por período, formas de pagamento
- **Produtos Mais Vendidos** — ranking com lucro bruto
- **Ranking de Clientes** — melhores clientes
- **Inventário de Estoque** — valor total do estoque

### Como filtrar por período:
1. Abra o relatório desejado
2. Selecione a **Data Início** e **Data Fim**
3. Clique em **Filtrar**
4. Para imprimir: clique no botão **Imprimir**

---

## 8. Como fazer backup {#backup}

### Backup pelo phpMyAdmin (recomendado):
1. Acesse o phpMyAdmin do seu servidor
2. Selecione o banco de dados do sistema
3. Clique em **Exportar**
4. Deixe as configurações padrão e clique **Executar**
5. Salve o arquivo `.sql` em local seguro

### Com que frequência fazer backup?
- Recomendamos backup **diário** ou **semanal** dependendo do volume de vendas
- Guarde os backups em pen drive, HD externo ou Google Drive

---

## 9. Usuários e perfis {#usuarios}

O sistema tem 3 tipos de usuário:

| Perfil | O que pode fazer |
|--------|-----------------|
| **Administrador** | Acesso total ao sistema |
| **Operador de Caixa** | PDV, vendas, abertura/fechamento de caixa |
| **Estoquista** | Produtos, estoque, relatórios |

### Como criar um novo usuário:
1. Vá em **Configurações** → **Usuários**
2. Clique em **Novo Usuário**
3. Preencha nome, e-mail, senha e perfil
4. Clique em **Criar Usuário**

---

## Atalhos Úteis

| Tecla | Ação |
|-------|------|
| No PDV, clique no produto | Adicionar ao carrinho |
| Botão Imprimir | Imprimir relatório/recibo |
| Menu lateral | Navegar entre módulos |

---

*Sistema PDV v1.0 — Suporte disponível pelo Mercado Livre*
