@extends('layouts.app')

@section('subtitle', $user->name)

@section('content_header')
  <h1>Dados Cadastrais</h1>

  <x-breadcrumb :items="array_values(array_filter([
      ['label' => 'Dashboard', 'url' => route('home')],
      auth()->user()->uuid !== $user->uuid ? ['label' => 'Funcionários', 'url' => route('users.index')] : null,
      ['label' => auth()->user()->name === $user->name ? 'Perfil' : $user->name],
    ]))"
  />
@stop

@section('content')
  <x-adminlte-card body-class="p-0">
    <div class="d-flex justify-content-between align-items-center bg-primary rounded-top mb-3 p-4">
      <div class="d-flex align-items-center">
        <div class="rounded-circle d-flex align-items-center justify-content-center mr-3"
             style="width:52px;height:52px;background:#e3f2fd;font-size:18px;font-weight:500;color:#1565c0;flex-shrink:0;">
          {{ $user->initials }}
        </div>
        <div>
          <p class="mb-0 font-weight-bold" style="font-size:18px;">{{ $user->name }}</p>
          <small class="text-white">{{$user->role->name}}</small>
        </div>
      </div>

      <div>
        <a href="{{route('users.edit', $user)}}" class="btn btn-light">
          <i class="fas fa-pen"></i>
        </a>
      </div>
    </div>

    {{-- Dados Pessoais --}}
    <div class="card border mb-3 mx-4">
      <div class="card-header py-2 px-3 d-flex align-items-center">
        <i class="fas fa-info-circle fa-sm mr-2 text-muted"></i>
        <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Dados Pessoais</small>
      </div>
      <div class="card-body p-0">
        <div class="row no-gutters">
          <div class="col-md-4 p-3 border-right">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Data de
              Nascimento</p>
            <strong>{{ $user->details->date_of_birth_formatted }}</strong>
          </div>
          <div class="col-md-4 p-3 border-right">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">E-mail</p>
            <strong>{{ $user->email }}</strong>
          </div>
          <div class="col-md-4 p-3">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">
              Telefone</p>
            <strong>{{ $user->details->phone_formatted }}</strong>
          </div>
        </div>
      </div>
    </div>

    {{-- Endereço --}}
    <div class="card border mb-3 mx-4">
      <div class="card-header py-2 px-3 d-flex align-items-center">
        <i class="fas fa-home fa-sm mr-2 text-muted"></i>
        <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Endereço</small>
      </div>
      <div class="card-body p-3">
        <p class="mb-0 font-weight-bold">
          {{ $user->details->address }}, {{ $user->details->address_complement }}
        </p>
        <small class="text-muted">
          {{ $user->details->neighborhood }} · {{ $user->details->city }} · {{ $user->details->zip_code_formatted }}
        </small>
      </div>
    </div>

    {{-- Dados de Contrato --}}
    <div class="card border mb-4 mx-4">
      <div class="card-header py-2 px-3 d-flex align-items-center">
        <i class="fas fa-briefcase fa-sm mr-2 text-muted"></i>
        <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Dados de
          Contrato</small>
      </div>
      <div class="card-body p-0">
        <div class="row no-gutters">
          <div class="col-md-6 p-3 border-right">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Salário</p>
            <span style="font-size:20px;font-weight:500;">R$ {{ $user->details->salary_formatted }}</span>
          </div>
          <div class="col-md-6 p-3">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Data de
              Admissão</p>
            <span style="font-size:20px;font-weight:500;">{{ $user->details->admission_date_formatted }}</span>
          </div>
        </div>
      </div>
    </div>
  </x-adminlte-card>
@stop
