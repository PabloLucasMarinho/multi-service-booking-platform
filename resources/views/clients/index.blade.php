@extends('layouts.app')

@section('subtitle', 'Clientes')
@section('content_header')
  <h1>Clientes</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Clientes'],
  ]"/>
@stop

@section('content_body')
  <div class="card">

    <div class="card-header">
      <form method="GET" class="d-flex justify-content-between">
        <div class="input-group input-group-sm" style="width: 250px;">
          <input type="text"
                 name="search"
                 value="{{ request('search') }}"
                 class="form-control"
                 placeholder="Buscar cliente">

          <div class="input-group-append">
            <button type="submit" class="btn btn-default">
              <i class="fas fa-search"></i>
            </button>
          </div>
        </div>

        <a href="{{route('clients.create')}}" class="btn btn-primary btn-sm">
          <i class="fas fa-plus"></i><span class="ml-2">Cadastrar Cliente</span>
        </a>
      </form>
    </div>

    <div class="card-body table-responsive p-0">

      <table class="table table-hover text-nowrap">

        <thead>
        <tr>
          <th>Nome</th>
          <th>Data de Nascimento</th>
          <th>CPF</th>
          <th>E-mail</th>
          <th>Telefone</th>
        </tr>
        </thead>

        <tbody>

        @forelse ($clients as $client)

          <tr>
            <td>{{ $client->name }}</td>
            <td>{{ $client->date_of_birth_formatted }}</td>
            <td>{{ $client->document_formatted }}</td>
            <td>{{ $client->email }}</td>
            <td>{{ $client->phone_formatted }}</td>
          </tr>

        @empty

          <tr>
            <td colspan="12" class="text-center">Nenhum cliente encontrado</td>
          </tr>

        @endforelse

        </tbody>

      </table>

    </div>

    <div class="card-footer clearfix">
      {{ $clients->links() }}
    </div>

  </div>
@stop
