@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Contratos'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-close">
                            </div>
                            <div class="card-header d-flex align-items-center">
                                <div class="col-sm-12 col-md-6">
                                    <h5> Iventário {{ date('d/m/Y', strtotime($datInv)) }} Depósito {{ $codDep }}</h5>
                                </div>
                                <div class="col-sm-12 col-md-6">
                                    <input type="text" class="form-control dropdown-toggle" id="filterend" />
                                </div>
                            </div>
                            <div class="card-body">
                                @foreach($itens_inventario as $item)
                                    <div class="card" style="max-width: 18rem;">
                                        <div class="card-header">
                                            SKU: {{ $item->codpro }} - {{ $item->codend }}
                                        </div>
                                        <div class="card-body">
                                            <h8>{{ $item->despro }}</h8><br>
                                            Contagem: {{ $item->numcon }}
                                        </div>
                                        <a class="btn btn-info" href="{{  route('contagem_inventario', ['id' => date('Y-m-d', strtotime($datInv)), 'dep' => $codDep, 'cont' => $item->numcon, 'prod' => $item->codpro])}}">
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
