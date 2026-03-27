@extends('layouts.app')

@section('subtitle', 'Agendamentos')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)
@section('plugins.Datatables', true)
@section('plugins.DatatablesPlugin', true)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <div>
      <h1>Agendamentos</h1>
      <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Agendamentos'],
      ]"/>
    </div>
    <a href="{{ route('appointments.create') }}" class="btn btn-success">
      <i class="fa fa-fw fa-plus"></i> Agendar
    </a>
  </div>
@stop

@section('content')
  <x-filter>
    <div class="col">
      <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">De</label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <div class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></div>
        </div>
        <input type="text" id="filter-date-from" class="form-control" placeholder="DD/MM/AAAA" autocomplete="off">
      </div>
    </div>

    <div class="col">
      <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Até</label>
      <div class="input-group input-group-sm">
        <div class="input-group-prepend">
          <div class="input-group-text bg-light"><i class="fas fa-calendar-alt"></i></div>
        </div>
        <input type="text" id="filter-date-to" class="form-control" placeholder="DD/MM/AAAA" autocomplete="off">
      </div>
    </div>

    <div class="col-auto">
      <button type="button" id="btn-apply-filters" class="btn btn-primary btn-sm">
        <i class="fas fa-search mr-1"></i> Filtrar
      </button>
    </div>
  </x-filter>

  @php
    $heads = [
              'Data e Hora',
              'Cliente',
              'Funcionário',
              'Status',
              ['label' => 'Ações', 'no-export' => true, 'width' => 5],
    ];

    $config = [
      'language' => [ 'url' => '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json' ],
    ];
  @endphp

  <x-adminlte-datatable id="appointmentsTable" :heads="$heads" :config="$config" hoverable striped>
    @foreach($appointments as $appointment)
      <tr>
        <td>{{$appointment->scheduled_at_formatted}}</td>
        <td>{{$appointment->client->name}}</td>
        <td>{{$appointment->user->name}}</td>
        <td>
          <span class="badge badge-{{$appointment->status_color}}">{{$appointment->status_formatted}}</span>
        </td>

        <td class="d-flex">
          <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-primary mr-2" title="Ver">
            <i class="fas fa-xg fa-eye"></i>
          </a>

          @can('update', $appointment)
            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-info mr-2" title="Editar">
              <i class="fas fa-xg fa-pen"></i>
            </a>
          @endcan

          @can('delete', $appointment)
            <x-adminlte-button
              data-toggle="modal"
              data-target="#removeAppointmentModal"
              theme="danger"
              icon="fas fa-xg fa-trash-alt"
              title="Apagar"
            />
          @endcan
        </td>
      </tr>

      <x-adminlte-modal
        id="removeAppointmentModal" title="Apagar Agendamento" theme="danger"
        icon="fas fa-trash" size="md"
      >
        <p>Tem certeza que quer apagar os dados de agendamento de <strong>{{$appointment->client->name}}</strong>?</p>
        <strong class="text-danger">Essa ação é permanente e não poderá ser desfeita!</strong>

        <x-slot name="footerSlot">
          <form action="{{route('appointments.destroy', $appointment)}}" method="POST">
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

@section('js')
  <script>
    $(document).ready(function () {
      $('#filter-date-from').inputmask('99/99/9999');
      $('#filter-date-to').inputmask('99/99/9999');
      // Inicializa os datepickers
      $('#filter-date-from, #filter-date-to').datetimepicker({
        format: 'L',
        locale: 'pt-br',
      });

      $('#btn-apply-filters').on('click', function () {
        const from = $('#filter-date-from').val();
        const to = $('#filter-date-to').val();

        $.ajax({
          url: '{{ route('appointments.index') }}',
          method: 'GET',
          data: {from, to},
          success: function (response) {
            $('#appointmentsTable').DataTable().destroy();
            $('#appointmentsTable tbody').html($(response).find('#appointmentsTable tbody').html());
            $('#appointmentsTable').DataTable({
              language: {url: '//cdn.datatables.net/plug-ins/1.10.19/i18n/Portuguese-Brasil.json'},
            });
          },
        });
      });

      $('#btn-clear-filters').on('click', function () {
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('#btn-apply-filters').trigger('click');
      });
    });
  </script>
@stop
