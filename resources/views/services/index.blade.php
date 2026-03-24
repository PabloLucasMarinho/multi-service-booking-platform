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
    <form id="service-form" action="{{ route('services.store') }}" method="POST">
      @csrf
      <input type="hidden" id="form-method" name="_method" value="">
      <input type="hidden" id="service-uuid" value="">

      <div class="row">
        <x-adminlte-input
          id="name"
          name="name"
          label="Nome *"
          placeholder="p.ex. Corte Máquina"
          value="{{ old('name') }}"
          autocomplete="off"
          fgroup-class="col-md-4"
          required
        />

        <x-adminlte-input
          id="price"
          name="price"
          label="Preço *"
          placeholder="p.ex. 50,00"
          value="{{ old('price') }}"
          autocomplete="off"
          fgroup-class="col-md-4"
          required
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">R$</div>
          </x-slot>
        </x-adminlte-input>

        <x-tag-input id="categories" label="Categorias" placeholder="Nova categoria..." col-size="4"/>
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          id="btn-cancel-edit"
          type="button"
          label="Cancelar"
          theme="secondary"
          icon="fas fa-times"
          class="mr-2 d-none"
        />
        <x-adminlte-button
          id="btn-submit"
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
        'language' => ['url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json'],
      ];
    @endphp

    <x-adminlte-datatable id="servicesTable" :heads="$heads" :config="$config" hoverable striped>
      @foreach($services as $service)
        <tr>
          <td>{{ $service->name }}</td>
          <td>R${{ $service->price_formatted }}</td>
          <td>
            @foreach($service->categories as $category)
              <span class="badge badge-primary mr-1 mb-1">
                {{ $category->name }}
              </span>
            @endforeach
          </td>

          <td class="d-flex">
            <button
              class="btn btn-info mr-2 btn-edit-service"
              type="button"
              data-uuid="{{ $service->uuid }}"
              data-name="{{ $service->name }}"
              data-price="{{ $service->price_formatted }}"
              data-categories="{{ $service->categories->pluck('name')->toJson() }}"
              title="Editar"
            >
              <i class="fas fa-xg fa-pen"></i>
            </button>

            <x-adminlte-button
              data-toggle="modal"
              data-target="#removeServiceModal-{{ $service->uuid }}"
              theme="danger"
              icon="fas fa-xg fa-trash-alt"
              title="Apagar"
            />
          </td>
        </tr>

        <x-adminlte-modal
          id="removeServiceModal-{{ $service->uuid }}" title="Apagar Serviço" theme="danger"
          icon="fas fa-trash" size="md"
        >
          <p>Tem certeza que quer apagar o serviço <strong>{{ $service->name }}</strong>?</p>
          <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

          <x-slot name="footerSlot">
            <form action="{{ route('services.destroy', $service) }}" method="POST">
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

        if (hidden.find(`input[value="${name}"]`).length) {
          toastr.warning('Tag já adicionada.');
          return;
        }

        list.append(`
          <span class="badge badge-primary mr-1 mb-1">
            ${name}
            <i class="fas fa-times ml-1 remove-tag"
                style="cursor:pointer" data-name="${name}" data-target="${targetId}"
            ></i>
          </span>
        `);

        hidden.append(`<input type="hidden" name="${targetId}[]" value="${name}">`);

        input.val('');
      });

      $(document).on('click', '.remove-tag', function () {
        const name = $(this).data('name');
        const targetId = $(this).data('target');

        $(this).closest('.badge').remove();
        $(`#${targetId}-hidden input[value="${name}"]`).remove();
      });

      $(document).on('click', '.btn-edit-service', function () {
        const uuid = $(this).data('uuid');
        const name = $(this).data('name');
        const price = $(this).data('price');
        // Limpa as categorias atuais
        $('#categories-list').empty();
        $('#categories-hidden').empty();

        // Popula com as categorias do serviço
        const categories = JSON.parse($(this).attr('data-categories'));
        categories.forEach(function (name) {
          $('#categories-list').append(`
            <span class="badge badge-primary mr-1 mb-1">
                ${name}
                <i class="fas fa-times ml-1 remove-tag"
                    style="cursor:pointer" data-name="${name}" data-target="categories"
                ></i>
            </span>
          `);
          $('#categories-hidden').append(`<input type="hidden" name="categories[]" value="${name}">`);
        });

        $('#service-form').attr('action', `/services/${uuid}`);
        $('#form-method').val('PUT');
        $('#service-uuid').val(uuid);

        $('#name').val(name);
        $('#price').val(price);

        $('#btn-submit').text('Atualizar');
        $('#btn-cancel-edit').removeClass('d-none');

        $('html, body').animate({scrollTop: 0}, 500);
      });

      $('#btn-cancel-edit').on('click', function () {
        $('#service-form').attr('action', '{{ route('services.store') }}');
        $('#form-method').val('');
        $('#service-uuid').val('');
        $('#name').val('');
        $('#price').val('');
        $('#categories-list').empty();
        $('#categories-hidden').empty();
        $('#btn-submit').text('Cadastrar');
        $('#btn-cancel-edit').addClass('d-none');
      });

      $('#categories-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          $('.btn-add-tag').trigger('click');
        }
      });
    });
  </script>
@stop
