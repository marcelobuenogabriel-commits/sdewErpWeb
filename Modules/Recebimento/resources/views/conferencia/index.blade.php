@extends('adminlte::page')

@section('content')
    
    @include('partials.breadcrumb_index', ['class' => 'Conferência'])

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
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table id="table_itens" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>SKU</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($list_itens as $value)
                                                <tr>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            @if($value->codpin <> ' ')
                                                                class="btn btn-danger col-md-12 itemModal"
                                                            @else
                                                                class="btn btn-warning col-md-12 itemModal"
                                                            @endif
                                                            data-toggle="modal"
                                                            data-id="{{ $value->numnfc }}"
                                                            data-codpro="{{ $value->codpro }}"
                                                            data-seqipo="{{ $value->seqipo }}"
                                                            data-chvnel="{{ $value->chvnel }}"
                                                            data-numocp="{{ $value->numocp }}"
                                                            data-codfor="{{ $value->codfor }}"
                                                            data-seqipc="{{ $value->seqipo }}"
                                                            data-numprj="{{ $value->numprj }}"
                                                            data-codfpj="{{ $value->codfpj }}"
                                                            data-despro="{{ $value->usu_descop }}"
                                                            data-proale="{{ $value->usu_proale }}"
                                                            data-coddep="{{ $value->coddep }}"
                                                            data-codpin="{{ $value->codpin }}"
                                                            data-target="#conferencia_modal"
                                                        >
                                                            <b>Conferir Item</b>
                                                            <b id="b-display" style="color: #1f2d3d; display:none"> - {{$value->numocp}}{{ sprintf('%2d', $value->seqipo) }}</b>
                                                            @if($value->codpin <> ' ')
                                                                <b style="color: #df2a2a">01010{{$value->numocp}}{{ sprintf('%04d', $value->seqipo) }}</b>
                                                            @else
                                                                <b style="color: #ffc107">01010{{$value->numocp}}{{ sprintf('%04d', $value->seqipo) }}</b>
                                                            @endif
                                                        </button>
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
        </section>

        <!-- Modal Recebimento-->
        <div class="modal fade" id="conferencia_modal" tabindex="-1" aria-labelledby="exampleModalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">

                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">
                            Receber Material
                        </h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <!-- Fim Modal Header -->

                    <!-- Modal Body -->
                    <div class="modal-body">
                        <div class="form-group">
                            {{Form::open(['route' => 'conferencia.store'])}}

                            {{Form::hidden('codPro', 'codPro', ['id' => 'codPro'])}}
                            {{Form::hidden('numNfc', 'numNfc', ['id' => 'numNfc'])}}
                            {{Form::hidden('numOcp', 'numOcp', ['id' => 'numOcp'])}}
                            {{Form::hidden('seqIpo', 'seqIpo', ['id' => 'seqIpo'])}}
                            {{Form::hidden('seqIpc', 'seqIpc', ['id' => 'seqIpc'])}}
                            {{Form::hidden('chvNel', 'chvNel', ['id' => 'chvNel'])}}
                            {{Form::hidden('numPrj', 'numPrj', ['id' => 'numPrj'])}}
                            {{Form::hidden('codFpj', 'codFpj', ['id' => 'codFpj'])}}
                            {{Form::hidden('codFor', 'codFor', ['id' => 'codFor'])}}

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{
                                        Form::number('qtdRec', null,  ['class' =>  'form-control'. ($errors->has('qtdRec')? ' is-invalid':NULL),
                                                                        'placeholder' => 'Quantidade',
                                                                        'required' => 'required'
                                                                    ]
                                                    )
                                    }}
                                </div>

                                @error('qtdRec')
                                <div class="alert alert-danger">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-12">
                                <div class="form-group">
                                    {{
                                        Form::text('codPal', null,  ['class' =>  'form-control'. ($errors->has('codPal')? ' is-invalid':NULL),
                                                                        'placeholder' => 'Pallet',
                                                                        'required' => 'required'
                                                                ]
                                                )
                                    }}
                                </div>

                                @error('codPal')
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
                        <button type="submit"
                                title="Imprimir OC"
                                data-toggle="modal"
                                data-target="#conferencia_etiqueta"
                                class="btn btn-success">
                            <i class="fa fa-print"></i>
                        </button>

                        <button type="submit"
                                title="Criar Pallet"
                                data-toggle="modal"
                                data-target="#conferencia_pallet"
                                class="btn btn-secondary">
                            <i class="fa fa-cubes"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <!-- Fim Modal Recebimento -->

        <!-- Modal Ferramentas-->
        @include('models.createPallet')


        <div class="modal fade" id="conferencia_etiqueta" tabindex="-1" aria-labelledby="conferencia_etiqueta"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <!-- Modal Header -->
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Ferramentas</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <section class="tables" style="padding: 5px !important;">
                        <div class="card">
                            <div class="card-header">Impressão de Etiquetas</div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <input type="number" id="txtQtdEti" required="required" class="form-control"
                                           placeholder="Qtd.Etiquetas"></input>
                                </div>
                                <div class="mb-3">
                                    <input type="number" id="txtQtdImp" required="required" class="form-control"
                                           placeholder="Qtd.Impressões"></input>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="button" class="btn btn-warning btnPrinter">Imprimir etiqueta
                                </button>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
        <!-- Fim Modal Ferramentas -->
    </section>

    <script type="module">
        $(document).ready(function () {

            $('.itemModal').on('click', function () {
                const numNfc = $(this).data('id');
                const codPro = $(this).data('codpro');
                const seqIpo = $(this).data('seqipo');
                const numOcp = $(this).data('numocp');
                const seqIpc = $(this).data('seqipo');
                const numPrj = $(this).data('numprj');
                const codFpj = $(this).data('codfpj');
                const chvNel = $(this).data('chvnel');
                const desPro = $(this).data('despro');
                const desAle = $(this).data('proale');
                const codDep = $(this).data('coddep');
                const codFor = $(this).data('codfor');

                document.getElementById('codPro').value = codPro;
                document.getElementById('numNfc').value = numNfc;
                document.getElementById('seqIpo').value = seqIpo;
                document.getElementById('numOcp').value = numOcp;
                document.getElementById('seqIpc').value = seqIpc;
                document.getElementById('numPrj').value = numPrj;
                document.getElementById('codFpj').value = codFpj;
                document.getElementById('chvNel').value = chvNel;
                document.getElementById('codFor').value = codFor;

                document.getElementById('txtNumPrj').value = numPrj;
                document.getElementById('txtCodFpj').value = codFpj;

                document.getElementsByClassName('modal-title')[0].innerHTML = 'Projeto ' + numPrj;

            });

            $('.btnPrinter').on('click', function () {
                const qtdEti = $('#txtQtdEti').val();
                const qtdImp = $('#txtQtdImp').val();
                const numOcp = document.getElementById('numOcp').value;
                const seqIpo = document.getElementById('seqIpo').value;

                $.ajax({
                    url: "{{ route('conferencia.print_tag') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'qtdEti': qtdEti,
                        'qtdImp': qtdImp,
                        'numOcp': numOcp,
                        'seqIpo': seqIpo
                    },
                    dataType: 'json',
                    async: true,
                    success: function (point) {
                        if (point.code == 500) {
                            mdtoast(
                                point.msg, {
                                    type: 'warning',
                                    duration: 2000
                                });

                            setTimeout(() => window.location.reload(), 2000);
                        } else if (point.code == 404) {
                            mdtoast(
                                point.msg, {
                                    type: 'alert',
                                    duration: 2000
                                });

                            setTimeout(() => window.location.reload(), 2000);
                        } else {
                            mdtoast(
                                point.msg, {
                                    type: 'success',
                                    dutarion: 2000
                                });

                            setTimeout(() => window.location.reload(), 2000);
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var error = jqXHR.responseText;
                        var content = error.content;
                        console.log(error);
                    }
                });
            });

            $('#table_itens').DataTable({
                searching: true,
                paginate: true,
                select: {
                    info: false
                },
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
        });
    </script>
    <script>

    </script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
