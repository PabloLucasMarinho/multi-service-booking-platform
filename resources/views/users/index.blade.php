@extends('layouts.app')

@section('subtitle', 'Funcionários')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1>Funcionários</h1>
      <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Funcionários'],
      ]"/>
    </div>
    <a href="{{ route('users.create') }}" class="btn btn-success">
      <i class="fa fa-fw fa-plus"></i> Cadastrar Funcionário
    </a>
  </div>
@stop

@section('content')
  @php
    $heads = [
              'Nome',
              'E-mail',
              'Cargo',
              ['label' => 'Ações', 'no-export' => true, 'width' => 5],
    ];

    $config = [
      'language' => [ 'url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json' ],
    ];
  @endphp

  <x-adminlte-datatable id="usersTable" :heads="$heads" :config="$config" hoverable striped>
    @foreach($users as $user)
      <tr>
        <td>{{$user->name}}</td>
        <td>{{$user->email}}</td>
        <td>{{$user->role->name}}</td>
        <td class="d-flex">
          <a href="{{ route('users.show', $user) }}" class="btn btn-primary mr-2" title="Ver">
            <i class="fas fa-xg fa-eye"></i>
          </a>

          <a href="{{ route('users.edit', $user) }}" class="btn btn-info mr-2" title="Editar">
            <i class="fas fa-xg fa-pen"></i>
          </a>
          
          <x-adminlte-button
            data-toggle="modal"
            data-target="#removeClientModal"
            theme="danger"
            icon="fas fa-xg fa-trash-alt"
            title="Apagar"
          />

        </td>
      </tr>

      <x-adminlte-modal
        id="removeClientModal" title="Apagar Cliente" theme="danger"
        icon="fas fa-trash" size="md"
      >
        <p>Tem certeza que quer apagar os dados de <strong>{{$user->name}}</strong>?</p>
        <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

        <x-slot name="footerSlot">
          <form action="{{route('users.destroy', $user)}}" method="POST">
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
