@php
    $nome   = \App\Models\Configuracao::get('empresa_nome', 'Sistema PDV');
    $cnpj   = \App\Models\Configuracao::get('empresa_cnpj');
    $ie     = \App\Models\Configuracao::get('empresa_ie');
    $tel    = \App\Models\Configuracao::get('empresa_telefone');
    $end    = \App\Models\Configuracao::get('empresa_endereco');
    $cidade = \App\Models\Configuracao::get('empresa_cidade');
    $rodape = \App\Models\Configuracao::get('recibo_rodape', 'Obrigado pela preferência! Volte sempre.');

    $logo = \App\Models\Configuracao::get('empresa_logo');
    $temLogo = $logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo);

    $qtdItens = $venda->itens->sum('quantidade');

    // Código de autenticação (apenas conferência — cupom não fiscal)
    $autenticacao = strtoupper(substr(hash('sha256', $venda->id.'|'.$venda->numero_venda.'|'.$venda->total.'|'.$venda->created_at), 0, 16));

    // QR Code PIX (somente se a venda foi paga em PIX e a chave está configurada)
    $pixChave   = \App\Models\Configuracao::get('pix_chave');
    $pixPayload = null;
    if ($venda->forma_pagamento === 'pix' && !empty($pixChave) && $venda->total > 0) {
        $pixPayload = \App\Services\Pix::payload(
            $pixChave,
            \App\Models\Configuracao::get('pix_beneficiario', $nome),
            \App\Models\Configuracao::get('pix_cidade', $cidade ?: 'CIDADE'),
            (float) $venda->total,
            preg_replace('/[^A-Za-z0-9]/', '', $venda->numero_venda)
        );
    }

    // Texto do WhatsApp
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
        body { background:#e2e8f0; font-family:'Segoe UI',sans-serif; padding:1.5rem 0; margin:0; }

        .acoes { max-width:302px; margin:0 auto 1rem; display:flex; gap:.5rem; }
        .acoes .btn { flex:1; border-radius:.6rem; }

        .recibo {
            width:302px; margin:0 auto; background:#fff; padding:20px 22px;
            box-shadow:0 8px 30px rgba(0,0,0,.18); border-radius:10px;
            font-family:'Courier New', ui-monospace, monospace; color:#000;
            font-size:12px; line-height:1.55;
        }
        .r-center { text-align:center; }
        .r-logo   { max-width:120px; max-height:70px; object-fit:contain; margin:0 auto 6px; display:block; }
        .r-nome   { font-size:17px; font-weight:700; letter-spacing:.5px; margin:0; }
        .r-sub    { font-size:10.5px; color:#222; }
        .r-badge  { display:inline-block; border:1px solid #000; border-radius:4px; padding:1px 8px; font-size:10px; font-weight:700; letter-spacing:1px; margin-top:6px; }

        .r-hr  { border:0; border-top:1px dashed #888; margin:9px 0; }
        .r-hr2 { border:0; border-top:2px solid #000; margin:8px 0; }

        table { width:100%; border-collapse:collapse; }
        .r-meta td { padding:1px 0; font-size:11px; }
        .r-meta td:last-child { text-align:right; font-weight:600; }

        .r-items th { font-size:10px; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #000; padding-bottom:3px; text-align:left; }
        .r-items th.q { text-align:center; } .r-items th.t { text-align:right; }
        .r-items td { padding:3px 0; vertical-align:top; font-size:11.5px; }
        .r-items .nome { font-weight:600; }
        .r-items .unit { font-size:9.5px; color:#666; }
        .r-items .q { text-align:center; white-space:nowrap; }
        .r-items .t { text-align:right; white-space:nowrap; font-weight:600; }
        .r-items tr + tr td { border-top:1px dotted #ddd; }

        .r-tot td { font-size:11.5px; padding:1px 0; }
        .r-tot td:last-child { text-align:right; }
        .r-tot .grand td { font-size:17px; font-weight:700; padding-top:4px; }

        .r-pay td { font-size:11.5px; padding:1px 0; }
        .r-pay td:last-child { text-align:right; font-weight:600; }
        .r-pay .forma td:last-child { font-weight:700; }

        .r-pix { text-align:center; margin-top:2px; }
        .r-pix .lbl { font-size:10.5px; font-weight:700; letter-spacing:.5px; margin-bottom:5px; }
        .r-pix #recibo-qr { display:flex; justify-content:center; }
        .r-pix #recibo-qr img, .r-pix #recibo-qr canvas { width:150px !important; height:150px !important; }
        .r-pix .copia { font-size:8px; color:#444; word-break:break-all; margin-top:5px; line-height:1.3; }

        .r-auth { text-align:center; font-size:9.5px; color:#444; margin-top:6px; }
        .r-auth b { letter-spacing:1px; }

        .r-foot { text-align:center; font-size:10.5px; color:#222; margin-top:8px; }
        .r-foot .obg { font-weight:700; font-size:11.5px; margin-bottom:3px; }
        .r-cut { text-align:center; color:#bbb; font-size:9px; letter-spacing:3px; margin-top:8px; }

        @media print {
            body { background:#fff; padding:0; }
            .acoes, .no-print { display:none !important; }
            .recibo { box-shadow:none; width:76mm; border-radius:0; padding:0 3mm; font-size:11px; }
            @page { margin:3mm; size:80mm auto; }
        }
    </style>
</head>
<body>

<div class="acoes no-print">
    <button onclick="window.print()" class="btn btn-primary">
        <i class="ti ti-printer me-1"></i>Imprimir
    </button>
    <a href="{{ $waUrl }}" target="_blank" class="btn btn-success">
        <i class="ti ti-brand-whatsapp me-1"></i>WhatsApp
    </a>
</div>
<div class="acoes no-print" style="margin-top:-.5rem">
    <a href="{{ route('pdv.index') }}" class="btn btn-dark" id="btnVoltarPdv">
        <i class="ti ti-arrow-back-up me-1"></i>Voltar ao Caixa
    </a>
    <a href="{{ route('vendas.show', $venda) }}" class="btn btn-outline-secondary">
        <i class="ti ti-list-details me-1"></i>Detalhes da venda
    </a>
</div>

<div class="recibo">
    {{-- Cabeçalho --}}
    <div class="r-center">
        @if($temLogo)
            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="Logo" class="r-logo">
        @endif
        <h2 class="r-nome">{{ $nome }}</h2>
        @if($cnpj)<div class="r-sub">CNPJ: {{ $cnpj }}</div>@endif
        @if($ie)<div class="r-sub">IE: {{ $ie }}</div>@endif
        @if($end)<div class="r-sub">{{ $end }}</div>@endif
        @if($cidade)<div class="r-sub">{{ $cidade }}</div>@endif
        @if($tel)<div class="r-sub">Tel: {{ $tel }}</div>@endif
        <div class="r-badge">CUPOM NÃO FISCAL</div>
    </div>

    <hr class="r-hr">

    {{-- Dados da venda --}}
    <table class="r-meta">
        <tr><td>Venda</td><td>{{ $venda->numero_venda }}</td></tr>
        <tr><td>Data</td><td>{{ $venda->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Operador</td><td>{{ $venda->user->name }}</td></tr>
        <tr><td>Cliente</td><td>{{ $venda->cliente?->nome ?? 'Consumidor Final' }}</td></tr>
    </table>

    <hr class="r-hr">

    {{-- Itens --}}
    <table class="r-items">
        <thead>
            <tr><th>Item</th><th class="q">Qtd</th><th class="t">Total</th></tr>
        </thead>
        <tbody>
            @foreach($venda->itens as $it)
            <tr>
                <td>
                    <span class="nome">{{ $it->produto_nome }}</span><br>
                    <span class="unit">{{ moeda($it->preco_unitario) }} / un</span>
                </td>
                <td class="q">{{ $it->quantidade }}</td>
                <td class="t">{{ moeda($it->subtotal) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <hr class="r-hr">

    {{-- Totais --}}
    <table class="r-tot">
        <tr><td>Itens / Qtd</td><td>{{ $venda->itens->count() }} / {{ $qtdItens }}</td></tr>
        <tr><td>Subtotal</td><td>{{ moeda($venda->subtotal) }}</td></tr>
        @if($venda->desconto > 0)
        <tr><td>Desconto</td><td>- {{ moeda($venda->desconto) }}</td></tr>
        @endif
        <tr class="grand"><td>TOTAL</td><td>{{ moeda($venda->total) }}</td></tr>
    </table>

    <hr class="r-hr2">

    {{-- Pagamento --}}
    <table class="r-pay">
        <tr class="forma"><td>Pagamento</td><td>{{ $venda->formaPagamentoLabel() }}</td></tr>
        @if($venda->forma_pagamento === 'cartao_credito' && $venda->parcelas > 1)
        <tr><td>Parcelas</td><td>{{ $venda->parcelas }}x de {{ moeda($venda->total / $venda->parcelas) }}</td></tr>
        @endif
        @if($venda->forma_pagamento === 'dinheiro')
        <tr><td>Recebido</td><td>{{ moeda($venda->valor_pago) }}</td></tr>
        <tr><td>Troco</td><td>{{ moeda($venda->troco) }}</td></tr>
        @endif
    </table>

    @if($pixPayload)
    <hr class="r-hr">
    <div class="r-pix">
        <div class="lbl">PAGAMENTO VIA PIX</div>
        <div id="recibo-qr" data-payload="{{ $pixPayload }}"></div>
        <div class="copia">{{ $pixPayload }}</div>
    </div>
    @endif

    <hr class="r-hr">

    {{-- Autenticação + Rodapé --}}
    <div class="r-auth">Autenticação: <b>{{ $autenticacao }}</b></div>
    <div class="r-foot">
        <div class="obg">{{ $rodape }}</div>
        <div>Documento emitido em {{ now()->format('d/m/Y H:i:s') }}</div>
    </div>
    <div class="r-cut">· · · · · · · · · · · · · · · ·</div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
<script>
    // "Voltar ao Caixa": se o recibo foi aberto em nova aba pelo PDV, fecha a aba (volta ao caixa já aberto).
    document.getElementById('btnVoltarPdv')?.addEventListener('click', function (e) {
        if (window.opener && !window.opener.closed) { e.preventDefault(); window.close(); }
    });
</script>
<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>
<script>
    (function () {
        var el = document.getElementById('recibo-qr');
        if (el && el.dataset.payload && typeof QRCode !== 'undefined') {
            new QRCode(el, { text: el.dataset.payload, width: 150, height: 150, correctLevel: QRCode.CorrectLevel.M });
        }
    })();
</script>
</body>
</html>
