
@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show', [
        'route'    => 'importacao.index',
        'class'    => 'Invoices - Importação',
        'variable' => 'Produtos da Invoice'
    ])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            Produtos da Invoice <b>{{ $invoice->codInv }}</b>
                        </h3>

                        <div class="card-tools d-flex align-items-center">
                            <div class="row">
                                <div class="col-md-12 d-flex gap-2 align-items-center">

                                    {{-- Selecionar todos - agora é um checkbox real --}}
                                    <div class="card-tools d-flex align-items-center mr-3">
                                        <input id="masterCheckbox" type="checkbox" class="mr-1" />
                                        <label for="masterCheckbox" class="mb-0">
                                            Selecionar todos (<span id="masterCount">0</span>)
                                        </label>
                                    </div>

                                    {{-- Botão Importar --}}
                                    <div class="card-tools d-flex align-items-center">
                                        <input type="file" id="import_document" name="import_document"
                                               accept=".pdf,.doc,.docx,.xls,.xlsx" style="display:none">
                                        <button type="button" id="btnImport" class="btn btn-primary btn-sm mr-2"
                                                title="Importar Documentos">
                                            <i class="fas fa-file-upload"></i> Importar
                                        </button>
                                        <small id="import_filename" class="text-muted"></small>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        {{ Form::open(['route' => ['importacao.updateprodutos', $invoice->codInv], 'method' => 'PUT']) }}
                        <input type="hidden" name="codInv" value="{{ $invoice->codInv }}">
                        <input type="hidden" name="codPed" value="{{ $invoice->numPed }}">
                        <input type="hidden" name="codEmp" value="{{ $invoice->codEmp }}">

                        <table id="table_invoice_importacao" class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Check</th>
                                    <th>País</th>
                                    <th>Nº Invoice</th>
                                    <th>Nº Pedido</th>
                                    <th>Código Produto</th>
                                    <th>Sequência</th>
                                    <th>Descrição</th>
                                    <th>Valor Unitário</th>
                                    <th>Quantidade Pedido</th>
                                    <th>Quantidade Invoice</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($produtos as $produto)
                                    <tr>
                                        <td>
                                            @php
                                                $checked = in_array($produto->CODPRO, $produtos->pluck('USU_CODITE')->toArray())
                                                           && $invoice->codInv == $produto->USU_CODINV;
                                            @endphp
                                            <input
                                                type="checkbox"
                                                class="produto_selecionado"
                                                name="produto_selecionado[{{ $produto->SEQIPD }}]"
                                                value="{{ $produto->SEQIPD }}"
                                                {{ $checked ? 'checked' : '' }}
                                            >
                                        </td>

                                        <td>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="codpai[{{ $produto->SEQIPD }}]"
                                                value="{{ $produto->USU_CODPAI }}"
                                                maxlength="2"
                                            >
                                        </td>

                                        <td>
                                            @if($invoice->codInv == $produto->USU_CODINV)
                                                {{ $produto->USU_CODINV }}
                                            @endif
                                        </td>

                                        <td>{{ $produto->NUMPED }}</td>
                                        <td>{{ $produto->CODPRO }}</td>
                                        <td class="seqipd">{{ $produto->SEQIPD }}</td>
                                        <td>{{ $produto->CPLIPD }}</td>
                                        <td>{{ number_format($produto->PREUNI, 2, ',', '.') }}</td>

                                        <td>
                                            @if($produto->QTDABE > $produto->USU_QTDPED)
                                                <input
                                                    type="number"
                                                    class="form-control"
                                                    name="qtdped[{{ $produto->SEQIPD }}]"
                                                    value="{{ $produto->QTDABE - $produto->USU_QTDPED }}"
                                                    min="1"
                                                    step="any"
                                                    onblur="validateQuantidade(this, {{ $produto->QTDABE }})"
                                                >
                                            @else
                                                {{ $produto->USU_QTDPED ? number_format($produto->USU_QTDPED, 2, ',', '.') : 0 }}
                                            @endif
                                        </td>

                                        <td>
                                            {{ $produto->USU_QTDPED ? number_format($produto->USU_QTDPED, 2, ',', '.') : 0 }}
                                        </td>

                                        <td>
                                            <button type="button" class="btn btn-danger btn-sm" title="Remover Produto">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-secondary">Atualizar Produtos</button>
                        {{ Form::close() }}
                    </div>
                </div>
            </section>
        </div>
    </div>

    {{-- SweetAlert precisa estar incluído no layout, ex.: https://unpkg.com/sweetalert/dist/sweetalert.min.js</script> --}}

    <script>
        function validateQuantidade(input, qtdabe) {
            const maxQuantidade = Number(qtdabe) || 0;
            let valor = parseFloat(input.value);

            if (Number.isNaN(valor) || valor <= 0) {
                swal({
                    title: 'Quantidade inválida',
                    text: 'Informe um número maior que zero.',
                    icon: 'error',
                    button: 'OK',
                });
                input.value = 1;
                return;
            }

            if (valor > maxQuantidade) {
                swal({
                    title: 'Quantidade Inválida',
                    text: 'A quantidade não pode ser maior que a quantidade aberta no pedido.',
                    icon: 'error',
                    button: 'OK',
                });
                input.value = maxQuantidade;
            }
        }

        // Se desejar forçar país como uppercase de 2 letras:
        document.addEventListener('blur', (e) => {
            if (e.target && e.target.name && e.target.name.startsWith('codpai[')) {
                e.target.value = (e.target.value || '').toUpperCase().substring(0, 2);
            }
        }, true);
    </script>

    <script>
        const master = document.getElementById('masterCheckbox');
        const masterCount = document.getElementById('masterCount');

        master.checked = false;
        master.indeterminate = false;

        function getItens() {
            return document.querySelectorAll('input.produto_selecionado[type="checkbox"]');
        }

        function contarMarcados() {
            return document.querySelectorAll('input.produto_selecionado[type="checkbox"]:checked').length;
        }

        function syncMaster() {
            const itens = getItens();
            const total = itens.length;
            const marcados = contarMarcados();

            master.checked = total > 0 && marcados === total;
            master.indeterminate = marcados > 0 && marcados < total;

            masterCount.textContent = marcados.toString();
        }

        function alternarTodosDoMaster() {
            const itens = getItens();
            itens.forEach(cb => {
            if (!cb.disabled) cb.checked = master.checked;
            });
            master.indeterminate = false;
            
            syncMaster();
        }

        master.addEventListener('change', alternarTodosDoMaster);

        document.addEventListener('change', (e) => {
            if (e.target.matches('input.produto_selecionado[type="checkbox"]')) {
            syncMaster();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            master.checked = false;
            master.indeterminate = false;
            masterCount.textContent = '0';
            syncMaster();
        });
    </script>

    <script>
        // Importar documento
        document.getElementById('btnImport').addEventListener('click', function () {
            document.getElementById('import_document').click();
        });

        document.getElementById('import_document').addEventListener('change', function (e) {
            const file = e.target.files[0];
            if (!file) return;

            document.getElementById('import_filename').textContent = file.name;

            const formData = new FormData();
            formData.append('import_document', file);
            formData.append('codInv', '{{ $invoice->codInv }}');
            formData.append('_token', '{{ csrf_token() }}');

            $.ajax({
                url: '{{ route("importacao-documento") }}',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (res) {
                    swal({
                        title: 'Importação realizada',
                        text: res.message || 'Documento importado com sucesso.',
                        icon: 'success',
                        button: 'OK'
                    }).then(() => location.reload());
                },
                error: function (xhr) {
                    const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Erro ao importar documento.';
                    swal({ title: 'Erro', text: msg, icon: 'error', button: 'OK' });
                }
            });
        });

        // (Opcional) garantir CSRF em todas as requisições AJAX do jQuery
        // <meta name="csrf-token" content="{{ csrf_token() }}">
        $.ajaxSetup({
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        });
    </script>

    <script type="module">
        // Remoção via AJAX — usando SEQIPD (6ª coluna)
        $(document).ready(function () {

            $('#table_invoice_importacao').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                },
                "paging": false,
                "lengthChange": true,
                "searching": true,
                "order": [[1, "desc"]],
                "info": true,
                "autoWidth": false,
                "responsive": true,
            });
            
            $(document).on('click', '.btn-danger', function (e) {
                e.preventDefault();

                swal({
                    title: 'Tem certeza que deseja remover este produto da Invoice?',
                    icon: 'warning',
                    buttons: true,
                    dangerMode: true
                }).then((willDelete) => {
                    if (willDelete) {
                        const codInv = $('input[name="codInv"]').val();
                        // pega a 6ª coluna (Sequência / SEQIPD)
                        const seqipd = $(this).closest('tr').find('td:eq(5)').text().trim();

                        if (!seqipd) {
                            swal({
                                title: 'Não foi possível identificar o item (SEQIPD).',
                                icon: 'error',
                                button: 'OK'
                            });
                            return;
                        }

                        // Ajuste esta URL conforme sua rota espera (ex.: deleteprodutos/{seqipd})
                        const url = `/importacao/${codInv}/deleteprodutos/${seqipd}`;

                        $.ajax({
                            url: url,
                            type: 'DELETE',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function (response) {
                                swal({
                                    title: 'Produto removido com sucesso!',
                                    icon: 'success',
                                    button: 'OK',
                                    timer: 2000
                                }).then(() => {
                                    location.reload();
                                });
                            },
                            error: function (xhr) {
                                const msg = (xhr.responseJSON && xhr.responseJSON.message)
                                    ? xhr.responseJSON.message
                                    : 'Ocorreu um erro ao tentar remover o produto.';
                                swal({
                                    title: 'Erro ao remover o produto.',
                                    text: msg,
                                    icon: 'error',
                                    button: 'OK',
                                    timer: 3000
                                });
                            }
                        });
                    }
                });
            });
        });
    </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{ now()->format('Y') }}
@stop
