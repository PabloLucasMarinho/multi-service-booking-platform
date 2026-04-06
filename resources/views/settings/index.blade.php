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

      {{-- Notificações --}}
      <h6 class="text-muted text-uppercase font-weight-bold mb-3" style="font-size: .7rem; letter-spacing: .08em;">
        <i class="fas fa-bell mr-1"></i> Notificações
      </h6>

      <div class="row">
        <div class="form-group col-md-4">
          <label for="rebooking_reminder_days">
            Lembrete de reagendamento
            <i class="fas fa-question-circle text-muted ml-1"
               data-toggle="popover"
               data-trigger="hover focus"
               data-placement="right"
               data-content="O cliente receberá um e-mail e SMS convidando-o a agendar novamente após esse número de dias sem agendamento. Deixe em branco para desativar."
               style="cursor: pointer;"></i>
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
    });
  </script>
@stop
