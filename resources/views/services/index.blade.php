@extends('layouts.app')

@section('subtitle', 'Serviços')

@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)
@section('plugins.InputMask', true)

@section('content_header')
  <div class="d-flex align-items-center">
    <div>
      <h1>Serviços</h1>
      <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Serviços'],
      ]"/>
    </div>
  </div>
@stop

@section('content')
  <x-adminlte-card title="Cadastro de serviços" theme="primary" icon="fas fa-clipboard-list">
    <form action="{{route('services.store')}}" method="POST">
      @csrf

      <div class="row">
        <x-adminlte-input
          id="name"
          name="name"
          label="Nome *"
          placeholder="p.ex. Corte Máquina"
          value="{{old('name')}}"
          autocomplete="off"
          fgroup-class="col-md-4"
          required
        />

        <x-adminlte-input
          id="price"
          name="price"
          label="Preço *"
          placeholder="p.ex. 50,00"
          value="{{old('price')}}"
          autocomplete="off"
          fgroup-class="col-md-4"
          required
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              R$
            </div>
          </x-slot>
        </x-adminlte-input>

        <x-tag-input id="categories" label="Categorias" placeholder="Nova categoria..." col-size="4"/>
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

  <div class="mb-2">
    @php
      $heads = [
                'Nome',
                'Preço',
                'Categorias',
                ['label' => 'Ações', 'no-export' => true, 'width' => 5],
      ];

      $config = [
        'language' => [ 'url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json' ],
      ];
    @endphp

    <x-adminlte-datatable id="servicesTable" :heads="$heads" :config="$config" hoverable striped>
      @foreach($services as $service)
        <tr>
          <td>{{$service->name}}</td>
          <td>R${{$service->price_formatted}}</td>
          <td>
            @foreach($service->categories as $category)
              <span class="badge badge-primary mr-1 mb-1" data-slug="{{ $category->slug }}">
                {{ $category->name }}
                <i class="fas fa-times ml-1 delete-category" style="cursor:pointer"></i>
            </span>
            @endforeach
          </td>

          <td class="d-flex">
            <a href="{{ route('services.edit', $service) }}" class="btn btn-info mr-2">Editar</a>

            <x-adminlte-button
              data-toggle="modal"
              data-target="#removeServiceModal"
              theme="danger"
              label="Apagar"
            />
          </td>
        </tr>

        <x-adminlte-modal
          id="removeServiceModal" title="Apagar Serviço" theme="danger"
          icon="fas fa-trash" size="md"
        >
          <p>Tem certeza que quer apagar os dados de <strong>{{$service->name}}</strong>?</p>
          <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

          <x-slot name="footerSlot">
            <form action="{{route('services.destroy', $service)}}" method="POST">
              @method('DELETE')
              @csrf
              <x-adminlte-button class="mr-auto" theme="danger" label="Sim" type="submit"/>
            </form>
            <x-adminlte-button theme="dark" label="Não" data-dismiss="modal" autofocus/>
          </x-slot>
        </x-adminlte-modal>
      @endforeach
    </x-adminlte-datatable>
  </div>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      $('#price').inputmask('currency', {
        prefix: '',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        rightAlign: false,
      });

      $(document).on('click', '.btn-add-tag', function () {
        const targetId = $(this).data('target');
        const input = $(`#${targetId}-input`);
        const name = input.val().trim();
        if (!name) return;

        const list = $(`#${targetId}-list`);
        const hidden = $(`#${targetId}-hidden`);

        // Evita duplicatas visuais
        if (hidden.find(`input[value="${name}"]`).length) {
          toastr.warning('Tag já adicionada.');
          return;
        }

        // Adiciona badge visual
        list.append(`
        <span class="badge badge-primary mr-1 mb-1">
            ${name}
            <i class="fas fa-times ml-1 remove-tag" style="cursor:pointer" data-name="${name}" data-target="${targetId}"></i>
        </span>
    `);

        // Adiciona campo hidden para enviar no form
        hidden.append(`<input type="hidden" name="${targetId}[]" value="${name}">`);

        input.val('');
      });

      $(document).on('click', '.remove-tag', function () {
        const name = $(this).data('name');
        const targetId = $(this).data('target');

        $(this).closest('.badge').remove();
        $(`#${targetId}-hidden input[value="${name}"]`).remove();
      });
    });
  </script>
@stop
