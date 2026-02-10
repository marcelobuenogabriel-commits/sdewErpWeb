@extends('adminlte::page')

@section('content')
<div class="container">
    <div class="row justify-content-center">

        @include('partials.alerts')
        
        <div class="col-md-8">
        </div>
    </div>
</div>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
