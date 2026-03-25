@extends('layouts.app')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('subtitle', 'Atualizar Promoção')
@section('content_header')
  <h1>Atualizar Promoção</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Promoção', 'url' => route('promotions.index')],
    ['label' => 'Atualizar Promoção'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Edição de Promoção" theme="primary" icon="fas fa-percentage">
    <form action="{{route('promotions.update', $promotion)}}" method="POST">
      @method('PUT')
      @csrf

      <div class="row">
        <x-adminlte-input
          name="name"
          label="Nome *"
          placeholder="Dê um nome a promoção"
          value="{{$promotion->name}}"
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
          <x-adminlte-options :options="$discountTypes" :selected="[$promotion->type]"/>
        </x-adminlte-select>

        <x-adminlte-input
          id="value"
          name="value"
          label="Valor *"
          placeholder="Informe o valor"
          value="{{ $promotion->value_formatted }}"
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
          placeholder="Escolha uma data..." fgroup-class="col-md-4" autocomplete="off"
          value="{{$promotion->starts_at_formatted}}"
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
          placeholder="Escolha uma data..." fgroup-class="col-md-4" autocomplete="off"
          value="{{$promotion->ends_at_formatted}}"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        <x-tag-input
          id="categories" label="Categorias"
          placeholder="Adicione as categorias que desejar..." col-size="4"
        />
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          type="submit"
          label="Editar"
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

      $(document).on('click', '.btn-add-tag', function () {
        const targetId = $(this).data('target');
        const input = $(`#${targetId}-input`);
        const name = input.val().trim();
        if (!name) return;

        const list = $(`#${targetId}-list`);
        const hidden = $(`#${targetId}-hidden`);

        if (hidden.find(`input[value="${name}"]`).length) {
          toastr.warning('Tag já adicionada.');
          return;
        }

        list.append(`
          <span class="badge badge-primary mr-1 mb-1">
            ${name}
            <i class="fas fa-times ml-1 remove-tag"
                style="cursor:pointer" data-name="${name}" data-target="${targetId}"
            ></i>
          </span>
        `);

        hidden.append(`<input type="hidden" name="${targetId}[]" value="${name}">`);

        input.val('');
      });

      $('#categories-input').on('keydown', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          $('.btn-add-tag').trigger('click');
        }
      });

      // Popula categorias existentes da promoção
      const existingCategories = @json($promotion->categories->pluck('name'));
      existingCategories.forEach(function (name) {
        $('#categories-list').append(`
          <span class="badge badge-primary mr-1 mb-1">
            ${name}
            <i class="fas fa-times ml-1 remove-tag"
              style="cursor:pointer" data-name="${name}" data-target="categories"
            ></i>
          </span>
        `);
        $('#categories-hidden').append(`<input type="hidden" name="categories[]" value="${name}">`);
      });

      $(document).on('click', '.remove-tag', function () {
        const name = $(this).data('name');
        const targetId = $(this).data('target');

        $(this).closest('.badge').remove();
        $(`#${targetId}-hidden input[value="${name}"]`).remove();
      });
    });
  </script>
@stop
