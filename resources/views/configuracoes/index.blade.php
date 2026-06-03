@extends('layouts.app')
@section('title', 'Configurações')
@section('breadcrumb')<li class="breadcrumb-item active">Configurações</li>@endsection
@section('content')

@php
    $logo = $configs->get('empresa_logo')?->valor;
    $temLogo = $logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo);
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold"><i class="ti ti-settings me-2 text-primary"></i>Configurações do Estabelecimento</h5>
</div>

<form method="POST" action="{{ route('configuracoes.salvar') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        {{-- Coluna esquerda: dados --}}
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><i class="ti ti-building-store me-2 text-primary"></i>Dados do Estabelecimento</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nome do Estabelecimento <span class="text-danger">*</span></label>
                            <input type="text" name="empresa_nome" value="{{ $configs->get('empresa_nome')?->valor }}"
                                class="form-control form-control-lg" placeholder="Ex: Mercadinho do João" required>
                            <div class="form-text">Aparece no menu, no login e nos recibos.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNPJ / CPF</label>
                            <input type="text" name="empresa_cnpj" value="{{ $configs->get('empresa_cnpj')?->valor }}" class="form-control" placeholder="00.000.000/0000-00">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Telefone / WhatsApp</label>
                            <input type="text" name="empresa_telefone" value="{{ $configs->get('empresa_telefone')?->valor }}" class="form-control" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="empresa_email" value="{{ $configs->get('empresa_email')?->valor }}" class="form-control" placeholder="contato@empresa.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CEP</label>
                            <input type="text" name="empresa_cep" value="{{ $configs->get('empresa_cep')?->valor }}" class="form-control" placeholder="00000-000">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="empresa_endereco" value="{{ $configs->get('empresa_endereco')?->valor }}" class="form-control" placeholder="Rua, número, bairro">
                        </div>
                        <div class="col-md-9">
                            <label class="form-label">Cidade</label>
                            <input type="text" name="empresa_cidade" value="{{ $configs->get('empresa_cidade')?->valor }}" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">UF / Região</label>
                            <input type="text" name="empresa_estado" value="{{ $configs->get('empresa_estado')?->valor }}" class="form-control" maxlength="4">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="ti ti-cash me-2 text-primary"></i>Moeda & Recibo</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Símbolo da Moeda</label>
                            <input type="text" name="moeda_simbolo" value="{{ $configs->get('moeda_simbolo')?->valor ?? 'R$' }}" class="form-control" maxlength="5" placeholder="R$">
                            <div class="form-text">Ex: R$, $, €, Kz, MZN…</div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Mensagem no rodapé do recibo</label>
                            <input type="text" name="recibo_rodape" value="{{ $configs->get('recibo_rodape')?->valor }}" class="form-control" placeholder="Ex: Obrigado pela preferência! Volte sempre.">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="ti ti-qrcode me-2 text-primary"></i>PIX — QR Code no Caixa</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">Configure sua chave PIX para gerar o QR Code automaticamente na hora da venda. O cliente paga escaneando — sem maquininha.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Chave PIX</label>
                            <input type="text" name="pix_chave" value="{{ $configs->get('pix_chave')?->valor }}" class="form-control" placeholder="CPF, CNPJ, e-mail, telefone ou aleatória">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome do Beneficiário</label>
                            <input type="text" name="pix_beneficiario" value="{{ $configs->get('pix_beneficiario')?->valor }}" class="form-control" maxlength="25" placeholder="Nome que recebe (max 25)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Cidade do Beneficiário</label>
                            <input type="text" name="pix_cidade" value="{{ $configs->get('pix_cidade')?->valor }}" class="form-control" maxlength="15" placeholder="Ex: SAO PAULO">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><i class="ti ti-receipt-tax me-2 text-primary"></i>NFC-e — Nota Fiscal (opcional)</div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Emita NFC-e direto do caixa via API (Focus NFe / NFe.io). Requer conta no provedor e certificado digital A1.
                        <a href="https://focusnfe.com.br" target="_blank">Saiba mais</a>.
                    </p>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Provedor</label>
                            <select name="nfce_provider" class="form-select">
                                <option value="focus" {{ $configs->get('nfce_provider')?->valor === 'focus' ? 'selected' : '' }}>Focus NFe</option>
                                <option value="nfeio" {{ $configs->get('nfce_provider')?->valor === 'nfeio' ? 'selected' : '' }}>NFe.io</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Ambiente</label>
                            <select name="nfce_ambiente" class="form-select">
                                <option value="homologacao" {{ $configs->get('nfce_ambiente')?->valor === 'homologacao' ? 'selected' : '' }}>Homologação (teste)</option>
                                <option value="producao" {{ $configs->get('nfce_ambiente')?->valor === 'producao' ? 'selected' : '' }}>Produção</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Token da API</label>
                            <input type="password" name="nfce_token" value="{{ $configs->get('nfce_token')?->valor }}" class="form-control" placeholder="Token do provedor">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Coluna direita: logo --}}
        <div class="col-lg-4">
            <div class="card mb-3">
                <div class="card-header"><i class="ti ti-photo me-2 text-primary"></i>Logo do Estabelecimento</div>
                <div class="card-body text-center">
                    <div class="border rounded p-3 mb-3 bg-light d-flex align-items-center justify-content-center" style="min-height:140px">
                        @if($temLogo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="Logo atual" class="img-fluid" style="max-height:120px">
                        @else
                            <div class="text-muted">
                                <i class="ti ti-photo" style="font-size:2.5rem"></i>
                                <div class="small mt-1">Nenhuma logo enviada</div>
                            </div>
                        @endif
                    </div>
                    <input type="file" name="empresa_logo" class="form-control" accept="image/*">
                    <div class="form-text">PNG ou JPG quadrado, máx. 1MB</div>
                    @error('empresa_logo')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="card">
                <div class="card-header"><i class="ti ti-link me-2 text-primary"></i>Atalhos</div>
                <div class="list-group list-group-flush">
                    <a href="{{ route('usuarios.index') }}" class="list-group-item list-group-item-action small">
                        <i class="ti ti-users-group me-2 text-primary"></i>Gerenciar Usuários
                    </a>
                    <a href="{{ route('relatorios.index') }}" class="list-group-item list-group-item-action small">
                        <i class="ti ti-chart-bar me-2 text-primary"></i>Relatórios
                    </a>
                    <a href="{{ route('estoque.historico') }}" class="list-group-item list-group-item-action small">
                        <i class="ti ti-history me-2 text-primary"></i>Histórico de Estoque
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex gap-2 mt-1">
        <button type="submit" class="btn btn-primary px-4">
            <i class="ti ti-check me-1"></i>Salvar Configurações
        </button>
        @if($temLogo)
        <button type="submit" form="form-remover-logo" class="btn btn-outline-danger">
            <i class="ti ti-trash me-1"></i>Remover Logo
        </button>
        @endif
    </div>
</form>

@if($temLogo)
<form id="form-remover-logo" method="POST" action="{{ route('configuracoes.remover-logo') }}" class="d-none"
    onsubmit="return confirm('Remover a logo atual?')">
    @csrf @method('DELETE')
</form>
@endif
@endsection
