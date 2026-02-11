@extends('adminlte::page')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-dashboard"></i>Home</a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
                                </div>
                            </div>
                            <div class="card-body">
                                {{Form::open(['route' => 'contage'])}}
                                @include('inventario::_form')
                                <button type="submit" class="btn btn-secondary">Salvar Regitro</button>
                                {{Form::close()}}
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
