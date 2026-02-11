@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Recebimento'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-close">
                            </div>
                            <div class="card-header d-flex flex-column align-items-start">
                                <h4>{{$title}}</h4>
                                <br>
                                <h6>{{$description}}</h6>
                            </div>
                            <div class="card-body">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="txtChaveNF"
                                               placeholder="Chave da NFE" onblur="getChaveNotaFiscal()" autofocus>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-close">
                            </div>
                            <div class="card-header d-flex align-items-center">
                                <h3 class="h4">Notas em Processamento</h3>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table id="tableNotas" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>Nota Fiscal</th>
                                                <th>Fornecedor</th>
                                                <th style="width: 150px !important;">Ordem de Compra</th>
                                                <th>% Conclusão</th>
                                                <th style="width: 200px !important;">Ações</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($listaNotas as $value)
                                                <tr>
                                                    <td id="numNfc">{{ $value['NumNfc'] }}</td>
                                                    <td>{{ $value['DadosGerais']['Fornecedor'] }}</td>
                                                    <td id="numOcp">{{ $value['NumOcp'] }}</td>
                                                    <td>
                                                        <div class="progress">
                                                            <div class="progress-bar bg-success" role="progressbar"
                                                                 style="width: {{ $value['DadosGerais']['Percentual'] }}%;"
                                                                 aria-valuenow="{{ $value['DadosGerais']['Percentual'] }}"
                                                                 aria-valuemin="0" aria-valuemax="100">
                                                                <b>{{ $value['DadosGerais']['Percentual'] }}%</b>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        @if($value['DadosGerais']['Percentual'] == 100)
                                                            <button type="submit"
                                                                    title="Fechar NF"
                                                                    class="btn btn-success"
                                                                    onclick="fecharNF('{{ $value['ChvNfc']}}', '{{$value['NumNfc']}}', '{{$value['DadosGerais']['CodFor'] }}')">
                                                                <i class="fa fa-check-square"></i>
                                                            </button>
                                                        @endif

                                                        <button type="button"
                                                                title="Informar OC"
                                                                class="btn btn-warning recebimentoModal"
                                                                data-toggle="modal"
                                                                data-numnfv="{{ $value['NumNfc'] }}"
                                                                data-codfor="{{ $value['DadosGerais']['CodFor'] }}"
                                                                data-chvnfc="{{ $value['ChvNfc'] }}"
                                                                data-target="#recebimentoModal">
                                                            <i class="fa fa-eraser"></i>
                                                        </button>
                                                        
                                                        @if($value['DadosGerais']['Percentual'] != 100)
                                                            <button type="submit"
                                                                    title="Imprimir OC"
                                                                    class="btn btn-dark etiquetaModal"
                                                                    onclick="imprimeOc(<?= $value['NumOcp'] ?>, '<?= $value['ChvNfc'] ?>', '<?= $value['NumNfc'] ?>', '{{ $value['DadosGerais']['CodFor'] }}')">
                                                                <i class="fa fa-print"></i>
                                                            </button>

                                                            @if($value['DadosGerais']['Percentual'] == 500)
                                                                <button type="button"
                                                                        title="Apagar OC"
                                                                        class="btn btn-warning recebimentoModal"
                                                                        data-toggle="modal"
                                                                        data-numnfv="{{ $value['NumNfc'] }}"
                                                                        data-codfor="{{ $value['DadosGerais']['CodFor'] }}"
                                                                        data-chvnfc="{{ $value['ChvNfc'] }}"
                                                                        data-target="#recebimentoModal">
                                                                    <i class="fa fa-eraser"></i>
                                                                </button>
                                                            @endif
                                                        @endif

                                                        @can('Recebimento.recusa')
                                                            @if($value['DadosGerais']['Percentual'] != 100)
                                                                <button type="button"
                                                                        title="Remover NF"
                                                                        class="btn btn-danger"
                                                                        onclick="removeNf('{{ $value['ChvNfc'] }}', '{{ $value['NumNfc'] }}', '{{$value['DadosGerais']['CodFor']}}')">
                                                                    <i class="fa fa-trash"></i>
                                                                </button>
                                                            @endif
                                                        @endcan

                                                        @can('Recebimento.recusa2')
                                                            <button type="button"
                                                                    title="Recusar NF"
                                                                    class="btn btn-danger itemModal"
                                                                    data-toggle="modal"
                                                                    data-numnfv="{{ $value['NumNfc'] }}"
                                                                    data-target="#recusaModal">
                                                                <i class="fa fa-times-circle"></i>
                                                            </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Recebimento-->
            <div class="modal fade" id="recebimentoModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- Fim Modal Header -->

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <div class="form-group">Ordem de Compra</div>
                            <div class="form-group">
                                {{Form::open(['route' => 'change_ocp'])}}

                                {{Form::hidden('codForOc', 'codForOc', ['id' => 'codForOc'])}}
                                {{Form::hidden('numNfcOc', 'numNfcOc', ['id' => 'numNfcOc'])}}
                                {{Form::hidden('chvNfc', 'chvNfc', ['id' => 'chvNfc'])}}

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{Form::number('numOcp', null,  ['class' =>  'form-control'. ($errors->has('numOcp')? ' is-invalid':NULL)])}}
                                    </div>

                                    @error('numOcp')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-3 mb-2">
                                    <button type="submit" class="btn btn-warning">
                                        Enviar
                                    </button>
                                </div>

                                {{Form::close()}}
                            </div>
                        </div>
                        <!-- Fim modal Body -->

                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Recusa-->
            <div class="modal fade" id="recusaModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">

                        <!-- Modal Header -->
                        <div class="modal-header">
                            <h5 class="modal-title" id="exampleModalLabel"></h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <!-- Fim Modal Header -->

                        <!-- Modal Body -->
                        <div class="modal-body">
                            <div class="form-group">Motivo da Recusa</div>
                            <div class="form-group">
                                <form action="conferencia/conferencia" method="POST">
                                    <div class="mb-3">
                                    <textarea type="text" name="txtObsRecusa" id="txtObsRecusa" required="required"
                                              class="form-control"></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning">Enviar</button>
                                </form>
                            </div>
                        </div>
                        <!-- Fim modal Body -->

                        <!-- Modal Footer -->
                        <div class="modal-footer">

                        </div>
                        <!-- Fim modal Footer -->
                    </div>
                </div>
            </div>

        </section>

        <script type="module">
            document.addEventListener('DOMContentLoaded', function() {
                $('#table_id').DataTable({
                    searching: true,
                    pageLength: 5,
                    info: false,
                    select: {
                        info: false
                    },
                    order: [[3, 'desc']],
                    language: {
                        paginate: {
                            next: 'Próximo',
                            previous: 'Anterior'
                        },
                        search: 'Pesquisar',
                        lengthMenu: '',
                        info: ''
                    }
                });

                $('#tableNotas').DataTable({
                    searching: true,
                    
                    select: {
                        info: false
                    },
                    order: [[2, 'asc']],
                    language: {
                        paginate: {
                            next: 'Próximo',
                            previous: 'Anterior'
                        },
                        search: 'Pesquisar',
                        lengthMenu: '',
                        info: ''
                    },
                    info: false,
                });

                $('.itemModal').on('click', function () {
                    const numNfc = $(this).data('numnfv');

                    document.getElementsByClassName('modal-title')[1].innerHTML = 'Nota Fiscal ' + numNfc;
                });

                $(document).on('click', '.recebimentoModal', function () {
                    const numNfc = $(this).data('numnfv');
                    const codFor = $(this).data('codfor');
                    const chvNfc = $(this).data('chvnfc');

                    document.getElementById('codForOc').value = codFor;
                    document.getElementById('numNfcOc').value = numNfc;
                    document.getElementById('chvNfc').value = chvNfc;

                    document.getElementsByClassName('modal-title')[0].innerHTML = 'Nota Fiscal ' + numNfc;
                });
            });
        </script>
        <script>
            function getChaveNotaFiscal() {
                let numChv;

                numChv = document.getElementById('txtChaveNF').value.replace(/\s/g, '');

                if (numChv) {
                    $.ajax({
                        url: "{{ route('recebimento.store') }}",
                        type: 'POST',
                        data: {
                            '_token': '{{csrf_token()}}',
                            'numChv': numChv
                        },
                        dataType: 'json',
                        async: true,
                        success: function (point) {
                            if (point.code == 500) {
                                mdtoast(
                                    point.msg, {
                                        type: 'warning',
                                        duration: 3000,
                                        position: 'top left'
                                    });
                                document.getElementById('txtChaveNF').value = '';

                                setTimeout(() => window.location.reload(), 3000);
                            } else if (point.code == 202) {
                                mdtoast(
                                    point.msg, {
                                        type: 'alert',
                                        duration: 3000,
                                        position: 'top left'
                                    });
                                document.getElementById('txtChaveNF').value = '';

                                setTimeout(() => window.location.reload(), 3000);
                            } else {
                                mdtoast(
                                    point.msg, {
                                        type: 'success',
                                        duration: 3000,
                                        position: 'top left'
                                    });

                                document.getElementById('txtChaveNF').value = '';

                                setTimeout(() => window.location.reload(), 3000);
                            }
                        },
                        error: function (jqXHR, textStatus, errorThrown) {
                            var error = jqXHR.responseText;
                            var content = error.content;
                            console.log(content.message);
                        }
                    });
                }
            }

            function imprimeOc(numOcp, chvNfc, numNfc, codFor) {
                $.ajax({
                    url: "{{ route('print_tag') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'numOcp': numOcp,
                        'chvNfc': chvNfc,
                        'numNfc': numNfc,
                        'codFor': codFor
                    },
                    dataType: 'json',
                    async: true,
                    success: function (point) {
                        if (point.code == 200) {
                            mdtoast(
                                point.msg, {
                                    type: 'success',
                                    duration: 5000,
                                    position: 'top left'
                                });
                        } else {
                            mdtoast(
                                point.msg, {
                                    type: 'error',
                                    duration: 5000,
                                    position: 'top left'
                                });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var error = jqXHR.responseText;
                        var content = error.content;
                        console.log(error);
                    }
                });
            }

            function removeNf(chvNfc, numNfc, codFor) {
                $.ajax({
                    url: "{{ route('remove_nfc') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'chvNfc': chvNfc,
                        'numNfc': numNfc,
                        'codFor': codFor
                    },
                    success: function (response) {
                        if (response.code == 200) {
                            mdtoast(
                                response.msg, {
                                    type: 'success',
                                    duration: 5000,
                                    position: 'top left'
                                });

                            setTimeout(() => window.location.reload(), 3000);
                        } else {
                            mdtoast(
                                response.msg, {
                                    type: 'error',
                                    duration: 5000,
                                    position: 'top left'
                                });
                                
                            setTimeout(() => window.location.reload(), 3000);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var error = jqXHR.responseText;
                        var content = error.content;
                        console.log(error);
                    }
                });
            }

            function fecharNF(chvNfc, numNfc, codFor) {
                $.ajax({
                    url: "{{ route('close_nfc') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'chvNfc': chvNfc,
                        'numNfc': numNfc,
                        'codFor': codFor
                    },
                    dataType: 'json',
                    async: true,
                    success: function (point) {
                        if (point.code == 200) {
                            mdtoast(
                                point.msg, {
                                    type: 'success',
                                    duration: 5000,
                                    position: 'top left'
                                });

                            setTimeout(() => window.location.reload(), 3000);
                        } else {
                            mdtoast(
                                point.msg, {
                                    type: 'error',
                                    duration: 5000,
                                    position: 'top left'
                                });
                                
                            setTimeout(() => window.location.reload(), 3000);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var error = jqXHR.responseText;
                        var content = error.content;
                        console.log(error);
                    }
                });
            }
        </script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
