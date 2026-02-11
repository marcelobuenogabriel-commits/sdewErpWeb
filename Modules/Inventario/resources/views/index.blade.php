@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Inventário'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-close">
                            </div>
                            <div class="card-header d-flex align-items-center">
                                <h4>Disponíveis</h4>
                            </div>
                            <div class="card-body">
                                @foreach($inventario as $value)
                                    <div class="card " style="max-width: 18rem;">

                                        <div class="card-header">Inventário: {{ date('d/m/Y', strtotime($value->DATINV)) }}</div>
                                        <div class="card-body">
                                            <p class="card-text">
                                                Contagem: {{ $value->ULTCON }}</p>
                                        </div>
                                        <a class="btn btn-success" href="{{  route('show_inventario', ['id' => date('Y-m-d', strtotime($value->DATINV)), 'dep' => $value->CODDEP, 'cont' => $value->ULTCON])}}">
                                            Realizar Contagem
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
