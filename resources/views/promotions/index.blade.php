@extends('layouts.app')

@section('subtitle', 'Promoções')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1>Promoções</h1>
      <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Promoções'],
      ]"/>
    </div>
    <a href="{{ route('promotions.create') }}" class="btn btn-success">
      <i class="fa fa-fw fa-plus"></i> Cadastrar Promoção
    </a>
  </div>
@stop

@section('content')
  @php
    $heads = [
              'Nome',
              'Tipo',
              'Valor',
              'Início',
              'Fim',
              'Categorias',
              'Status',
              ['label' => 'Ações', 'no-export' => true, 'width' => 5],
    ];

    $config = [
      'language' => [ 'url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json' ],
    ];
  @endphp

  <x-adminlte-datatable id="promotionsTable" :heads="$heads" :config="$config" hoverable striped>
    @foreach($promotions as $promotion)
      <tr>
        <td>{{$promotion->name}}</td>
        <td>{{$promotion->type_formatted}}</td>
        <td>{{$promotion->value_formatted}}</td>
        <td>{{$promotion->starts_at_formatted}}</td>
        <td>{{$promotion->ends_at_formatted}}</td>
        <td>
          @if($promotion->isGlobal())
            <span class="badge badge-success">Global</span>
          @else
            @foreach($promotion->categories as $category)
              <span class="badge badge-primary">
        {{ $category->name }}
      </span>
            @endforeach
          @endif
        </td>
        <td>
          <span
            class="badge {{$promotion->active ? 'badge-info' : 'badge-danger'}}">{{$promotion->active_formatted}}</span>
        </td>
        <td class="d-flex">
          <a href="{{ route('promotions.edit', $promotion) }}" class="btn btn-info mr-2" title="Editar">
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
        id="removeClientModal" title="Apagar Promoção" theme="danger"
        icon="fas fa-trash" size="md"
      >
        <p>Tem certeza que quer apagar os dados de <strong>{{$promotion->name}}</strong>?</p>
        <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

        <x-slot name="footerSlot">
          <form action="{{route('promotions.destroy', $promotion)}}" method="POST">
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
