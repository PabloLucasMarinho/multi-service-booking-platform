@extends('layouts.app')

@section('subtitle', 'Clientes')
@section('content_header')
  <h1>Clientes</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Clientes', 'url' => route('clients.index')],
    ['label' => 'Cadastrar Cliente'],
  ]"/>
@stop

@section('content_body')
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

        <x-adminlte-input
          id="date_of_birth"
          name="date_of_birth"
          label="Data de Nascimento"
          placeholder="Digite o data de nascimento do cliente"
          value="{{old('date_of_birth')}}"
          autocomplete="bday"
          fgroup-class="col-md-4"
          required
        />

        <x-adminlte-input
          id="email"
          name="email"
          label="E-mail"
          placeholder="Digite o e-mail do cliente"
          value="{{old('email')}}"
          autocomplete="email"
          fgroup-class="col-md-4"
        />

        <x-adminlte-input
          id="phone"
          name="phone"
          label="Telefone"
          placeholder="Digite o telefone do cliente"
          value="{{old('phone')}}"
          autocomplete="tel-local"
          fgroup-class="col-md-4"
        />
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

      $('#phone').inputmask('(99) 99999-9999');

    });

    $(document).ready(function () {

      $('#document').inputmask('999.999.999-99');

    });

    $(document).ready(function () {

      $('#date_of_birth').inputmask('99/99/9999');

    });

  </script>

@stop
