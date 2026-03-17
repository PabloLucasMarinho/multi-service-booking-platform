@extends('layouts.app')

@section('subtitle', 'Editar Cliente')
@section('content_header')
  <h1>Editar Cliente</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Clientes', 'url' => route('clients.index')],
    ['label' => 'Editar Cliente'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Editar Cliente" theme="primary" icon="fas fa-users">
    <form action="{{route('clients.update', $client)}}" method="POST">
      @method('PUT')
      @csrf

      <div class="row">
        <x-adminlte-input
          name="name"
          label="Nome"
          placeholder="Digite o nome do cliente"
          value="{{$client->name}}"
          autocomplete="name"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="document"
          name="document"
          label="CPF"
          placeholder="Digite o CPF do cliente"
          value="{{$client->document}}"
          autocomplete="on"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="date_of_birth"
          name="date_of_birth"
          label="Data de Nascimento"
          placeholder="Digite o data de nascimento do cliente"
          value="{{$client->date_of_birth_formatted}}"
          autocomplete="bday"
          fgroup-class="col-md-4"
          required
        />

        <x-adminlte-input
          id="email"
          name="email"
          label="E-mail"
          placeholder="Digite o e-mail do cliente"
          value="{{$client->email}}"
          autocomplete="email"
          fgroup-class="col-md-4"
        />

        <x-adminlte-input
          id="phone"
          name="phone"
          label="Telefone"
          placeholder="Digite o telefone do cliente"
          value="{{$client->phone}}"
          autocomplete="tel-local"
          fgroup-class="col-md-4"
        />
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          type="submit"
          label="Editar"
          theme="success"
          icon="fas fa-pen"
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
