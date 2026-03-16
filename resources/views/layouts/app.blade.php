@extends('adminlte::page')

{{-- Extend and customize the browser title --}}

@section('title')
  {{ config('adminlte.title') }}
  @hasSection('subtitle')
    | @yield('subtitle')
  @endif
@stop

@section('plugins.Toastr', true)

@section('content')
  @yield('content_body')
@stop

{{-- Create a common footer --}}

@section('footer')
  <div class="d-flex justify-content-end">
    <strong>
      Feito por:
      <a href="{{ config('app.dev_url', '#') }}" target="_blank" class="text-dark">
        {{ config('app.dev_name', 'Pablo Marinho') }}
      </a>
    </strong>
  </div>
@stop

@push('js')
  <script>
    $(document).ready(function () {
      // Configurações globais (Opcional)
      toastr.options = {
        "progressBar": true,
        "positionClass": "toast-top-center",
      }

      // Exemplo de disparo manual via JS
      // toastr.success('Operação realizada com sucesso!');

      // Integração com as sessões do Laravel
      @if(Session::has('success'))
      toastr.success("{{ Session::get('success') }}");
      @endif

      @if(Session::has('error'))
      toastr.error("{{ Session::get('error') }}");
      @endif
    });
  </script>
@endpush
