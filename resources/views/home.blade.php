@extends('layouts.app')

@section('subtitle', 'Início')
@section('content_header')
  <h1>Dashboard</h1>
@stop

@section('content')
  {{-- Cards de status --}}
  @php
    $totalMonthAppointments = array_sum($statusCounts->toArray());
    $pct = fn($key) => $totalMonthAppointments > 0
      ? number_format(($statusCounts[$key] ?? 0) / $totalMonthAppointments * 100, 1)
      : '0,0';
  @endphp
  <div class="row mb-3">
    <div class="col-md-3">
      <div class="rounded p-3 h-100" style="background:#E6F1FB;border:0.5px solid #B5D4F4;">
        <div class="text-uppercase font-weight-bold mb-1" style="font-size:11px;letter-spacing:.05em;color:#185FA5;">
          Agendados
        </div>
        <div class="d-flex align-items-baseline" style="gap:8px;">
          <div class="font-weight-bold" style="font-size:28px;color:#0C447C;">{{ $statusCounts['scheduled'] ?? 0 }}</div>
          <div style="font-size:13px;color:#185FA5;">{{ $pct('scheduled') }}%</div>
        </div>
        <div style="font-size:12px;color:#185FA5;">este mês</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="rounded p-3 h-100" style="background:#EAF3DE;border:0.5px solid #C0DD97;">
        <div class="text-uppercase font-weight-bold mb-1" style="font-size:11px;letter-spacing:.05em;color:#3B6D11;">
          Concluídos
        </div>
        <div class="d-flex align-items-baseline" style="gap:8px;">
          <div class="font-weight-bold" style="font-size:28px;color:#27500A;">{{ $statusCounts['completed'] ?? 0 }}</div>
          <div style="font-size:13px;color:#3B6D11;">{{ $pct('completed') }}%</div>
        </div>
        <div style="font-size:12px;color:#3B6D11;">este mês</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="rounded p-3 h-100" style="background:#FCEBEB;border:0.5px solid #F7C1C1;">
        <div class="text-uppercase font-weight-bold mb-1" style="font-size:11px;letter-spacing:.05em;color:#A32D2D;">
          Cancelados
        </div>
        <div class="d-flex align-items-baseline" style="gap:8px;">
          <div class="font-weight-bold" style="font-size:28px;color:#791F1F;">{{ $statusCounts['cancelled'] ?? 0 }}</div>
          <div style="font-size:13px;color:#A32D2D;">{{ $pct('cancelled') }}%</div>
        </div>
        <div style="font-size:12px;color:#A32D2D;">este mês</div>
      </div>
    </div>
    <div class="col-md-3">
      <div class="rounded p-3 h-100" style="background:#FAEEDA;border:0.5px solid #FAC775;">
        <div class="text-uppercase font-weight-bold mb-1" style="font-size:11px;letter-spacing:.05em;color:#854F0B;">Não
          compareceram
        </div>
        <div class="d-flex align-items-baseline" style="gap:8px;">
          <div class="font-weight-bold" style="font-size:28px;color:#633806;">{{ $statusCounts['no_show'] ?? 0 }}</div>
          <div style="font-size:13px;color:#854F0B;">{{ $pct('no_show') }}%</div>
        </div>
        <div style="font-size:12px;color:#854F0B;">este mês</div>
      </div>
    </div>
  </div>

  <div class="row mb-3">
    {{-- Agendamentos de hoje --}}
    <div class="col-md-4">
      <div class="card border">
        <div class="card-header py-2 px-3 d-flex align-items-center">
          <i class="fas fa-calendar-day fa-sm mr-2 text-muted"></i>
          <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Agendamentos de hoje</small>
        </div>
        <div class="card-body p-0">
          @forelse($todayAppointments as $appointment)
            @php $isNext = $nextAppointment && $appointment->uuid === $nextAppointment->uuid; @endphp
            <a href="{{ route('appointments.show', $appointment) }}"
               class="text-decoration-none text-dark appointment-link {{ $isNext ? 'next-appointment' : '' }}"
               style="display:flex; align-items:center; justify-content:space-between; width:100%; padding:10px 16px; border-bottom:0.5px solid #dee2e6; font-size:13px; {{ $isNext ? 'background:#fffbe6; border-left:3px solid #f0a500 !important;' : '' }}">
              <div>
                <div class="font-weight-bold d-flex align-items-center gap-1">
                  @if($isNext)
                    <span style="font-size:10px; background:#f0a500; color:#fff; border-radius:3px; padding:1px 5px; margin-right:5px; letter-spacing:.04em; font-weight:700; text-transform:uppercase;">Próximo</span>
                  @endif
                  {{ $appointment->client->name }}
                </div>
                <div class="text-muted" style="font-size:12px;">
                  {{ $appointment->scheduled_time }} · {{ $appointment->user->name }}
                </div>
              </div>
              <span class="badge badge-{{ $appointment->status_color }}" style="white-space:nowrap; margin-left:12px;">
                {{ $appointment->status_formatted }}
              </span>
            </a>
          @empty
            <div class="text-center text-muted py-4" style="font-size:13px;">Nenhum agendamento para hoje.</div>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Agendamentos de amanhã --}}
    <div class="col-md-4">
      <div class="card border">
        <div class="card-header py-2 px-3 d-flex align-items-center">
          <i class="fas fa-calendar fa-sm mr-2 text-muted"></i>
          <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Agendamentos de amanhã</small>
        </div>
        <div class="card-body p-0">
          @forelse($tomorrowAppointments as $appointment)
            <a href="{{ route('appointments.show', $appointment) }}"
               class="text-decoration-none text-dark appointment-link"
               style="display:flex; align-items:center; justify-content:space-between; width:100%; padding:10px 16px; border-bottom:0.5px solid #dee2e6; font-size:13px;">
              <div>
                <div class="font-weight-bold">{{ $appointment->client->name }}</div>
                <div class="text-muted" style="font-size:12px;">
                  {{ $appointment->scheduled_time }} · {{ $appointment->user->name }}
                </div>
              </div>
              <span class="badge badge-{{ $appointment->status_color }}" style="white-space:nowrap; margin-left:12px;">
                {{ $appointment->status_formatted }}
              </span>
            </a>
          @empty
            <div class="text-center text-muted py-4" style="font-size:13px;">Nenhum agendamento para amanhã.</div>
          @endforelse
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="row h-100" style="flex-direction:column;gap:12px;margin:0;">
        {{-- Faturamento --}}
        <div class="card border">
          <div class="card-header py-2 px-3 d-flex align-items-center">
            <i class="fas fa-dollar-sign fa-sm mr-2 text-muted"></i>
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Faturamento do
              mês</small>
          </div>
          <div class="card-body py-3">
            @if($isOwner)
              <div class="mb-3">
                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Total
                  geral
                </div>
                <div class="font-weight-bold" style="font-size:26px;color:#3B6D11;">
                  R$ {{ number_format($monthlyRevenue, 2, ',', '.') }}
                </div>
              </div>
              <div>
                <div class="text-muted" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Seus
                  atendimentos
                </div>
                <div class="font-weight-bold" style="font-size:20px;color:#3B6D11;">
                  R$ {{ number_format($myMonthlyRevenue, 2, ',', '.') }}
                </div>
              </div>
            @else
              <div class="font-weight-bold" style="font-size:26px;color:#3B6D11;">
                R$ {{ number_format($monthlyRevenue, 2, ',', '.') }}
              </div>
              <div class="text-muted" style="font-size:12px;margin-top:4px;">
                {{ $statusCounts['completed'] ?? 0 }} atendimentos concluídos
              </div>
            @endif
          </div>
        </div>

        {{-- Resumo geral --}}
        @if($isOwner)
          <div class="card border">
            <div class="card-header py-2 px-3 d-flex align-items-center">
              <i class="fas fa-users fa-sm mr-2 text-muted"></i>
              <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Resumo
                geral</small>
            </div>
            <div class="card-body p-0">
              <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom"
                   style="font-size:13px;">
                <span class="text-muted">Clientes</span>
                <span class="font-weight-bold">{{ $totalClients }}</span>
              </div>
              <div class="d-flex justify-content-between align-items-center px-3 py-2" style="font-size:13px;">
                <span class="text-muted">Funcionários ativos</span>
                <span class="font-weight-bold">{{ $totalEmployees }}</span>
              </div>
            </div>
          </div>
        @endif
      </div>
    </div>
  </div>
@stop

@section('css')
  <style>
    .appointment-link:hover {
      background-color: #f8f9fa;
    }

    .appointment-link:last-child {
      border-bottom: none !important;
    }
  </style>
@stop
