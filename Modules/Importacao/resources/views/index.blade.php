@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Invoices'])

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
                                <span class="col-md-10">
                                    <h4>{{$title}}</h4>
                                    <br>
                                    <h6>{{$description}}</h6>
                                </span>
                                <span class="col-md-2 text-right">
                                     <a href="{{route('importacao.create', ['pedidos'])}}">
                                        <button class="btn btn-secondary"
                                                type="button">
                                            Nova invoice
                                        </button>
                                    </a>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table id="table_id" class="table table-hover">
                                            <thead>
                                            <tr>
                                                <th>Empresa</th>
                                                <th>Número Invoice</th>
                                                <th>Pedido</th>
                                                <th>Cliente</th>
                                                <th>Versão</th>
                                                <th>Data</th>
                                                <th style="min-width: 200px;">Ações</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($pedidos as $pedido)
                                                <tr>
                                                    @switch($pedido->usu_codemp)
                                                        @case('1')
                                                            <td>KNAPP Sudamérica Logística e Automação Ltda</td>
                                                            @break
                                                        @case('2')
                                                            <td>MAIS Inteligência</td>
                                                            @break
                                                        @case('3')
                                                            <td>KNAPP Chile</td>
                                                            @break
                                                        @case('4')
                                                            <td>KNAPP México</td>
                                                            @break
                                                    @endswitch
                                                    <td>{{$pedido->usu_codinv}}</td>
                                                    <td>{{$pedido->numped}}</td>
                                                    <td>{{$pedido->nomcli}}</td>
                                                    <td>{{$pedido->usu_numver}}</td>
                                                    <td>{{ date('d/m/Y', strtotime($pedido->usu_datfec)) }}</td>
                                                    <td style="min-width: 150px;">

                                                        @include('partials.button_edit',[
                                                            $route = 'importacao.edit',
                                                            $class = 'importacao',
                                                            $object_id = $pedido->usu_codinv
                                                        ])

                                                        <a class="btn btn-secondary" href="{{route('importacao.produtos',['id' => $pedido->usu_codinv])}}" title="Editar">
                                                            <i class="fa fa-boxes"></i>
                                                        </a>

                                                        <a class="btn btn-info" href="{{route('importacao.endereco',['id' => $pedido->usu_codinv])}}" title="Endereço">
                                                            <i class="fa fa-address-card"></i>
                                                        </a>

                                                        <button type="button"
                                                                title="Imprimir Invoice"
                                                                class="btn btn-dark itemModal"
                                                                data-toggle="modal"
                                                                data-codinv="{{ $pedido->usu_codinv }}"
                                                                data-numver="{{ $pedido->usu_numver }}"
                                                                data-target="#informaEmail">
                                                            <i class="fa fa-print"></i>
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


            <!-- Modal Recusa-->
            <div class="modal fade" id="informaEmail" tabindex="-1" aria-labelledby="exampleModalLabel"
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
                            <div class="form-group">
                                <input hidden="hidden" id="codInv" class="codInv">
                                <input hidden="hidden" id="numVer" class="numVer">
                               
                                <div class="class mb-3">
                                    <label class="form-label">Country of Destination</label>
                                    <input type="text" name="aContDst" id="aContDst" class="form-control">
                                </div>

                                <div class="class mb-3">
                                    <label class="form-label">Country of Precedence</label>
                                    <input type="text" name="aContPre" id="aContPre" class="form-control">
                                </div>

                                <div class="class mb-3">
                                    <label class="form-label">Country of Acquisition</label>
                                    <input type="text" name="aContAqu" id="aContAqu" class="form-control">
                                </div>

                                <div class="class mb-3">
                                    <label class="form-label">Wooden Packing</label>
                                    <input type="text" name="aWood" id="aWood" class="form-control">
                                </div>

                                <div class="class mb-3">
                                    <label class="form-label">Mostrar NCM</label>
                                    <select id="nMostrarNcm" name="nMostrarNcm" class="form-control">
                                        <option value="1">Sim</option>
                                        <option value="0">Não</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">E-mail Destinatário (KNAPP)</label>
                                    <input type="email" name="txtEmailKnapp" id="txtEmailKnapp" required="required"
                                           class="form-control">
                                </div>
                                
                                <button type="submit" class="btn btn-warning" onclick="printInvoice()">Enviar</button>

                            </div>
                        </div>
                        <!-- Fim modal Body -->

                        <div class="modal-footer">

                        </div>
                    </div>
                </div>
            </div>

        </section>
    </section>
    <script type="module">
        $(document).ready(function () {
            $('#table_id').on('click', 'button', function(){
                const codInv = this.dataset.codinv;
                const numVer = this.dataset.numver;

                document.getElementById('codInv').value = codInv;
                document.getElementById('numVer').value = numVer;
            });

        });
    </script>

    <script>
        function printInvoice() {
            codInv = document.getElementById('codInv').value;
            emailKnapp = document.getElementById('txtEmailKnapp').value;
            aContDst = document.getElementById('aContDst').value;
            aContPre = document.getElementById('aContPre').value;
            aContAqu = document.getElementById('aContAqu').value;
            aWood = document.getElementById('aWood').value;
            nMostrarNcm = document.getElementById('nMostrarNcm').value;
            numVer = document.getElementById('numVer').value;

            $.ajax({
                url: "{{ route('importacao-emite-invoice') }}",
                type: 'POST',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codInv': codInv,
                    'emailKnapp': emailKnapp,
                    'aContDst': aContDst,
                    'aContPre': aContPre,
                    'aContAqu': aContAqu,
                    'aWood': aWood,
                    'nMostrarNcm': nMostrarNcm,
                    'numVer': numVer
                },
                dataType: 'json',
                async: true,
                success: function (point) {
                    window.location.reload();
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
@endsection
