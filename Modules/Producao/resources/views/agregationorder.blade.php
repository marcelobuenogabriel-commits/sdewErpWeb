@extends('adminlte::page')

@section('content')
    @include('partials.breadcrumb_show',
                ['route' => 'agregationorder',
                 'class' => 'Produção',
                 'variable' => 'Filtro Agregação'])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Entrada de Solicitação da Agregação</h3>
                        <div class="card-tools"></div>
                    </div>
                    <div class="card-body">
                        {{Form::open(['route' => ['executeproc'], 'id' => 'form_filtro'])}}

                        @include('producao::_form')

                        <button type="submit" class="btn btn-secondary">Executar</button>

                        {{Form::close()}}
                    </div>
                    <div class="card-footer">

                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
