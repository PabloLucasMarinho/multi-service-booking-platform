@extends('layouts.app')

@section('subtitle', 'Cadastrar Funcionário')

@section('plugins.Tempus', true)
@section('plugins.InputMask', true)

@section('content_header')
  <h1>Cadastrar Funcionário</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Funcionários', 'url' => route('users.index')],
    ['label' => 'Cadastrar Funcionário'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Cadastro de Funcionário" theme="primary" icon="fas fa-address-card">
    <form action="{{route('users.store')}}" method="POST">
      @csrf

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-input
          id="name"
          name="name"
          label="Nome *"
          placeholder="p.ex. João da Silva"
          value="{{old('name')}}"
          autocomplete="name"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="document"
          name="document"
          label="CPF *"
          placeholder="p.ex. 123.456.789-00"
          value="{{old('document')}}"
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
          value="{{old('email')}}"
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
          value="{{old('phone')}}"
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
          value="{{old('zip_code')}}"
          autocomplete="postal_code"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="address"
          name="address"
          label="Endereço *"
          placeholder="p.ex. Rua da Feira, 123"
          value="{{old('address')}}"
          autocomplete="address-line1"
          fgroup-class="col-md-4"
          required
        />

        <x-adminlte-input
          id="address_complement"
          name="address_complement"
          label="Complemento"
          placeholder="p.ex. Casa 1"
          value="{{old('address_complement')}}"
          autocomplete="address-line2"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="neighborhood"
          name="neighborhood"
          label="Bairro *"
          placeholder="p.ex. Realengo"
          value="{{old('neighborhood')}}"
          autocomplete="address-line3"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="city"
          name="city"
          label="Cidade *"
          placeholder="p.ex. Rio de Janeiro"
          value="{{old('city')}}"
          autocomplete="address-level2"
          fgroup-class="col-md-2"
        />
      </div>

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-select name="role" label="Função" fgroup-class="col-md-4">
          <x-slot name="prependSlot">
            <div class="input-group-text">
              <i class="fas fa-user-tag"></i>
            </div>
          </x-slot>
          <option value="employee">Funcionário</option>
          <option value="admin">Administrador</option>
        </x-adminlte-select>

        <x-adminlte-input
          id="salary"
          name="salary"
          label="Salário"
          placeholder="p.ex. 2.500,00"
          value="{{old('salary')}}"
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

  <x-adminlte-modal
    id="userExists" title="Usuário já cadastrado" theme="warning"
    icon="fas fa-exclamation-triangle" size="md" static-backdrop
  >
    <div class="text-center py-2">
      <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
      <h5 class="font-weight-bold">Dados pertencentes a um usuário inativo</h5>
      <p class="text-muted mb-1">Os dados informados já pertencem ao usuário:</p>
      <h4 class="font-weight-bold text-dark" id="modal-user-name"></h4>
      <hr>
      <p class="text-muted">O que deseja fazer com este cadastro?</p>
    </div>

    <x-slot name="footerSlot">
      <form id="form-restore" action="" method="POST" class="mr-auto">
        @method('PUT')
        @csrf
        <x-adminlte-button theme="success" icon="fas fa-user-check" label="Reativar cadastro" type="submit"/>
      </form>

      <form id="form-force-delete" action="" method="POST">
        @method('DELETE')
        @csrf
        <x-adminlte-button theme="danger" icon="fas fa-trash" label="Apagar permanentemente" type="submit"/>
      </form>
    </x-slot>
  </x-adminlte-modal>
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

      let pendingFormData = null;

      $('form[action="{{ route('users.store') }}"]').on('submit', function (e) {
        e.preventDefault();

        const form = $(this);

        $.ajax({
          url: '{{ route('users.store') }}',
          method: 'POST',
          data: form.serialize(),
          success: function (response) {
            if (response.deleted_user) {
              pendingFormData = form.serialize();
              $('#modal-user-name').text(response.name);
              $('#form-restore').attr('action', '/users/' + response.uuid + '/restore');
              $('#form-force-delete').attr('action', '/users/' + response.uuid + '/anonymize');
              $('#userExists').modal('show');
            } else {
              form.off('submit').submit();
            }
          },
        });
      });

      $('#form-force-delete').on('submit', function (e) {
        e.preventDefault();

        const anonymizeUrl = $(this).attr('action');

        $.ajax({
          url: anonymizeUrl,
          method: 'POST',
          data: $(this).serialize() + '&_method=DELETE',
          success: function () {
            $.ajax({
              url: '{{ route('users.store') }}',
              method: 'POST',
              data: pendingFormData,
              success: function () {
                window.location.href = '{{ route('users.index') }}?success=Funcionário+cadastrado+com+sucesso!';
              },
              error: function (response) {
                console.error('Erro ao cadastrar:', response.responseJSON);
              }
            });
          },
          error: function (response) {
            console.error('Erro ao anonimizar:', response.responseJSON);
          }
        });
      });

      $('#form-restore').on('submit', function (e) {
        e.preventDefault();

        const restoreUrl = $(this).attr('action');

        $.ajax({
          url: restoreUrl,
          method: 'POST',
          data: $(this).serialize() + '&_method=PUT',
          success: function () {
            window.location.href = '{{ route('users.index') }}?restored=Funcionário+reativado+com+sucesso!';
          },
          error: function (response) {
            console.error('Erro ao restaurar:', response.responseJSON);
          }
        });
      });
    });
  </script>
@stop
