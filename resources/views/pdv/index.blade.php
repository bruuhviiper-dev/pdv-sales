@extends('layouts.app')
@section('title', 'PDV — Frente de Caixa')
@section('breadcrumb')
    <li class="breadcrumb-item active">PDV</li>
@endsection
@section('content')
    @livewire('pdv')
@endsection
