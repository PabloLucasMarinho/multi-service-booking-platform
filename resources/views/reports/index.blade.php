@extends('layouts.app')

@section('subtitle', 'Relatórios')
@section('plugins.InputMask', true)
@section('plugins.Tempus', true)

@section('content_header')
  <h1>Relatórios</h1>
  <x-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('home')],
        ['label' => 'Relatórios'],
    ]"/>
@stop

@section('content')
  <x-adminlte-card>
    <ul class="nav nav-tabs" id="reportTabs">
      <li class="nav-item">
        <a class="nav-link active" data-toggle="tab" href="#financeiro">
          <i class="fas fa-dollar-sign mr-1"></i> Financeiro
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#operacional">
          <i class="fas fa-chart-bar mr-1"></i> Operacional
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#clientes">
          <i class="fas fa-users mr-1"></i> Clientes
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-toggle="tab" href="#promocoes">
          <i class="fas fa-percentage mr-1"></i> Promoções
        </a>
      </li>
    </ul>

    <div class="tab-content pt-3">
      {{-- Filtro compartilhado --}}
      @php
        $dateConfig = [
          'format' => 'L',
          'locale' => 'pt-br',
          'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
          'dayViewHeaderFormat' => 'MMM YYYY',
        ];
      @endphp
      <form method="GET" action="{{ route('reports.index') }}" class="mb-4">
        <div class="d-flex align-items-end" style="gap:12px;">
          <x-adminlte-input-date
            id="from" name="from" :config="$dateConfig"
            label="De" placeholder="DD/MM/AAAA"
            value="{{ $from->format('d/m/Y') }}"
            fgroup-class="mb-0" autocomplete="off"
          >
            <x-slot name="prependSlot">
              <div class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </div>
            </x-slot>
          </x-adminlte-input-date>

          <x-adminlte-input-date
            id="to" name="to" :config="$dateConfig"
            label="Até" placeholder="DD/MM/AAAA"
            value="{{ $to->format('d/m/Y') }}"
            fgroup-class="mb-0" autocomplete="off"
          >
            <x-slot name="prependSlot">
              <div class="input-group-text">
                <i class="fas fa-calendar-alt"></i>
              </div>
            </x-slot>
          </x-adminlte-input-date>

          <div class="form-group mb-0">
            <button type="submit" class="btn btn-primary btn-sm">
              <i class="fas fa-search mr-1"></i> Filtrar
            </button>
          </div>
        </div>
      </form>

      {{-- Financeiro --}}
      <div class="tab-pane fade show active" id="financeiro">
        <div class="row mb-3">
          <div class="col-md-3">
            <div class="p-3 rounded" style="background:#EAF3DE;border:0.5px solid #C0DD97;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#3B6D11;">Faturamento total
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#27500A;">
                R$ {{ number_format($financial['totalRevenue'], 2, ',', '.') }}</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 rounded" style="background:#E6F1FB;border:0.5px solid #B5D4F4;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#185FA5;">Ticket médio
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#0C447C;">
                R$ {{ number_format($financial['avgTicket'], 2, ',', '.') }}</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 rounded"
                 style="background:#f8f9fa;border:0.5px solid #dee2e6;">
              <div class="text-uppercase font-weight-bold mb-1 text-muted" style="font-size:11px;letter-spacing:.05em;">
                Atendimentos concluídos
              </div>
              <div class="font-weight-bold" style="font-size:24px;">{{ $financial['totalAppointments'] }}</div>
            </div>
          </div>
          <div class="col-md-3">
            <div class="p-3 rounded" style="background:#FEF9EC;border:0.5px solid #F5D87A;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#7A5700;">Gorjetas no período
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#5A3F00;">
                R$ {{ number_format($financial['totalTips'], 2, ',', '.') }}</div>
            </div>
          </div>
        </div>

        <div class="card border">
          <div class="card-header py-2 px-3">
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Faturamento por
              funcionário</small>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="bg-light">
              <tr>
                <th>Funcionário</th>
                <th class="text-right">Atendimentos</th>
                <th class="text-right">Faturamento</th>
                <th class="text-right">Ticket médio</th>
              </tr>
              </thead>
              <tbody>
              @forelse($financial['byEmployee'] as $employee)
                <tr>
                  <td>{{ $employee['name'] }}</td>
                  <td class="text-right">{{ $employee['count'] }}</td>
                  <td class="text-right">R$ {{ number_format($employee['revenue'], 2, ',', '.') }}</td>
                  <td class="text-right">R$ {{ number_format($employee['avg_ticket'], 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">Nenhum dado no período.</td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Operacional --}}
      <div class="tab-pane fade" id="operacional">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="p-3 rounded"
                 style="background:#f8f9fa;border:0.5px solid #dee2e6;">
              <div class="text-uppercase font-weight-bold mb-1 text-muted" style="font-size:11px;letter-spacing:.05em;">
                Total agendamentos
              </div>
              <div class="font-weight-bold" style="font-size:24px;">{{ $operational['total'] }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#FCEBEB;border:0.5px solid #F7C1C1;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#A32D2D;">Taxa de cancelamento
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#791F1F;">{{ $operational['cancellationRate'] }}
                %
              </div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#FAEEDA;border:0.5px solid #FAC775;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#854F0B;">Não compareceram
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#633806;">{{ $operational['noShowRate'] }}%
              </div>
            </div>
          </div>
        </div>

        <div class="card border">
          <div class="card-header py-2 px-3">
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Serviços mais
              agendados</small>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="bg-light">
              <tr>
                <th>Serviço</th>
                <th class="text-right" style="width:100px;">Agendamentos</th>
                <th style="width:40%;"></th>
              </tr>
              </thead>
              <tbody>
              @php $maxService = $operational['topServices']->max('total') ?: 1; @endphp
              @forelse($operational['topServices'] as $item)
                <tr>
                  <td>{{ $item->service->name }}</td>
                  <td class="text-right">{{ $item->total }}</td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="flex-grow-1" style="background:#f0f0f0;border-radius:4px;height:6px;">
                        <div
                          style="width:{{ ($item->total / $maxService) * 100 }}%;height:6px;border-radius:4px;background:#378ADD;"></div>
                      </div>
                    </div>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">Nenhum dado no período.</td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Clientes --}}
      <div class="tab-pane fade" id="clientes">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="p-3 rounded"
                 style="background:#f8f9fa;border:0.5px solid #dee2e6;">
              <div class="text-uppercase font-weight-bold mb-1 text-muted" style="font-size:11px;letter-spacing:.05em;">
                Total clientes
              </div>
              <div class="font-weight-bold" style="font-size:24px;">{{ $clients['totalClients'] }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#EAF3DE;border:0.5px solid #C0DD97;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#3B6D11;">Novos no período
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#27500A;">{{ $clients['newClients'] }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#FCEBEB;border:0.5px solid #F7C1C1;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#A32D2D;">Sem retorno +30 dias
              </div>
              <div class="font-weight-bold"
                   style="font-size:24px;color:#791F1F;">{{ $clients['inactiveClients'] }}</div>
            </div>
          </div>
        </div>

        @if($clients['topTipClient'])
          @php $ttc = $clients['topTipClient']; @endphp
          <div class="card border mb-3" style="border-color:#F5D87A !important;">
            <div class="card-body py-2 px-3 d-flex align-items-center" style="gap:16px;background:#FEF9EC;border-radius:inherit;">
              <i class="fas fa-hand-holding-usd fa-2x" style="color:#7A5700;opacity:.7;flex-shrink:0;"></i>
              <div>
                <div class="text-uppercase font-weight-bold mb-1" style="font-size:11px;letter-spacing:.05em;color:#7A5700;">
                  Cliente que mais dá gorjeta
                </div>
                <div class="font-weight-bold" style="font-size:16px;color:#5A3F00;">{{ $ttc->name }}</div>
                <div style="font-size:12px;color:#7A5700;">
                  R$ {{ number_format($ttc->total_tips, 2, ',', '.') }} em gorjetas
                  &middot; {{ $ttc->tip_count }} {{ $ttc->tip_count === 1 ? 'vez' : 'vezes' }}
                </div>
              </div>
            </div>
          </div>
        @endif

        <div class="card border">
          <div class="card-header py-2 px-3">
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Clientes mais
              frequentes</small>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="bg-light">
              <tr>
                <th>Cliente</th>
                <th class="text-right">Visitas</th>
                <th class="text-right">Último atendimento</th>
                <th class="text-right">Gasto total</th>
              </tr>
              </thead>
              <tbody>
              @forelse($clients['topClients'] as $client)
                <tr>
                  <td>{{ $client['name'] }}</td>
                  <td class="text-right">{{ $client['visits'] }}</td>
                  <td class="text-right">{{ $client['last_visit'] }}</td>
                  <td class="text-right">R$ {{ number_format($client['total_spent'], 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-3">Nenhum dado no período.</td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- Promoções --}}
      <div class="tab-pane fade" id="promocoes">
        <div class="row mb-3">
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#FCEBEB;border:0.5px solid #F7C1C1;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#A32D2D;">Total descontos
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#791F1F;">
                R$ {{ number_format($promotions['totalDiscount'], 2, ',', '.') }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded"
                 style="background:#f8f9fa;border:0.5px solid #dee2e6;">
              <div class="text-uppercase font-weight-bold mb-1 text-muted" style="font-size:11px;letter-spacing:.05em;">
                Atendimentos c/ promoção
              </div>
              <div class="font-weight-bold" style="font-size:24px;">{{ $promotions['totalWithPromotion'] }}</div>
            </div>
          </div>
          <div class="col-md-4">
            <div class="p-3 rounded" style="background:#E6F1FB;border:0.5px solid #B5D4F4;">
              <div class="text-uppercase font-weight-bold mb-1"
                   style="font-size:11px;letter-spacing:.05em;color:#185FA5;">% com desconto
              </div>
              <div class="font-weight-bold" style="font-size:24px;color:#0C447C;">{{ $promotions['promotionRate'] }}%
              </div>
            </div>
          </div>
        </div>

        <div class="card border">
          <div class="card-header py-2 px-3">
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Desempenho por
              promoção</small>
          </div>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead class="bg-light">
              <tr>
                <th>Promoção</th>
                <th class="text-right">Usos</th>
                <th class="text-right">Total descontos</th>
              </tr>
              </thead>
              <tbody>
              @forelse($promotions['byPromotion'] as $item)
                <tr>
                  <td>{{ $item->promotion->name }}</td>
                  <td class="text-right">{{ $item->uses }}</td>
                  <td class="text-right">R$ {{ number_format($item->total_discount, 2, ',', '.') }}</td>
                </tr>
              @empty
                <tr>
                  <td colspan="3" class="text-center text-muted py-3">Nenhum dado no período.</td>
                </tr>
              @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </x-adminlte-card>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      const hash = window.location.hash;
      if (hash) {
        $(`a[href="${hash}"]`).tab('show');
      }

      $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        history.replaceState(null, null, e.target.getAttribute('href'));
      });
    });
  </script>
@stop
