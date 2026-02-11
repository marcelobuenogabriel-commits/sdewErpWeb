@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Listas'])

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
                                <h4>Listas Disponíveis</h4>
                            </div>
                            <div class="card-body">
                                @foreach($listas as $lista)
                                    <div class="card " style="max-width: 40rem;">

                                        <div class="card-header">
                                            Sequência de Procedimento: {{ $lista->USU_SEQPRO }}
                                        </div>
                                        <div class="card-body">
                                            <p class="">
                                                Depósito Origem: {{ $lista->USU_DEPORI }}
                                            </p>
                                            <p class="">
                                                Depósito Destino: {{ $lista->USU_DEPDES }}
                                            </p>
                                            {{Form::open(['route' => 'lista-ite'])}}
                                            @include('lista::_form')
                                        </div>
                                        
                                        <div class="card-footer d-flex flex-wrap gap-1">
                                            {{-- Botão Iniciar Separação --}}
                                                <button type="submit" class="btn btn-secondary mr-1">
                                                    Iniciar Separação
                                                </button>
                                            {{ Form::close() }}

                                            {{-- Botão Criar Pallet --}}
                                            <button type="button"
                                                    title="Criar Pallet"
                                                    class="btn btn-warning itemModal mr-1"
                                                    data-toggle="modal"
                                                    data-target="#conferencia_pallet"
                                                    data-numprj="{{$lista->USU_NUMPRJ}}"
                                                    data-codfpj="{{$lista->USU_CODFPJ}}">
                                                <i class="fa fa-cubes"></i>
                                            </button>

                                            @can('Recebimento.recusa')
                                                {{-- Botão Encerrar Lista --}}
                                                {{ Form::open(['route' => 'lista-cancel']) }}
                                                    <input id="seqPro" name="seqPro" hidden value="{{$lista->USU_SEQPRO}}"/>
                                                    <button type="submit" class="btn btn-danger">
                                                        Encerrar Lista
                                                    </button>
                                                {{ Form::close() }}
                                            @endcan
                                        </div>

                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>

    @include('models.createPallet')

    <script type="module">
        $(document).ready(function () {

            $('.itemModal').on('click', function () {
                const numPrj = $(this).data('numprj');
                const codFpj = $(this).data('codfpj');

                document.getElementById('txtNumPrj').value = numPrj;
                document.getElementById('txtCodFpj').value = codFpj;
                document.getElementById('txtOrigemReq').value = 'lista';
            });
        });
    </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@endsection
