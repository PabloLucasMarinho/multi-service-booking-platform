@extends('layouts.app')

@section('subtitle', 'Configurações')

@section('content_header')
  <h1>Configurações</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Configurações'],
  ]"/>
@stop

@section('content')
  <form action="{{ route('settings.save') }}" method="POST">
    @csrf

    <x-adminlte-card title="Configurações" theme="primary" icon="fas fa-sliders-h">

      {{-- Inputs compactos --}}
      <div class="row">
        <div class="col-md-6">
          <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:.7rem;letter-spacing:.08em;">
            <i class="fas fa-bell mr-1"></i> Notificações
          </h6>
          <div class="form-group">
            <label for="rebooking_reminder_days">
              Lembrete de reagendamento
              <i class="fas fa-question-circle text-muted ml-1"
                 data-toggle="popover"
                 data-trigger="hover focus"
                 data-placement="right"
                 data-content="O cliente receberá um e-mail e SMS convidando-o a agendar novamente após esse número de dias sem agendamento. Deixe em branco para desativar."
                 style="cursor:pointer;"></i>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-calendar-check"></i></span>
              </div>
              <input
                type="number"
                id="rebooking_reminder_days"
                name="rebooking_reminder_days"
                class="form-control @error('rebooking_reminder_days') is-invalid @enderror"
                placeholder="p.ex. 30"
                value="{{ old('rebooking_reminder_days', $company->rebooking_reminder_days) }}"
                min="1"
                max="365"
                autocomplete="off"
              >
              <div class="input-group-append">
                <span class="input-group-text">dias</span>
              </div>
              @error('rebooking_reminder_days')
              <span class="invalid-feedback d-block">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:.7rem;letter-spacing:.08em;">
            <i class="fas fa-percentage mr-1"></i> Descontos
          </h6>
          <div class="form-group">
            <label for="max_discount_percentage">
              Teto de desconto
              <i class="fas fa-question-circle text-muted ml-1"
                 data-toggle="popover"
                 data-trigger="hover focus"
                 data-placement="right"
                 data-content="Limite máximo de desconto total (promoção + manual) sobre o preço base do serviço. Por exemplo: com teto de 30%, um serviço de R$ 100 não pode ter mais de R$ 30 de desconto, independente da promoção ativa ou do desconto manual aplicado. Deixe em branco para não aplicar limite."
                 style="cursor:pointer;"></i>
            </label>
            <div class="input-group">
              <div class="input-group-prepend">
                <span class="input-group-text"><i class="fas fa-percentage"></i></span>
              </div>
              <input
                type="number"
                id="max_discount_percentage"
                name="max_discount_percentage"
                class="form-control @error('max_discount_percentage') is-invalid @enderror"
                placeholder="p.ex. 30"
                value="{{ old('max_discount_percentage', $company->max_discount_percentage) }}"
                min="1"
                max="100"
                autocomplete="off"
              >
              <div class="input-group-append">
                <span class="input-group-text">%</span>
              </div>
              @error('max_discount_percentage')
              <span class="invalid-feedback d-block">{{ $message }}</span>
              @enderror
            </div>
          </div>
        </div>
      </div>

      <hr>

      {{-- Autorizações de desconto --}}
      <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size:.7rem;letter-spacing:.08em;">
        <i class="fas fa-user-check mr-1"></i> Autorizações de Desconto
      </h6>

      <div class="form-group">
        <label>
          Funcionários autorizados a aplicar desconto manual
          <i class="fas fa-question-circle text-muted ml-1"
             data-toggle="popover"
             data-trigger="hover focus"
             data-placement="right"
             data-content="Somente os funcionários marcados poderão informar um desconto manual ao adicionar um serviço em um agendamento. O dono sempre tem essa permissão."
             style="cursor:pointer;"></i>
        </label>

        @error('discount_users.*')
        <div class="text-danger mb-2" style="font-size:.875em;">{{ $message }}</div>
        @enderror

        @php
          $allUsers = $users->flatten();
          $checkedUuids = old('discount_users', $allUsers->where('can_apply_manual_discount', true)->pluck('uuid')->toArray());
        @endphp

        @forelse($users as $roleName => $roleUsers)
          <div class="mb-3">
            <div class="custom-control custom-checkbox mb-2">
              <input
                type="checkbox"
                class="custom-control-input select-all-role"
                id="select_all_{{ $roleName }}"
                data-role="{{ $roleName }}"
              >
              <label class="custom-control-label font-weight-bold" for="select_all_{{ $roleName }}">
                Marcar todos — {{ $roleUsers->first()->role->name_formatted ?? $roleName }}
              </label>
            </div>

            <div class="row ml-2">
              @foreach($roleUsers as $user)
                <div class="col-md-3 col-sm-4 mb-2">
                  <div class="custom-control custom-checkbox">
                    <input
                      type="checkbox"
                      class="custom-control-input discount-user-checkbox role-{{ $roleName }}"
                      id="discount_user_{{ $user->uuid }}"
                      name="discount_users[]"
                      value="{{ $user->uuid }}"
                      {{ in_array($user->uuid, $checkedUuids) ? 'checked' : '' }}
                    >
                    <label class="custom-control-label" for="discount_user_{{ $user->uuid }}">
                      {{ $user->name }}
                    </label>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        @empty
          <div class="col-12">
            <span class="text-muted">Nenhum funcionário cadastrado.</span>
          </div>
        @endforelse
      </div>

      <hr>

      <div class="row justify-content-end">
        <div class="col-auto">
          <x-adminlte-button
            type="submit"
            label="Salvar"
            theme="success"
            icon="fas fa-save"
          />
        </div>
      </div>

    </x-adminlte-card>
  </form>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      $('[data-toggle="popover"]').popover();

      // Ao clicar em "Marcar todos", marca/desmarca todos do grupo
      $('.select-all-role').on('change', function () {
        const role = $(this).data('role');
        $(`.role-${role}`).prop('checked', this.checked);
      });

      // Ao alterar um checkbox individual, sincroniza o estado do "marcar todos"
      $('.discount-user-checkbox').on('change', function () {
        const role = $(this).attr('class').match(/role-(\S+)/)[1];
        const total = $(`.role-${role}`).length;
        const checked = $(`.role-${role}:checked`).length;
        $(`#select_all_${role}`).prop('checked', total === checked).prop('indeterminate', checked > 0 && checked < total);
      });

      // Inicializa o estado dos "marcar todos" com base nos checkboxes já marcados
      $('.select-all-role').each(function () {
        const role = $(this).data('role');
        const total = $(`.role-${role}`).length;
        const checked = $(`.role-${role}:checked`).length;
        $(this).prop('checked', total === checked && total > 0).prop('indeterminate', checked > 0 && checked < total);
      });
    });
  </script>
@stop
