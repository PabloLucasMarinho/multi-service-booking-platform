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
      @foreach(['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'] as $dow)
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

    const statusConfig = {
      'scheduled': {label: 'agendado', labelPlural: 'agendados', class: 'bar-scheduled'},
      'completed': {label: 'concluído', labelPlural: 'concluídos', class: 'bar-completed'},
      'cancelled': {label: 'cancelado', labelPlural: 'cancelados', class: 'bar-cancelled'},
      'no_show': {label: 'não compareceu', labelPlural: 'não compareceram', class: 'bar-noshow'},
    };

    function render() {
      const year = current.getFullYear();
      const month = current.getMonth();

      document.getElementById('cal-title').textContent = months[month] + ' de ' + year;

      const firstDay = new Date(year, month, 1);
      const lastDay = new Date(year, month + 1, 0);
      const today = new Date();

      let dow = firstDay.getDay(); // 0 = domingo

      const body = document.getElementById('cal-body');
      body.innerHTML = '';

      for (let i = 0; i < dow; i++) {
        const isWeekend = i === 0 || i === 6;
        body.innerHTML += `<div class="border-right border-bottom ${isWeekend ? 'bg-light' : ''}" style="width:calc(100%/7);min-height:90px;"></div>`;
      }

      for (let d = 1; d <= lastDay.getDate(); d++) {
        const dateStr = year + '-' + String(month + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
        const dayOfWeek = new Date(year, month, d).getDay();
        const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
        const isToday = today.getFullYear() === year && today.getMonth() === month && today.getDate() === d;
        const dayData = appointmentData[dateStr];
        const hasAppointments = dayData && Object.keys(dayData).length > 0;

        const formattedDate = String(d).padStart(2, '0') + '/' + String(month + 1).padStart(2, '0') + '/' + year;
        const clickable = hasAppointments ? `onclick="window.location.href='${indexUrl}?from=${formattedDate}&to=${formattedDate}'"` : '';

        const dayBadge = isToday
          ? `<div class="d-flex align-items-center justify-content-center rounded-circle bg-primary text-white" style="width:24px;height:24px;font-size:13px;font-weight:500;">${d}</div>`
          : `<div style="font-size:13px;font-weight:500;">${d}</div>`;

        let bars = '';
        if (hasAppointments) {
          bars = '<div style="display:flex;flex-direction:column;gap:2px;margin-top:auto;">';
          for (const [status, count] of Object.entries(dayData)) {
            const cfg = statusConfig[status];
            if (!cfg) continue;
            const label = count === 1 ? cfg.label : cfg.labelPlural;
            bars += `<div class="${cfg.class}" style="display:flex;align-items:center;border-radius:3px;padding:1px 5px;font-size:10px;font-weight:500;gap:4px;">
              <span>●</span> ${count} ${label}
            </div>`;
          }
          bars += '</div>';
        }

        body.innerHTML += `
          <div class="${isWeekend ? 'bg-light' : ''} ${isToday ? 'cal-today' : ''} ${hasAppointments ? 'cal-clickable' : ''} border-right border-bottom d-flex flex-column p-2"
            style="width:calc(100%/7);min-height:90px;${isToday ? 'background:#E6F1FB!important;' : ''}" ${clickable}>
            ${dayBadge}
            ${bars}
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
    .cal-clickable {
      cursor: pointer;
    }

    .cal-clickable:hover {
      filter: brightness(0.97);
    }

    .cal-today {
      background: #E6F1FB !important;
    }

    .bar-scheduled {
      background: #E6F1FB;
      color: #185FA5;
    }

    .bar-completed {
      background: #EAF3DE;
      color: #3B6D11;
    }

    .bar-cancelled {
      background: #FCEBEB;
      color: #A32D2D;
    }

    .bar-noshow {
      background: #FAEEDA;
      color: #854F0B;
    }
  </style>
@stop
