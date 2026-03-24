@extends('layouts.app')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('subtitle', 'Cadastrar Promoção')
@section('content_header')
  <h1>Cadastrar Promoção</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Promoção', 'url' => route('promotions.index')],
    ['label' => 'Cadastrar Promoção'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Cadastro de Promoção" theme="primary" icon="fas fa-percentage">
    <form action="{{route('promotions.store')}}" method="POST">
      @csrf

      <div class="row">
        <x-adminlte-input
          name="name"
          label="Nome *"
          placeholder="Dê um nome a promoção"
          value="{{old('name')}}"
          autocomplete="on"
          fgroup-class="col-md-4"
          required
        />

        @php
          $discountTypes = [
            'percentage' => 'Porcentagem (%)',
            'fixed' => 'Valor Fixo (R$)',
          ];
        @endphp

        <x-adminlte-select
          id="type"
          name="type"
          label="Tipo *"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-tag"></i>
            </div>
          </x-slot>
          <x-adminlte-options :options="$discountTypes" :selected="[old('type', 'percentage')]"/>
        </x-adminlte-select>

        <x-adminlte-input
          id="value"
          name="value"
          label="Valor *"
          placeholder="Informe o valor"
          value="{{ old('value') }}"
          autocomplete="off"
          fgroup-class="col-md-4"
          required
        >
          <x-slot name="prependSlot">
            <div class="input-group-text" id="value-icon">
              <i class="fas fa-percentage"></i>
            </div>
          </x-slot>
        </x-adminlte-input>

        @php
          $startsAtConfig = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
            'dayViewHeaderFormat' => 'MMM YYYY',
          ];
        @endphp
        <x-adminlte-input-date
          id="starts_at" name="starts_at" :config="$startsAtConfig" label="Início *" required
          placeholder="Escolha uma data..." fgroup-class="col-md-6" autocomplete="off"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        @php
          $endsAtConfig = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
            'dayViewHeaderFormat' => 'MMM YYYY',
          ];
        @endphp
        <x-adminlte-input-date
          id="ends_at" name="ends_at" :config="$endsAtConfig" label="Término *" required
          placeholder="Escolha uma data..." fgroup-class="col-md-6" autocomplete="off"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>
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

@section('js')
  <script>
    $(document).ready(function () {
      function applyValueMask(type) {
        $('#value').inputmask('remove');

        if (type === 'percentage') {
          $('#value-icon').html('<i class="fas fa-percentage"></i>');
          $('#value').inputmask('decimal', {
            radixPoint: ',',
            digits: 2,
            digitsOptional: false,
            placeholder: '0',
            rightAlign: false,
            max: 100,
          });
        } else {
          $('#value-icon').html('R$');
          $('#value').inputmask('currency', {
            prefix: '',
            groupSeparator: '.',
            radixPoint: ',',
            digits: 2,
            digitsOptional: false,
            placeholder: '0',
            rightAlign: false,
          });
        }
      }

      // Aplica a máscara inicial baseado no valor selecionado
      applyValueMask($('#type').val());

      $('#type').on('change', function () {
        $('#value').val('');
        applyValueMask($(this).val());
      });
    });
  </script>
@stop
