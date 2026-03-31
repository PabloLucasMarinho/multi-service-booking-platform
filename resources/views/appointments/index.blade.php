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
      <label class="text-uppercase text-muted font-weight-bold d-block" style="font-size:11px;letter-spacing:.05em;">Status</label>
      <div class="dropdown">
        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="status-dropdown"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          <span id="status-count">Todos</span>
        </button>
        <div class="dropdown-menu p-2" aria-labelledby="status-dropdown" style="min-width:200px;">
          @foreach(\App\Enums\AppointmentStatus::cases() as $status)
            <label class="dropdown-item d-flex align-items-center" style="cursor:pointer;">
              <input type="checkbox" class="status-checkbox mr-2" value="{{ $status->value }}" checked>
              <span class="badge badge-{{ match($status) {
                \App\Enums\AppointmentStatus::Scheduled => 'primary',
                \App\Enums\AppointmentStatus::Completed => 'success',
                \App\Enums\AppointmentStatus::Cancelled => 'danger',
                \App\Enums\AppointmentStatus::NoShow => 'warning',
            } }} mr-2">&nbsp;</span>
              {{ match($status) {
                  \App\Enums\AppointmentStatus::Scheduled => 'Agendado',
                  \App\Enums\AppointmentStatus::Completed => 'Concluído',
                  \App\Enums\AppointmentStatus::Cancelled => 'Cancelado',
                  \App\Enums\AppointmentStatus::NoShow => 'Não Compareceu',
              } }}
            </label>
          @endforeach
          <div class="dropdown-divider"></div>
          <a class="dropdown-item text-muted" style="font-size:12px;cursor:pointer;" id="select-all-status">Selecionar
            todos</a>
          <a class="dropdown-item text-muted" style="font-size:12px;cursor:pointer;" id="clear-status">Limpar
            seleção</a>
        </div>
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
        <td data-order="{{ $appointment->scheduled_at->format('Y-m-d H:i:s') }}">
          {{ $appointment->scheduled_at_formatted }}
        </td>
        <td>
          {{ $appointment->client->name }}
          @if($appointment->client->trashed())
            <span class="badge badge-secondary">Inativo</span>
          @endif
        </td>
        <td>
          {{ $appointment->user->name }}
          @if($appointment->user->trashed())
            <span class="badge badge-secondary">Inativo</span>
          @endif
        </td>
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

      $('#filter-date-from, #filter-date-to').datetimepicker({
        format: 'L',
        locale: 'pt-br',
      });

      // Atualiza o contador de status selecionados
      function updateStatusCount() {
        const checked = $('.status-checkbox:checked').length;
        const total = $('.status-checkbox').length;
        $('#status-count').text(checked === total ? 'Todos' : checked + ' selecionados');
      }

      $(document).on('change', '.status-checkbox', updateStatusCount);

      $('#clear-status').on('click', function (e) {
        e.stopPropagation();
        $('.status-checkbox').prop('checked', false);
        updateStatusCount();
      });

      // Impede o dropdown de fechar ao clicar nos checkboxes
      $('.dropdown-menu').on('click', function (e) {
        e.stopPropagation();
      });

      $('#btn-apply-filters').on('click', function () {
        const from = $('#filter-date-from').val();
        const to = $('#filter-date-to').val();
        const statuses = $('.status-checkbox:checked').map(function () {
          return $(this).val();
        }).get();

        const params = new URLSearchParams();
        if (from) params.set('from', from);
        if (to) params.set('to', to);
        statuses.forEach(s => params.append('statuses[]', s));

        window.location.href = '{{ route('appointments.index') }}?' + params.toString();
      });

      $('#btn-clear-filters').on('click', function () {
        $('#filter-date-from').val('');
        $('#filter-date-to').val('');
        $('.status-checkbox').prop('checked', true);
        updateStatusCount();
        $('#btn-apply-filters').trigger('click');
      });

      $('#select-all-status').on('click', function (e) {
        e.stopPropagation();
        $('.status-checkbox').prop('checked', true);
        updateStatusCount();
      });

      const params = new URLSearchParams(window.location.search);
      if (params.get('from')) $('#filter-date-from').val(params.get('from'));
      if (params.get('to')) $('#filter-date-to').val(params.get('to'));

      const selectedStatuses = params.getAll('statuses[]');
      if (selectedStatuses.length) {
        $('.status-checkbox').prop('checked', false);
        selectedStatuses.forEach(s => {
          $(`.status-checkbox[value="${s}"]`).prop('checked', true);
        });
        updateStatusCount();
      }
    });
  </script>
@stop
