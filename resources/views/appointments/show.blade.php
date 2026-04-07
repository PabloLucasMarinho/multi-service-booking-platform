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
              @php $canDiscount = auth()->user()->can_apply_manual_discount || auth()->user()->role->name === 'owner'; @endphp
              <div class="col-md-3">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Desconto</label>
                <select name="manual_discount_type" class="form-control form-control-sm" {{ $canDiscount ? '' : 'disabled' }}>
                  <option value="">Sem desconto</option>
                  <option value="percentage">Porcentagem (%)</option>
                  <option value="fixed">Valor fixo (R$)</option>
                </select>
              </div>
              <div class="col-md-2">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Valor</label>
                <input type="text" name="manual_discount_value" id="discount-value" class="form-control form-control-sm"
                       placeholder="0,00" {{ $canDiscount ? '' : 'disabled' }}/>
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
                  @php $capped = $appointmentService->isDiscountCapped($company?->max_discount_percentage); @endphp
                  <span class="badge" style="background:{{ $capped ? '#fce4ec' : '#fff3e0' }};color:{{ $capped ? '#b71c1c' : '#e65100' }};font-size:11px;border-radius:20px;user-select:none;">
                    @if($appointmentService->manual_discount_type?->value === 'percentage')
                      @if($capped)<s>{{ number_format($appointmentService->manual_discount_value, 2, ',', '.') }}%</s>@else{{ number_format($appointmentService->manual_discount_value, 2, ',', '.') }}%@endif —
                      R$ {{ number_format($appointmentService->manual_discount_amount, 2, ',', '.') }}
                    @else
                      R$ {{ number_format($appointmentService->manual_discount_amount, 2, ',', '.') }} fixo
                    @endif
                  </span>
                  @if($capped)
                    <span class="css-tooltip" style="display:inline-block;position:relative;vertical-align:middle;" data-tip="Teto de {{ $company->max_discount_percentage }}% aplicado — desconto limitado ao máximo permitido">
                      <i class="fas fa-lock" style="color:#b71c1c;font-size:10px;cursor:default;pointer-events:none;"></i>
                    </span>
                  @endif
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

    {{-- Caixa --}}
    @php
      $caixaAberta  = $appointment->isEditable() && !$appointment->scheduled_at->isFuture() && $appointment->appointmentServices->isNotEmpty();
      $caixaFechada = $appointment->status === \App\Enums\AppointmentStatus::Completed && $appointment->payments->isNotEmpty();
    @endphp

    @if($caixaAberta || $caixaFechada)
      @php
        $totalPaid  = $appointment->total_paid;
        $total      = $appointment->total;
        $balance    = round($totalPaid - $total, 2);
        $isEmployee = auth()->user()->role->name === \App\Enums\RoleNames::Employee->value;
      @endphp

      <div class="card border mb-4 mx-4">
        <div class="card-header py-2 px-3 d-flex align-items-center justify-content-between">
          <div class="d-flex align-items-center">
            <i class="fas fa-cash-register fa-sm mr-2 text-muted"></i>
            <small class="text-uppercase text-muted font-weight-bold" style="letter-spacing:.05em;">Caixa</small>
          </div>
          @if($caixaFechada)
            <span class="badge badge-success" style="font-size:11px;">
              <i class="fas fa-lock mr-1"></i> Fechado
            </span>
          @endif
        </div>

        {{-- Formulário de admissão de pagamento --}}
        <form action="{{ $caixaAberta ? route('appointment-payments.store', $appointment) : '#' }}" method="POST">
          @csrf
          <div class="p-3 border-bottom bg-light">
            <div class="row align-items-end">
              <div class="col-md-4">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Valor recebido</label>
                <input type="text" name="amount" id="payment-amount" class="form-control form-control-sm"
                       placeholder="0,00" {{ $caixaFechada ? 'disabled' : 'required' }} autocomplete="off" />
              </div>
              <div class="col-md-5">
                <label class="text-uppercase text-muted font-weight-bold" style="font-size:11px;letter-spacing:.05em;">Forma de pagamento</label>
                <select name="payment_method" class="form-control form-control-sm" {{ $caixaFechada ? 'disabled' : 'required' }}>
                  <option value="">Selecione...</option>
                  @foreach(\App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}">{{ $method->label() }}</option>
                  @endforeach
                </select>
              </div>
              <div class="col-md-3">
                <button type="submit" class="btn btn-primary btn-sm btn-block" {{ $caixaFechada ? 'disabled' : '' }}>
                  <i class="fas fa-plus mr-1"></i> Admitir
                </button>
              </div>
            </div>
          </div>
        </form>

        {{-- Lista de pagamentos --}}
        @if($appointment->payments->isNotEmpty())
          <div class="table-responsive">
            <table class="table table-sm mb-0" style="table-layout:fixed;">
              <thead class="bg-light">
                <tr>
                  <th style="width:50%;font-size:11px;" class="text-uppercase text-muted font-weight-bold pl-3">Forma</th>
                  <th style="width:40%;font-size:11px;" class="text-uppercase text-muted font-weight-bold text-right">Valor</th>
                  <th style="width:10%;"></th>
                </tr>
              </thead>
              <tbody>
                @foreach($appointment->payments as $payment)
                  <tr>
                    <td class="pl-3 align-middle">{{ $payment->payment_method->label() }}</td>
                    <td class="text-right align-middle font-weight-bold">R$ {{ number_format($payment->amount, 2, ',', '.') }}</td>
                    <td class="text-center align-middle">
                      @if($caixaAberta)
                        <form action="{{ route('appointment-payments.destroy', $payment) }}" method="POST">
                          @method('DELETE')
                          @csrf
                          <button type="submit" class="btn btn-link text-danger p-0">
                            <i class="fas fa-trash" style="font-size:13px;"></i>
                          </button>
                        </form>
                      @endif
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif

        {{-- Resumo financeiro --}}
        <div class="px-3 py-2 border-top d-flex justify-content-end align-items-center flex-wrap" style="gap:16px;background:#f8f9fa;">
          <span style="font-size:13px;">
            <span class="text-muted">Total dos serviços:</span>
            <strong>R$ {{ $appointment->formatted_total }}</strong>
          </span>
          <span style="font-size:13px;">
            <span class="text-muted">Total pago:</span>
            <strong>R$ {{ number_format($totalPaid, 2, ',', '.') }}</strong>
          </span>

          @if($caixaFechada)
            {{-- Estado final registrado no banco --}}
            @if($appointment->tip > 0)
              <span class="badge badge-success" style="font-size:12px;">
                <i class="fas fa-hand-holding-usd mr-1"></i> Gorjeta: R$ {{ number_format($appointment->tip, 2, ',', '.') }}
              </span>
            @elseif($appointment->closing_discount > 0)
              <span class="badge badge-warning" style="font-size:12px;">
                <i class="fas fa-tag mr-1"></i> Desconto: R$ {{ number_format($appointment->closing_discount, 2, ',', '.') }}
              </span>
            @else
              <span class="badge badge-secondary" style="font-size:12px;">Valor exato</span>
            @endif
          @else
            {{-- Estado em aberto —calculado em tempo real --}}
            @if($balance > 0)
              <span class="badge badge-success" style="font-size:12px;">
                <i class="fas fa-arrow-up mr-1"></i> Gorjeta: R$ {{ number_format($balance, 2, ',', '.') }}
              </span>
            @elseif($balance < 0)
              <span class="badge badge-warning" style="font-size:12px;">
                <i class="fas fa-arrow-down mr-1"></i> Faltam: R$ {{ number_format(abs($balance), 2, ',', '.') }}
              </span>
            @else
              <span class="badge badge-secondary" style="font-size:12px;">Valor exato</span>
            @endif

            <button type="button" class="btn btn-success btn-sm" id="btn-concluir">
              <i class="fas fa-check mr-1"></i> Concluir agendamento
            </button>
          @endif
        </div>
      </div>

      @if($caixaAberta)
        {{-- Formulário oculto de conclusão --}}
        <form id="form-complete" action="{{ route('appointments.complete', $appointment) }}" method="POST" style="display:none;">
          @method('PATCH')
          @csrf
          <input type="hidden" name="admin_email" id="complete-admin-email" value="">
          <input type="hidden" name="admin_password" id="complete-admin-password" value="">
        </form>

        {{-- Modal: Gorjeta --}}
        <div class="modal fade" id="modal-tip" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd mr-2 text-success"></i> Gorjeta detectada</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <p>O cliente pagou <strong>R$ <span id="tip-amount"></span></strong> a mais do que o valor dos serviços.</p>
                <p class="mb-0 text-muted" style="font-size:13px;">Esse valor será registrado como gorjeta para uso em relatórios. Deseja confirmar?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-confirm-tip">
                  <i class="fas fa-check mr-1"></i> Confirmar e concluir
                </button>
              </div>
            </div>
          </div>
        </div>

        {{-- Modal: Desconto (admin/owner) --}}
        <div class="modal fade" id="modal-discount" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-tag mr-2 text-warning"></i> Desconto no fechamento</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <p>O valor pago é <strong>R$ <span id="discount-amount"></span></strong> menor do que o total dos serviços.</p>
                <p class="mb-0 text-muted" style="font-size:13px;">Esse valor será registrado como desconto no fechamento. Deseja confirmar?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning btn-sm" id="btn-confirm-discount">
                  <i class="fas fa-check mr-1"></i> Confirmar e concluir
                </button>
              </div>
            </div>
          </div>
        </div>

        {{-- Modal: Autorização de desconto (employee) --}}
        <div class="modal fade" id="modal-auth" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-lock mr-2 text-danger"></i> Autorização necessária</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <p>O valor pago é <strong>R$ <span id="auth-discount-amount"></span></strong> menor que o total. Para conceder esse desconto, um administrador precisa autorizar.</p>
                <div class="form-group mb-2">
                  <label style="font-size:12px;" class="text-uppercase text-muted font-weight-bold">E-mail do administrador</label>
                  <input type="email" id="auth-email" class="form-control form-control-sm" placeholder="admin@exemplo.com" autocomplete="off" />
                </div>
                <div class="form-group mb-0">
                  <label style="font-size:12px;" class="text-uppercase text-muted font-weight-bold">Senha</label>
                  <input type="password" id="auth-password" class="form-control form-control-sm" placeholder="••••••••" autocomplete="new-password" />
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger btn-sm" id="btn-confirm-auth">
                  <i class="fas fa-unlock mr-1"></i> Autorizar e concluir
                </button>
              </div>
            </div>
          </div>
        </div>

        {{-- Modal: Valor exato --}}
        <div class="modal fade" id="modal-exact" tabindex="-1" role="dialog" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-check-circle mr-2 text-success"></i> Confirmar conclusão</h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
              </div>
              <div class="modal-body">
                <p class="mb-0">O valor pago corresponde exatamente ao total dos serviços. Deseja concluir o agendamento?</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success btn-sm" id="btn-confirm-exact">
                  <i class="fas fa-check mr-1"></i> Confirmar e concluir
                </button>
              </div>
            </div>
          </div>
        </div>
      @endif
    @endif

    <div class="mx-4">
      <x-audit-footer :model="$appointment" />
    </div>
  </x-adminlte-card>
@stop

@section('css')
  <style>
    .css-tooltip::after {
      content: attr(data-tip);
      position: absolute;
      bottom: calc(100% + 6px);
      left: 50%;
      transform: translateX(-50%);
      background: rgba(0,0,0,.75);
      color: #fff;
      font-size: 11px;
      line-height: 1.4;
      padding: 4px 8px;
      border-radius: 4px;
      white-space: nowrap;
      pointer-events: none;
      opacity: 0;
      transition: opacity .15s ease;
      z-index: 9999;
    }
    .css-tooltip:hover::after {
      opacity: 1;
    }
  </style>
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

      $('#payment-amount').inputmask('currency', {
        prefix: '',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        rightAlign: false,
      });

      @if($appointment->isEditable() && !$appointment->scheduled_at->isFuture())
        const balance    = {{ $balance ?? 0 }};
        const isEmployee = {{ $isEmployee ? 'true' : 'false' }};

        function submitComplete(adminEmail, adminPassword) {
          $('#complete-admin-email').val(adminEmail || '');
          $('#complete-admin-password').val(adminPassword || '');
          $('#form-complete').submit();
        }

        $('#btn-concluir').on('click', function () {
          if (balance > 0) {
            $('#tip-amount').text('{{ number_format($balance ?? 0, 2, ',', '.') }}');
            $('#modal-tip').modal('show');
          } else if (balance < 0) {
            const absBalance = '{{ number_format(abs($balance ?? 0), 2, ',', '.') }}';
            if (isEmployee) {
              $('#auth-discount-amount').text(absBalance);
              $('#auth-email').val('');
              $('#auth-password').val('');
              $('#modal-auth').modal('show');
            } else {
              $('#discount-amount').text(absBalance);
              $('#modal-discount').modal('show');
            }
          } else {
            $('#modal-exact').modal('show');
          }
        });

        $('#btn-confirm-tip').on('click', function () {
          $('#modal-tip').modal('hide');
          submitComplete();
        });

        $('#btn-confirm-discount').on('click', function () {
          $('#modal-discount').modal('hide');
          submitComplete();
        });

        $('#btn-confirm-exact').on('click', function () {
          $('#modal-exact').modal('hide');
          submitComplete();
        });

        $('#btn-confirm-auth').on('click', function () {
          const email    = $('#auth-email').val().trim();
          const password = $('#auth-password').val();
          if (!email || !password) {
            alert('Informe o e-mail e a senha do administrador.');
            return;
          }
          $('#modal-auth').modal('hide');
          submitComplete(email, password);
        });
      @endif
    });
  </script>
@stop
