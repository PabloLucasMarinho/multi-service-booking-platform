@extends('adminlte::auth.login')

@section('plugins.Toastr', true)

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
    });
  </script>
@endpush
