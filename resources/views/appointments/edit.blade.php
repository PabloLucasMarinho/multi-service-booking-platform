@extends('layouts.app')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('subtitle', 'Editar Agendamento')
@section('content_header')
  <h1>Editar Agendamento</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Agendamentos', 'url' => route('appointments.index')],
    ['label' => 'Editar Agendamento'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Editar Agendamento" theme="primary" icon="fas fa-clock">
    <form action="{{route('appointments.update', $appointment)}}" method="POST">
      @method('PUT')
      @csrf

      <div class="row">
        @php
          $config = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
            'dayViewHeaderFormat' => 'MMM YYYY',
          ];
        @endphp
        <x-adminlte-input-date
          id="scheduled_at" name="scheduled_at" :config="$config" label="Data *"
          placeholder="Escolha uma data..." fgroup-class="col-md-6" autocomplete="off" required
          value="{{$appointment->scheduled_date}}"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        @php
          $config = [
            'format' => 'LT',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
          ];
        @endphp
        <x-adminlte-input-date
          id="scheduled_hour" name="scheduled_hour" :config="$config" label="Hora *"
          placeholder="Informe o horário..." fgroup-class="col-md-6" autocomplete="off" required
          value="{{$appointment->scheduled_time}}"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-clock"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-select
          name="client" label="Cliente *" fgroup-class="col-md-6" required
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-user-friends"></i>
            </div>
          </x-slot>

          @if($clients->isEmpty())
            <option value="">Nenhum cliente cadastrado...</option>
          @else
            <x-adminlte-options
              :options="$clients->pluck('name', 'uuid')->toArray()"
              :selected="[$appointment->client_uuid]"
              placeholder="Escolha um cliente..."
            />
          @endif
        </x-adminlte-select>

        @php
          $isDisabled = auth()->user()->cannot('updateAny', \App\Models\User::class);
        @endphp

        @if($isDisabled)
          <input type="hidden" name="user" value="{{$appointment->user_uuid}}">
        @endif

        <x-adminlte-select
          name="user" label="Funcionário *" fgroup-class="col-md-6"
          :disabled="$isDisabled"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-user"></i>
            </div>
          </x-slot>

          @if($users->isEmpty())
            <option value="">Nenhum funcionário cadastrado...</option>
          @else
            <x-adminlte-options
              :options="$users->pluck('name', 'uuid')->toArray()"
              :selected="[$isDisabled ? auth()->user()->uuid : '']"
            />
          @endif
        </x-adminlte-select>

        <x-adminlte-textarea
          id="notes" name="notes" fgroup-class="col-md-12" placeholder="Insira suas observações..."
          label="Observações"
        >
          {{$appointment->notes}}
        </x-adminlte-textarea>
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          type="submit"
          label="Cadastrar"
          theme="success"
          icon="fas fa-save"
        />
      </div>
    </form>
  </x-adminlte-card>
@stop
