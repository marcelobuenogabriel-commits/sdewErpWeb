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
                                <div class="row">
                                    <div class="col-md-12">


                                        @foreach($itens_lista as $itens)
                                            <div class="card " style="max-width: 40rem;">

                                                <input id="seqPro" name="seqPro" hidden="hidden"
                                                       value="{{$itens->USU_SEQPRO}}"/>
                                                <input id="codPro" name="codPro" hidden="hidden"
                                                       value="{{$itens->USU_CODPRO}}"/>
                                                <input id="codEnd" name="codEnd" hidden="hidden"
                                                       value="{{$itens->CODEND}}"/>

                                                <div class="card-header">
                                                    <div class="col-md-12">
                                                        <b>Lista de Separação: </b> {{$seqPro}}
                                                    </div>

                                                    <div class="col-md-12">
                                                        <b>Produto:</b> {{ $itens->USU_CODPRO }}
                                                        - {{ $itens->USU_DESPRO }}
                                                    </div>

                                                    <div class="col-md-12">
                                                        <b>Pallet:</b> {{ $codPlt }}
                                                    </div>
                                                </div>
                                                <div class="card-body">
                                                    <div class="col-md-12">
                                                        Quantidade: {{ $itens->USU_QTDMOV }}
                                                    </div>

                                                    <div class="col-md-12">
                                                        Endereço: {{ $itens->CODEND }}
                                                    </div>

                                                    <div class="col-md-12">
                                                        <input type="number" id="qtdFal" class="form-control"
                                                               placeholder="Qtd.Faltante"
                                                               onblur="setQtdFaltante()" value="0">
                                                    </div>
                                                </div>
                                                <div class="card-footer">
                                                    <a href="{{ route('lista.index') }}" type="submit"
                                                       class="btn btn-secondary">
                                                        Alterar Pallet
                                                    </a>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">

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

@section('js')
    <script>

        function setQtdFaltante() {
            seqPro = document.getElementById('seqPro').value;
            codPro = document.getElementById('codPro').value;
            qtdFal = document.getElementById('qtdFal').value;
            codEnd = document.getElementById('codEnd').value;

            $.ajax({
                url: '{{route('lista-set')}}',
                type: 'POST',
                data: {
                    '_token': '{{ csrf_token() }}',
                    'seqPro': seqPro,
                    'codPro': codPro,
                    'qtdFal': qtdFal,
                    'codEnd': codEnd
                },
                dataType: 'json',
                async: true,
                success: function (point) {
                    window.location.href = '{{ route('lista.index') }}';
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    alert(textStatus);
                }
            });
        }
    </script>
@stop
