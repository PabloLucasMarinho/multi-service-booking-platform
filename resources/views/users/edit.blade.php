@extends('layouts.app')

@section('subtitle', 'Editar Funcionário')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('content_header')
  <h1>Editar Funcionário</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Funcionários', 'url' => route('users.index')],
    ['label' => 'Editar Funcionário'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Edição de Funcionário" theme="primary" icon="fas fa-address-card">
    <form action="{{route('users.update', $user)}}" method="POST">
      @method('PUT')
      @csrf

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-input
          id="name"
          name="name"
          label="Nome *"
          placeholder="p.ex. João da Silva"
          value="{{$user->name}}"
          autocomplete="name"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="document"
          name="document"
          label="CPF *"
          placeholder="p.ex. 123.456.789-00"
          value="{{$user->document}}"
          autocomplete="on"
          fgroup-class="col-md-6"
          required
        />

        @php
          $config = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'bottom'],
            'daysOfWeekDisabled' => [0, 6],
            'dayViewHeaderFormat' => 'MMM YYYY',
            'viewMode' => 'years'
          ];
        @endphp
        <x-adminlte-input-date
          id="date_of_birth" name="date_of_birth" :config="$config" label="Data de Nascimento *"
          placeholder="Escolha uma data..." fgroup-class="col-md-4" autocomplete="off"
          value="{{$user->date_of_birth_formatted}}"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text bg-dark-subtle">
              <i class="fas fa-calendar-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input-date>

        <x-adminlte-input
          id="email"
          name="email"
          label="E-mail"
          placeholder="p.ex. joao@gmail.com"
          value="{{$user->email}}"
          autocomplete="email"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-at"></i>
            </div>
          </x-slot>
        </x-adminlte-input>

        <x-adminlte-input
          id="phone"
          name="phone"
          label="Telefone"
          placeholder="p.ex. (21)91234-5678"
          value="{{$user->phone_formatted}}"
          autocomplete="tel-national"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-phone-alt"></i>
            </div>
          </x-slot>
        </x-adminlte-input>
      </div>

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-input
          id="zip_code"
          name="zip_code"
          label="CEP *"
          placeholder="p.ex. 32123-123"
          value="{{$user->zip_code_formatted}}"
          autocomplete="postal_code"
          fgroup-class="col-md-1"
        />

        <x-adminlte-input
          id="address"
          name="address"
          label="Endereço *"
          placeholder="p.ex. Rua da Feira, 123"
          value="{{$user->address}}"
          autocomplete="address-line1"
          fgroup-class="col-md-3"
          required
        />

        <x-adminlte-input
          id="address_number"
          name="address_number"
          label="Número"
          placeholder="p.ex. 123"
          value="{{$user->address_number}}"
          autocomplete="off"
          fgroup-class="col-md-1"
        />

        <x-adminlte-input
          id="address_complement"
          name="address_complement"
          label="Complemento"
          placeholder="p.ex. Casa 1"
          value="{{$user->address_complement}}"
          autocomplete="address-line2"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="neighborhood"
          name="neighborhood"
          label="Bairro *"
          placeholder="p.ex. Realengo"
          value="{{$user->neighborhood}}"
          autocomplete="address-line3"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="city"
          name="city"
          label="Cidade *"
          placeholder="p.ex. Rio de Janeiro"
          value="{{$user->city}}"
          autocomplete="address-level2"
          fgroup-class="col-md-2"
        />


        <x-adminlte-select name="state" label="Estado *" fgroup-class="col-md-1">
          <x-adminlte-options
            :options="collect(App\Enums\BrazilianState::cases())->pluck('value', 'value')->toArray()"
            :selected="[old('state', $user->state)]"
            placeholder="UF"
          />
        </x-adminlte-select>
      </div>

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-select name="role" label="Função *" fgroup-class="col-md-4">
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-user-tag"></i>
            </div>
          </x-slot>
          <x-adminlte-options
            :options="$roles"
            :selected="[old('role', $user->role->name)]"
            placeholder="Selecione uma função..."
          />
        </x-adminlte-select>

        <x-adminlte-input
          id="salary"
          name="salary"
          label="Salário"
          placeholder="p.ex. 2.500,00"
          value="{{$user->salary_formatted}}"
          autocomplete="off"
          fgroup-class="col-md-4"
        >
          <x-slot name="prependSlot">
            <div class="input-group-text">
              R$
            </div>
          </x-slot>
        </x-adminlte-input>

        @php
          $config = [
            'format' => 'L',
            'locale' => 'pt-br',
            'widgetPositioning' => ['horizontal' => 'auto', 'vertical' => 'top'],
            'daysOfWeekDisabled' => [0, 6],
            'dayViewHeaderFormat' => 'MMM YYYY',
          ];
        @endphp
        <x-adminlte-input-date
          id="admission_date" name="admission_date" :config="$config" label="Data de Admissão *"
          placeholder="Escolha uma data..." fgroup-class="col-md-4" autocomplete="off"
          value="{{$user->admission_date_formatted}}"
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
          label="Editar"
          theme="success"
          icon="fas fa-pen"
        />
      </div>
    </form>

    <x-audit-footer :model="$user" />
  </x-adminlte-card>
@stop

@section('js')
  <script>
    $(document).ready(function () {
      $('#phone').inputmask('(99) 99999-9999');
      $('#document').inputmask('999.999.999-99');

      $('#salary').inputmask('currency', {
        prefix: '',
        groupSeparator: '.',
        radixPoint: ',',
        digits: 2,
        digitsOptional: false,
        placeholder: '0',
        rightAlign: false,
      });

      $('#zip_code').inputmask('99999-999').on('blur', function () {
        let cep = this.value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        fetch(`https://viacep.com.br/ws/${cep}/json/`)
          .then(res => res.json())
          .then(data => {
            if (data.erro) return;
            $('#address').val(data.logradouro);
            $('#neighborhood').val(data.bairro);
            $('#city').val(data.localidade);
          })
          .catch(() => console.error('Erro ao buscar CEP'));
      });
    });
  </script>
@stop
