@extends('layouts.app')

@section('plugins.InputMask', true)

@section('subtitle', 'Agendamento de ' . $appointment->client->name)

@section('content_header')
  <h1>Agendamento de {{$appointment->client->name}}</h1>

  <x-breadcrumb :items="array_values(array_filter([
      ['label' => 'Dashboard', 'url' => route('home')],
      ['label' => 'Agendamentos', 'url' => route('appointments.index')],
      ['label' => 'Agendamento de ' . $appointment->client->name],
    ]))"
  />
@stop

@section('content')
  <x-adminlte-card
    body-class="p-0 pt-4" title="Agendamento de {{$appointment->client->name}}"
    theme="primary" icon="fas fa-clock"
  >
    {{-- Dados de Agendamento --}}
    <div class="card border mb-3 mx-4">
      <div class="card-header py-2 px-3 d-flex align-items-center">
        <i class="fas fa-info-circle fa-sm mr-2 text-muted"></i>
        <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Dados de
          Agendamento</small>
      </div>
      <div class="card-body p-0">
        <div class="row no-gutters">
          <div class="col-md-4 p-3 border-right">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Cliente</p>
            <strong>{{ $appointment->client->name }}</strong>
          </div>

          <div class="col-md-4 p-3 border-right">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">Data e
              Hora</p>
            <strong>{{ $appointment->scheduled_at_formatted }}</strong>
          </div>

          <div class="col-md-4 p-3">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">
              Status</p>
            <strong class="text-{{$appointment->status_color}}">{{ $appointment->status_formatted }}</strong>
          </div>

          <div class="col-md-12 p-3 border-top">
            <p class="text-muted mb-1" style="font-size:11px;text-transform:uppercase;letter-spacing:.05em;">
              Observação</p>
            <strong>{{ $appointment->notes }}</strong>
          </div>
        </div>
      </div>
    </div>

    {{-- Serviços --}}
    <div class="card border mb-4 mx-4">
      <div class="card-header py-2 px-3">
        <div class="d-flex align-items-center">
          <i class="fas fa-clipboard-list fa-sm mr-2 text-muted"></i>
          <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Serviços</small>
        </div>
      </div>

      {{-- Formulário de adição --}}
      @if($appointment->isEditable())
        <form action="{{ route('appointment-services.store', $appointment) }}" method="POST">
          @csrf
          <div class="p-3 border-bottom bg-light">
            <div class="row align-items-end">
              <div class="col-md-5">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Serviço</label>
                <select name="service_uuid" class="form-control form-control-sm">
                  <option value="">Selecione um serviço...</option>
                  @foreach($services as $service)
                    <option value="{{ $service->uuid }}">{{ $service->name }} —
                      R$ {{ $service->price_formatted }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Desconto</label>
                <select name="manual_discount_type" class="form-control form-control-sm">
                  <option value="">Sem desconto</option>
                  <option value="percentage">Porcentagem (%)</option>
                  <option value="fixed">Valor fixo (R$)</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Valor</label>
                <input type="text" name="manual_discount_value" id="discount-value" class="form-control form-control-sm"
                       placeholder="0,00"/>
              </div>
              <div class="col-md-2">
                <button type="submit" class="btn btn-success btn-sm btn-block">
                  <i class="fas fa-plus mr-1"></i> Adicionar
                </button>
              </div>
            </div>
          </div>
        </form>
      @endif

      {{-- Tabela de serviços --}}
      <div class="table-responsive">
        <table class="table table-sm mb-0" style="table-layout:fixed;">
          <thead class="bg-light">
          <tr>
            <th style="width:30%;font-size:11px;" class="text-uppercase text-muted font-weight-bold pl-3"
                style="letter-spacing:.05em;">Serviço
            </th>
            <th style="width:15%;font-size:11px;" class="text-uppercase text-muted font-weight-bold text-right"
                style="letter-spacing:.05em;">Preço orig.
            </th>
            <th style="width:20%;font-size:11px;" class="text-uppercase text-muted font-weight-bold text-right"
                style="letter-spacing:.05em;">Promoção
            </th>
            <th style="width:20%;font-size:11px;" class="text-uppercase text-muted font-weight-bold text-right"
                style="letter-spacing:.05em;">Desc. manual
            </th>
            <th style="width:10%;font-size:11px;" class="text-uppercase text-muted font-weight-bold text-right"
                style="letter-spacing:.05em;">Final
            </th>
            <th style="width:5%;"></th>
          </tr>
          </thead>
          <tbody>
          @forelse($appointment->appointmentServices as $appointmentService)
            <tr>
              <td class="pl-3 align-middle font-weight-bold">{{ $appointmentService->service->name }}</td>
              <td class="text-right align-middle text-muted">
                R$ {{ number_format($appointmentService->original_price, 2, ',', '.') }}</td>
              <td class="text-right align-middle">
                @if($appointmentService->promotion_amount_snapshot)
                  <span class="badge" style="background:#e3f2fd;color:#1565c0;font-size:11px;border-radius:20px;">
                  {{ $appointmentService->promotion->name }} — R$ {{ number_format($appointmentService->promotion_amount_snapshot, 2, ',', '.') }}
                </span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-right align-middle">
                @if($appointmentService->manual_discount_amount)
                  <span class="badge" style="background:#fff3e0;color:#e65100;font-size:11px;border-radius:20px;">
                  @if($appointmentService->manual_discount_type?->value === 'percentage')
                      {{ number_format($appointmentService->manual_discount_value, 2, ',', '.') }}% —
                      R$ {{ number_format($appointmentService->manual_discount_amount, 2, ',', '.') }}
                    @else
                      R$ {{ number_format($appointmentService->manual_discount_amount, 2, ',', '.') }} fixo
                    @endif
                </span>
                @else
                  <span class="text-muted">—</span>
                @endif
              </td>
              <td class="text-right align-middle font-weight-bold">
                R$ {{ number_format($appointmentService->final_price, 2, ',', '.') }}</td>
              <td class="text-center align-middle">
                @if($appointment->isEditable())
                  <form action="{{ route('appointment-services.destroy', $appointmentService) }}" method="POST">
                    @method('DELETE')
                    @csrf
                    <button type="submit" class="btn btn-link text-danger p-0">
                      <i class="fas fa-trash" style="font-size:13px;"></i>
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6" class="text-center text-muted py-3" style="font-size:13px;">
                Nenhum serviço adicionado ainda.
              </td>
            </tr>
          @endforelse
          </tbody>
          @if($appointment->appointmentServices->isNotEmpty())
            <tfoot class="bg-light">
            <tr>
              <td colspan="4" class="text-right font-weight-bold text-muted" style="font-size:13px;">Total</td>
              <td class="text-right font-weight-bold" style="font-size:15px;">
                R$ {{ $appointment->formatted_total }}</td>
              <td></td>
            </tr>
            </tfoot>
          @endif
        </table>
      </div>

      {{-- Ações do agendamento --}}
      @if($appointment->appointmentServices->isNotEmpty())
        <div class="card-footer d-flex justify-content-end" style="gap:8px;">
          @if($appointment->status === \App\Enums\AppointmentStatus::Completed)
            <a href="{{ route('appointments.receipt', $appointment) }}" class="btn btn-primary btn-sm">
              <i class="fas fa-file-invoice mr-1"></i> Gerar Recibo
            </a>
          @elseif($appointment->isEditable())
            <form action="{{ route('appointments.destroy', $appointment) }}" method="POST">
              @method('DELETE')
              @csrf
              <button type="submit" class="btn btn-danger btn-sm">
                <i class="fas fa-times mr-1"></i> Cancelar agendamento
              </button>
            </form>

            @if(!$appointment->scheduled_at->isFuture())
              <form action="{{ route('appointments.complete', $appointment) }}" method="POST">
                @method('PATCH')
                @csrf
                <button type="submit" class="btn btn-success btn-sm">
                  <i class="fas fa-check mr-1"></i> Concluir agendamento
                </button>
              </form>
            @endif
          @elseif($appointment->canRestore())
            <form action="{{ route('appointments.restore', $appointment) }}" method="POST">
              @method('PATCH')
              @csrf
              <button type="submit" class="btn btn-warning btn-sm">
                <i class="fas fa-undo mr-1"></i> Desfazer cancelamento
              </button>
            </form>
          @endif
        </div>
      @endif
    </div>
  </x-adminlte-card>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      $('#discount-value').inputmask('currency', {
        prefix: '',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        rightAlign: false,
      });
    });
  </script>
@stop
