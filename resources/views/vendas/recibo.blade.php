@php
    $nome = \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV');
    $cnpj = \App\Models\Configuracao::get('empresa_cnpj');
    $tel  = \App\Models\Configuracao::get('empresa_telefone');
    $end  = \App\Models\Configuracao::get('empresa_endereco');
    $cidade = \App\Models\Configuracao::get('empresa_cidade');
    $rodape = \App\Models\Configuracao::get('recibo_rodape', 'Obrigado pela preferência!');

    // Monta texto do WhatsApp
    $linhas = [];
    $linhas[] = "*{$nome}*";
    $linhas[] = "Recibo {$venda->numero_venda}";
    $linhas[] = $venda->created_at->format('d/m/Y H:i');
    $linhas[] = "--------------------------------";
    foreach ($venda->itens as $it) {
        $linhas[] = "{$it->quantidade}x {$it->produto_nome} - " . moeda($it->subtotal);
    }
    $linhas[] = "--------------------------------";
    if ($venda->desconto > 0) $linhas[] = "Desconto: -" . moeda($venda->desconto);
    $linhas[] = "*TOTAL: " . moeda($venda->total) . "*";
    $linhas[] = "Pagamento: " . $venda->formaPagamentoLabel();
    if ($venda->troco > 0) $linhas[] = "Troco: " . moeda($venda->troco);
    $linhas[] = "";
    $linhas[] = $rodape;
    $waText = rawurlencode(implode("\n", $linhas));
    $waFone = preg_replace('/\D/', '', $venda->cliente->telefone ?? '');
    $waUrl  = 'https://wa.me/' . ($waFone ? '55'.$waFone : '') . '?text=' . $waText;
@endphp
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo {{ $venda->numero_venda }}</title>
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/tabler/tabler-icons.min.css') }}">
    @include('partials.favicon')
    <style>
        body { background:#e2e8f0; font-family:'Segoe UI',sans-serif; padding:1.5rem 0; }
        .acoes { max-width:380px; margin:0 auto 1rem; display:flex; gap:.5rem; }
        .acoes .btn { flex:1; }
        .recibo {
            width:320px; margin:0 auto; background:#fff; padding:18px 20px;
            box-shadow:0 6px 24px rgba(0,0,0,.15); border-radius:8px;
            font-family:'Courier New', monospace; color:#111; font-size:13px; line-height:1.5;
        }
        .recibo .center { text-align:center; }
        .recibo h2 { font-size:16px; font-weight:700; margin:0; }
        .recibo .sub { font-size:11px; color:#333; }
        .recibo hr { border:0; border-top:1px dashed #999; margin:8px 0; }
        .recibo table { width:100%; font-size:12px; }
        .recibo table td { padding:1px 0; vertical-align:top; }
        .recibo .tot { font-size:15px; font-weight:700; }
        .recibo .qr { margin:8px auto 0; }
        @media print {
            body { background:#fff; padding:0; }
            .acoes, .no-print { display:none !important; }
            .recibo { box-shadow:none; width:80mm; border-radius:0; padding:0 4mm; }
            @page { margin:4mm; }
        }
    </style>
</head>
<body>

<div class="acoes no-print">
    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success">
        <i class="ti ti-brand-whatsapp me-1"></i>Enviar WhatsApp
    </a>
    <button onclick="window.print()" class="btn btn-primary">
        <i class="ti ti-printer me-1"></i>Imprimir
    </button>
    <a href="{{ route('vendas.show', $venda) }}" class="btn btn-outline-secondary">
        <i class="ti ti-arrow-left"></i>
    </a>
</div>

<div class="recibo">
    <div class="center">
        <h2>{{ $nome }}</h2>
        @if($cnpj)<div class="sub">CNPJ: {{ $cnpj }}</div>@endif
        @if($end)<div class="sub">{{ $end }}</div>@endif
        @if($cidade)<div class="sub">{{ $cidade }}</div>@endif
        @if($tel)<div class="sub">Tel: {{ $tel }}</div>@endif
    </div>
    <hr>
    <div class="center" style="font-weight:700">COMPROVANTE DE VENDA</div>
    <table>
        <tr><td>Venda:</td><td style="text-align:right">{{ $venda->numero_venda }}</td></tr>
        <tr><td>Data:</td><td style="text-align:right">{{ $venda->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Operador:</td><td style="text-align:right">{{ $venda->user->name }}</td></tr>
        <tr><td>Cliente:</td><td style="text-align:right">{{ $venda->cliente?->nome ?? 'Consumidor Final' }}</td></tr>
    </table>
    <hr>
    <table>
        <thead>
            <tr style="border-bottom:1px solid #000">
                <td>Item</td><td style="text-align:center">Qtd</td><td style="text-align:right">Total</td>
            </tr>
        </thead>
        <tbody>
            @foreach($venda->itens as $it)
            <tr>
                <td>{{ $it->produto_nome }}<br><span style="font-size:10px;color:#555">{{ moeda($it->preco_unitario) }}/un</span></td>
                <td style="text-align:center">{{ $it->quantidade }}</td>
                <td style="text-align:right">{{ moeda($it->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table>
        <tr><td>Subtotal:</td><td style="text-align:right">{{ moeda($venda->subtotal) }}</td></tr>
        @if($venda->desconto > 0)
        <tr><td>Desconto:</td><td style="text-align:right">- {{ moeda($venda->desconto) }}</td></tr>
        @endif
        <tr class="tot"><td>TOTAL:</td><td style="text-align:right">{{ moeda($venda->total) }}</td></tr>
    </table>
    <hr>
    <table>
        <tr><td>Pagamento:</td><td style="text-align:right">{{ $venda->formaPagamentoLabel() }}</td></tr>
        @if($venda->forma_pagamento === 'dinheiro')
        <tr><td>Recebido:</td><td style="text-align:right">{{ moeda($venda->valor_pago) }}</td></tr>
        <tr><td>Troco:</td><td style="text-align:right">{{ moeda($venda->troco) }}</td></tr>
        @endif
    </table>
    <hr>
    <div class="center sub">{{ $rodape }}</div>
    <div class="center sub" style="margin-top:6px">{{ now()->format('d/m/Y H:i:s') }}</div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
