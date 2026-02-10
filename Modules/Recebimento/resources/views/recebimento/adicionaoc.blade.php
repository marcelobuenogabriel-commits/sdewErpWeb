@extends('adminlte::page')

@section('content')

     @include('partials.breadcrumb_show',
                ['route' => 'recebimento.index',
                 'class' => 'Recebimento',
                 'variable' => 'OC x NFE'])

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
                                <!-- Table NFE-->
                                <div class="col-md-16">
                                    <div class="form-group">
                                        <table id="table_id" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th hidden="hidden">Chave NFE</th>
                                                <th>Nota Fiscal</th>
                                                <th>Sequência XML</th>
                                                <th>Código do Produto</th>
                                                <th>Descrição</th>
                                                <th>Quantidade</th>
                                                <th>Ordem de Compra</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($listaXml as $item)
                                                <tr>
                                                    <td hidden="hidden">{{ $item->chvnel }}</td>
                                                    <td id="numNfc">{{ $item->numnfc }}</td>
                                                    <td>{{ $item->seqipc }}</td>
                                                    <td>{{ $item->codpro }}</td>
                                                    <td>{{ $item->cplipc }}</td>
                                                    <td>{{ $item->qtdrec }}</td>
                                                    <td>
                                                        <select class="form-control" name="seqipo[]" id="seqipo">
                                                            @foreach($listaOcp as $itemOcp)
                                                                <option value="{{ $itemOcp->seqipo }}">
                                                                    {{ $itemOcp->codpro }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                        <button type="submit" class="btn btn-info" id="btnEnviar">Enviar</button>
                                    </div>
                                </div>
                                <!-- End Table -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
        
        <script type="module">
            $(document).ready(function() {
                $('#btnEnviar').click(function(e) {
                    e.preventDefault();

                    let itens = [];

                    // Percorre cada linha do tbody da tabela
                    $('#table_id tbody tr').each(function() {
                        let linha = $(this);

                        let item = {
                            chvnel: linha.find('td').eq(0).text().trim(),
                            numnfc: linha.find('td').eq(1).text().trim(),
                            seqipc: linha.find('td').eq(2).text().trim(),
                            seqipo: linha.find('select[name="seqipo[]"]').val()
                        };

                        itens.push(item);
                    });

                    $.ajax({
                        url: '{{ route("update_ocp") }}',
                        method: 'POST',
                        data: {
                            itens: itens,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.code == 200) {
                                mdtoast(
                                    response.msg, {
                                    type: 'success',
                                    duration: 5000,
                                    position: 'top left'
                                });
                                
                                setTimeout(function() {
                                    window.location.href = '{{ route("recebimento.index") }}';
                                }, 2000);
                            } else {
                                mdtoast(
                                    response.msg, {
                                    type: 'error',
                                    duration: 5000,
                                    position: 'top left'
                                });
                            }
                        },
                        error: function(xhr) {
                            // Handle error
                        }
                    });
                });
            });
        </script>
@endsection
