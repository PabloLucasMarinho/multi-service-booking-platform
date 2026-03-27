@extends('layouts.app')

@section('subtitle', 'Agendamentos Mensais')

@section('content_header')
  <h1>Agendamentos Mensais</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Agendamentos', 'url' => route('appointments.index')],
    ['label' => 'Mensal'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card body-class="p-0" theme="primary" icon="fas fa-calendar-alt" title="Calendário de Agendamentos">

    <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
      <button class="btn btn-sm btn-outline-secondary" id="prev-month">&#8592; Anterior</button>
      <span id="cal-title" class="font-weight-bold" style="font-size:15px; text-transform:capitalize;"></span>
      <button class="btn btn-sm btn-outline-secondary" id="next-month">Próximo &#8594;</button>
    </div>

    <div class="row no-gutters border-bottom">
      @foreach(['Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb', 'Dom'] as $dow)
        <div class="col text-center py-2 text-uppercase text-muted font-weight-bold"
             style="font-size:11px;letter-spacing:.05em;">
          {{ $dow }}
        </div>
      @endforeach
    </div>

    <div class="row no-gutters" id="cal-body"></div>

  </x-adminlte-card>
@stop

@section('js')
  <script>
    let appointmentData = @json($appointmentsByDate);
    let current = new Date({{ $year }}, {{ $month - 1 }}, 1);

    const indexUrl = '{{ route('appointments.index') }}';
    const months = ['janeiro', 'fevereiro', 'março', 'abril', 'maio', 'junho', 'julho', 'agosto', 'setembro', 'outubro', 'novembro', 'dezembro'];

    function render() {
      const year = current.getFullYear();
      const month = current.getMonth();

      document.getElementById('cal-title').textContent = months[month] + ' de ' + year;

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const today = new Date();

      let dow = firstDay.getDay();
      dow = dow === 0 ? 6 : dow - 1;

      const body = document.getElementById('cal-body');
      body.innerHTML = '';

      for (let i = 0; i < dow; i++) {
        body.innerHTML += `<div class="border-right border-bottom bg-light" style="width:calc(100%/7);min-height:90px;"></div>`;
      }

      for (let d = 1; d <= lastDay.getDate(); d++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
        const count = appointmentData[dateStr];

        const formattedDate = String(d).padStart(2, '0') + '/' + String(month + 1).padStart(2, '0') + '/' + year;
        const clickable = count ? `style="cursor:pointer;" onclick="window.location.href='${indexUrl}?from=${formattedDate}&to=${formattedDate}'"` : '';
        const hoverClass = count ? 'calendar-cell-hover' : '';

        const dayBadge = isToday
          ? `<div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white mx-auto" style="width:24px;height:24px;font-size:13px;font-weight:500;">${d}</div>`
          : `<div class="text-center font-weight-bold" style="font-size:13px;">${d}</div>`;

        const countBadge = count
          ? `<div class="d-flex align-items-center justify-content-center rounded-circle bg-info text-white mx-auto mt-auto mb-2" style="width:36px;height:36px;font-size:14px;font-weight:500;">${count}</div>`
          : '';

        body.innerHTML += `
          <div class="${hoverClass} border-right border-bottom d-flex flex-column align-items-center pt-2" style="width:calc(100%/7);min-height:90px;" ${clickable}>
            ${dayBadge}
            ${countBadge}
          </div>`;
      }
    }

    function fetchAndRender() {
      const year = current.getFullYear();
      const month = current.getMonth() + 1;

      $.ajax({
        url: '{{ route('appointments.monthly') }}',
        method: 'GET',
        data: {month, year},
        success: function (data) {
          appointmentData = data;
          render();
        }
      });
    }

    document.getElementById('prev-month').addEventListener('click', function () {
      current.setMonth(current.getMonth() - 1);
      fetchAndRender();
    });

    document.getElementById('next-month').addEventListener('click', function () {
      current.setMonth(current.getMonth() + 1);
      fetchAndRender();
    });

    render();
  </script>

  <style>
    .calendar-cell-hover:hover {
      background-color: #f8f9fa;
      cursor: pointer;
    }
  </style>
@stop
