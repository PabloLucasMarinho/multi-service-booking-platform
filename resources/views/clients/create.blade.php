@extends('layouts.app')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('subtitle', 'Cadastrar Cliente')
@section('content_header')
  <h1>Cadastrar Cliente</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Clientes', 'url' => route('clients.index')],
    ['label' => 'Cadastrar Cliente'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Cadastro de Cliente" theme="primary" icon="fas fa-users">
    <form action="{{route('clients.store')}}" method="POST">
      @csrf

      <div class="row">
        <x-adminlte-input
          name="name"
          label="Nome"
          placeholder="Digite o nome do cliente"
          value="{{old('name')}}"
          autocomplete="name"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="document"
          name="document"
          label="CPF"
          placeholder="Digite o CPF do cliente"
          value="{{old('document')}}"
          autocomplete="on"
          fgroup-class="col-md-6"
          required
        />

        @php
          $config = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
            'daysOfWeekDisabled' => [0, 6],
            'dayViewHeaderFormat' => 'MMM YYYY',
            'viewMode' => 'years'
          ];
        @endphp
        <x-adminlte-input-date
          id="date_of_birth" name="date_of_birth" :config="$config" label="Data de Nascimento *"
          placeholder="Escolha uma data..." fgroup-class="col-md-4" autocomplete="off"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-input
          id="email"
          name="email"
          label="E-mail"
          placeholder="p.ex. joao@gmail.com"
          value="{{old('email')}}"
          autocomplete="email"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-at"></i>
            </div>
          </x-slot>
        </x-adminlte-input>

        <x-adminlte-input
          id="phone"
          name="phone"
          label="Telefone"
          placeholder="p.ex. (21)91234-5678"
          value="{{old('phone')}}"
          autocomplete="tel-national"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-phone-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input>
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          type="submit"
          label="Cadastrar"
          theme="success"
          icon="fas fa-save"
        />
      </div>
    </form>
  </x-adminlte-card>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      $('#phone').inputmask('(99)99999-9999');
      $('#document').inputmask('999.999.999-99');
    });
  </script>
@stop
