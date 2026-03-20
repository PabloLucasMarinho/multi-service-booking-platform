@extends('adminlte::page')

@section('title')
  {{ config('adminlte.title') }}
  @hasSection('subtitle')
    | @yield('subtitle')
  @endif
@stop

@section('plugins.Toastr', true)

@section('footer')
  <div class="d-flex justify-content-end">
    <strong>
      Feito por:
      <a href="{{ config('app.dev_url', '#') }}" target="_blank" class="text-primary">
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

      // Integração com as sessões do Laravel
      @if(Session::has('success'))
      toastr.success("{{ Session::get('success') }}");
      @endif

      @if(Session::has('error'))
      toastr.error("{{ Session::get('error') }}");
      @endif

      @if(Session::has('warning'))
      toastr.warning("{{ Session::get('warning') }}");
      @endif
    });
  </script>
@endpush
