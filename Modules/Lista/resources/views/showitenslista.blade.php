@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',[
        $class = 'Listas',
        $route = 'lista.index',
        $variable = 'Lista ' . $seqPro
    ])

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
                                <h4>Livros Disponíveis</h4>
                            </div>
                            <div class="card-body">
                                @php($liv = 0)
                                @php($aux_lista = 0)
                                @foreach($itens_lista as $item)
                                    @if($liv <> $item->USU_NUMLIV)
                                        {{ Form::open(['route' => 'lista-liv', 'style' => 'display: grid !important; margin-bottom: 2px !important']) }}
                                        <input id="seqPro" name="seqPro" hidden="hidden"
                                               value="{{$item->USU_SEQPRO}}"/>
                                        <input id="codPlt" name="codPlt" hidden="hidden" value="{{$codPlt}}"/>
                                        <input id="numLiv" name="numLiv" hidden="hidden"
                                               value="{{$item->USU_NUMLIV}}"/>
                                        <button type="submit" class="btn btn-secondary">
                                            Página {{ $item->USU_NUMLIV }}
                                        </button>
                                        {{ Form::close() }}
                                        @php($liv = $item->USU_NUMLIV)
                                    @endif

                                    @php($aux_lista = 1)
                                @endforeach

                                @if($aux_lista <> 1)
                                    {{ Form::open(['route' => 'lista-close']) }}
                                    <input id="seqPro" name="seqPro" hidden="hidden"
                                           value="{{$seqPro}}"/>
                                    <button type="submit" class="btn btn-danger">
                                        Encerrar Lista
                                    </button>
                                    {{ Form::close() }}
                                @endif
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
@endsection
