@extends('layouts.app')

@section('subtitle', 'Estabelecimento')

@section('plugins.InputMask', true)

@section('content_header')
  <h1>Estabelecimento</h1>

  <x-breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => route('home')],
    ['label' => 'Estabelecimento'],
  ]"/>
@stop

@section('content')
  <x-adminlte-card title="Dados do Estabelecimento" theme="primary" icon="fas fa-store">
    <form action="{{ route('company.save') }}" method="POST">
      @csrf

      <div class="row border border-dark-subtle rounded mb-2 pt-2">
        <x-adminlte-input
          id="name"
          name="name"
          label="Razão Social *"
          placeholder="p.ex. Barbearia do João LTDA"
          value="{{ old('name', $company->name) }}"
          autocomplete="off"
          fgroup-class="col-md-6"
          required
        />

        <x-adminlte-input
          id="fantasy_name"
          name="fantasy_name"
          label="Nome Fantasia"
          placeholder="p.ex. Barbearia do João"
          value="{{ old('fantasy_name', $company->fantasy_name) }}"
          autocomplete="off"
          fgroup-class="col-md-6"
        />

        <x-adminlte-input
          id="document"
          name="document"
          label="CNPJ/CPF"
          placeholder="p.ex. 12.345.678/0001-99"
          value="{{ old('document', $company->document) }}"
          autocomplete="off"
          fgroup-class="col-md-4"
        />

        <x-adminlte-input
          id="email"
          name="email"
          label="E-mail"
          placeholder="p.ex. contato@barbearia.com"
          value="{{ old('email', $company->email) }}"
          autocomplete="off"
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
          placeholder="p.ex. (21) 91234-5678"
          value="{{ old('phone', $company->phone) }}"
          autocomplete="off"
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
          label="CEP"
          placeholder="p.ex. 21725-180"
          value="{{ old('zip_code', $company->zip_code) }}"
          autocomplete="off"
          fgroup-class="col-md-1"
        />

        <x-adminlte-input
          id="address"
          name="address"
          label="Endereço"
          placeholder="p.ex. Rua da Feira"
          value="{{ old('address', $company->address) }}"
          autocomplete="off"
          fgroup-class="col-md-3"
        />

        <x-adminlte-input
          id="address_number"
          name="address_number"
          label="Número"
          placeholder="p.ex. 123"
          value="{{ old('address_number', $company->address_number) }}"
          autocomplete="off"
          fgroup-class="col-md-1"
        />

        <x-adminlte-input
          id="address_complement"
          name="address_complement"
          label="Complemento"
          placeholder="p.ex. Sala 1"
          value="{{ old('address_complement', $company->address_complement) }}"
          autocomplete="off"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="neighborhood"
          name="neighborhood"
          label="Bairro"
          placeholder="p.ex. Realengo"
          value="{{ old('neighborhood', $company->neighborhood) }}"
          autocomplete="off"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="city"
          name="city"
          label="Cidade"
          placeholder="p.ex. Rio de Janeiro"
          value="{{ old('city', $company->city) }}"
          autocomplete="off"
          fgroup-class="col-md-2"
        />

        <x-adminlte-input
          id="state"
          name="state"
          label="Estado"
          placeholder="p.ex. RJ"
          value="{{ old('state', $company->state) }}"
          autocomplete="off"
          fgroup-class="col-md-1"
          maxlength="2"
        />
      </div>

      <div class="row justify-content-end">
        <x-adminlte-button
          type="submit"
          label="Salvar"
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
      $('#phone').inputmask('(99) 99999-9999');

      // $('#document').on('input', function () {
      //   const digits = $(this).val().replace(/\D/g, '');
      //   $(this).inputmask('remove');
      //
      //   if (digits.length <= 11) {
      //     $(this).inputmask('999.999.999-99');
      //   } else {
      //     $(this).inputmask('99.999.999/9999-99');
      //   }
      // });

      $('#document').inputmask({
        mask: ['999.999.999-99', '99.999.999/9999-99'],
        keepStatic: true,
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
            $('#state').val(data.uf);
          })
          .catch(() => console.error('Erro ao buscar CEP'));
      });
    });
  </script>
@stop
