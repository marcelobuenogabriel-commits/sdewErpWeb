@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',
                ['route' => 'recebimento.prefatura',
                 'class' => 'Pré-faturas',
                 'variable' => 'Produtos'])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span class="col-md-10">
                            <h3 class="card-title">Produtos da Pré-fatura <b>{{$numane}}-{{$numpfa}}</b></h3>
                        </span>
                    </div>
                    <div class="card-body">
                        <table id="table_pre_fatura_produtos" class="table table-hover">
                            <thead>
                            <tr>
                                <th>SKU - End - Qtd.</th>
                                <th></th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($produtos as $produto)
                                    <tr>
                                        <td>
                                            {{$produto->codpro}} - {{$produto->codend}} - {{number_format($produto->qtdppf, 2)}}
                                        </td>
                                        <td>
                                            <input type="checkbox" id="select_{{$produto->codpro}}" class="product-checkbox" value="{{$produto->codpro}}">
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="openModalBtn" class="btn btn-secondary">
                            Gerar Embalagens  (<span id="selectedCount">0</span>)
                        </button>
                    </div>
                </div>
            </section>

            <!-- Modal Gerar Embalagens -->
            <div class="modal fade" id="modalGerarEmbalagens" tabindex="-1" role="dialog" aria-labelledby="modalGerarEmbalagensLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalGerarEmbalagensLabel">Gerar Embalagens</h5>
                        </div>
                        <div class="modal-body">
                            {{Form::model($numane, ['route' => ['recebimento.prefatura.gerar_embalagens'], 'method' => 'POST'])}}
                            <div class="form-group">
                                {{Form::hidden('numane', $numane)}}
                                {{Form::hidden('numpfa', $numpfa)}}
                                {{Form::hidden('selected_ids', '', ['id' => 'selected_ids'])}}

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{Form::select('codemb',
                                            [
                                                '' => 'Selecione o Tipo de Embalagem',
                                                '1' => 'Caixa(s)',
                                                '2' => 'Pacotes(s)',
                                                '5' => 'Outro(s)',
                                                '10' => 'Peça(s)',
                                                '12' => 'Unidade(es)',
                                                '13' => 'Volume(s)',
                                                '15' => 'Pallet(s)',
                                            ],
                                            isset($invoice->codemb) ? $invoice->codemb : null,
                                            ['id' => 'codemb', 'class' => 'form-control', 'required' => 'required'])
                                        }}
                                    </div>

                                    @error('codemb')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{
                                            Form::text('obsemb', null,  ['class' =>  'form-control'. ($errors->has('obsemb')? ' is-invalid':NULL),
                                                                            'placeholder' => 'Dimensões da Embalagem',
                                                                            'required' => 'required'
                                                                        ]
                                                        )
                                        }}
                                    </div>

                                    @error('obsemb')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{
                                            Form::number('pesbru', null,  ['class' =>  'form-control'. ($errors->has('pesbru')? ' is-invalid':NULL),
                                                                            'placeholder' => 'Peso Bruto',
                                                                            'required' => 'required',
                                                                            'step' => '0.001'
                                                                        ]
                                                        )
                                        }}
                                    </div>

                                    @error('pesbru')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{
                                            Form::number('pesliq', null,  ['class' =>  'form-control'. ($errors->has('pesliq')? ' is-invalid':NULL),
                                                                            'placeholder' => 'Peso Líquido',
                                                                            'required' => 'required',
                                                                            'step' => '0.001'
                                                                        ]
                                                        )
                                        }}
                                    </div>

                                    @error('pesliq')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <div class="form-group">
                                        {{
                                            Form::number('qtdpfa', null,  ['class' =>  'form-control'. ($errors->has('qtdpfa')? ' is-invalid':NULL),
                                                                            'placeholder' => 'Quantidade',
                                                                            'id' => 'qtdpfa',
                                                                        ]
                                                        )
                                        }}
                                    </div>

                                    @error('qtdpfa')
                                    <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror
                                </div>

                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-success">Confirmar</button>
                        </div>
                        {{Form::close()}}
                    </div>
                </div>
            </div>
            <!-- Fim Modal Gerar Embalagens -->

            <script type="module">
                $(document).ready(function(){
                    $('#table_pre_fatura_produtos').DataTable({
                        "language": {
                            "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                        },
                        "order": [[1, "asc"]],
                        "paging":   false,
                    });

                    $(document).on('change', '.product-checkbox', updateSelectedCount);

                    $('#openModalBtn').on('click', function(e) {
                        const count = updateSelectedCount();
                        if (!count) return alert('Selecione ao menos um produto.');

                        // cria/atualiza hidden com IDs selecionados
                        const ids = $('.product-checkbox:checked').map((i, el) => $(el).val()).get();
                        let $hidden = $('#modalGerarEmbalagens').find('input[name="selected_ids"]');
                        if (!$hidden.length) {
                        $hidden = $('<input>', { type: 'hidden', name: 'selected_ids' }).appendTo($('#modalGerarEmbalagens').find('form').first() || $('#modalGerarEmbalagens'));
                        }
                        $hidden.val(ids.join(','));

                        // show/hide qtdPfa conforme contagem
                        if (count > 1) {
                            $('#qtdpfa').prop('hidden', true);
                        } else {
                            $('#qtdpfa').prop('hidden', false);
                        }

                        $('#selectedCount').text(count);
                        $('#modalGerarEmbalagens').modal('show');
                    });

                    // Se o usuário alterar seleção enquanto o modal estiver aberto, atualiza hidden e qtdPfa
                    $(document).on('change', '.product-checkbox', function() {
                        if ($('#modalGerarEmbalagens').hasClass('show')) {
                        const ids = $('.product-checkbox:checked').map((i, el) => $(el).val()).get();
                        $('#modalGerarEmbalagens').find('input[name="selected_ids"]').val(ids.join(','));
                        const count = ids.length;
                        $('#qtdpfa').prop('hidden', count > 1);
                        $('#selectedCount').text(count);
                        }
                    });

                    function updateSelectedCount() {
                        const count = $('.product-checkbox:checked').length;
                        $('#selectedCount').text(count);
                        $('#openModalBtn').prop('disabled', count === 0);
                        return count;
                    }
                });
            </script>
        </div>
    </div>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
