@extends('layouts.app')

@section('subtitle', 'Clientes')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1>Clientes</h1>
      <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Clientes'],
      ]"/>
    </div>
    <a href="{{ route('clients.create') }}" class="btn btn-success">
      <i class="fa fa-fw fa-plus"></i> Cadastrar Cliente
    </a>
  </div>
@stop

@section('content')
  @php
    $heads = [
              'Nome',
              'Data de Nascimento',
              'CPF',
              'E-mail',
              'Telefone',
              ['label' => 'Ações', 'no-export' => true, 'width' => 5],
    ];

    $config = [
      'language' => [ 'url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json' ],
    ];
  @endphp

  <x-adminlte-datatable id="clientsTable" :heads="$heads" :config="$config" hoverable striped>
    @foreach($clients as $client)
      <tr>
        <td>{{$client->name}}</td>
        <td>{{$client->date_of_birth_formatted}}</td>
        <td>{{$client->document_formatted}}</td>
        <td>{{$client->email}}</td>
        <td>{{$client->phone_formatted}}</td>
        <td class="d-flex">
          @can('create', App\Models\Appointment::class)
            <a href="{{route('appointments.create', ['client' => $client->uuid])}}"
               class="btn btn-primary mr-2"
               title="Agendar"
            >
              <i class="fas fa-xg fa-calendar-alt"></i>
            </a>
          @endcan
          @can('update', $client)
            <a href="{{ route('clients.edit', $client) }}" class="btn btn-info mr-2" title="Editar">
              <i class="fas fa-xg fa-pen"></i>
            </a>
          @endcan

          @can('delete', $client)
            <x-adminlte-button
              data-toggle="modal"
              data-target="#removeClientModal-{{$client->uuid}}"
              theme="danger"
              icon="fas fa-xg fa-trash-alt"
              title="Apagar"
            />
          @endcan
        </td>
      </tr>

      <x-adminlte-modal
        id="removeClientModal-{{$client->uuid}}" title="Apagar Cliente" theme="danger"
        icon="fas fa-trash" size="md"
      >
        <p>Tem certeza que quer apagar os dados de <strong>{{$client->name}}</strong>?</p>
        <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

        <x-slot name="footerSlot">
          <form action="{{route('clients.destroy', $client)}}" method="POST">
            @method('DELETE')
            @csrf
            <x-adminlte-button class="mr-auto" theme="danger" label="Sim" type="submit"/>
          </form>
          <x-adminlte-button theme="dark" label="Não" data-dismiss="modal" autofocus/>
        </x-slot>
      </x-adminlte-modal>
    @endforeach
  </x-adminlte-datatable>
@stop
