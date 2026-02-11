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
                                     <a href="{{route('invoice.create', ['pedidos'])}}">
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
                                                <th>Ações</th>
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
                                                    <td>
                                                        @include('partials.button_edit',[
                                                       $route = 'invoice.edit',
                                                       $class = 'invoice',
                                                       $object_id = $pedido->usu_codinv
                                                   ])
                                                        <button type="button"
                                                                title="Imprimir Invoice"
                                                                class="btn btn-dark itemModal"
                                                                data-toggle="modal"
                                                                data-codinv="{{ $pedido->usu_codinv }}"
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
                                <div class="mb-3">
                                    <label class="form-label">E-mail Responsável</label>
                                    <input type="email" name="txtEmail" id="txtEmail" required="required"
                                              class="form-control">
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

                document.getElementById('codInv').value = codInv;
            });

        });
    </script>
    <script>
        function printInvoice() {
            codInv = document.getElementById('codInv').value;
            email = document.getElementById('txtEmail').value;
            emailKnapp = document.getElementById('txtEmailKnapp').value;

            if(emailKnapp.indexOf('knapp') != -1) {
                $.ajax({
                    url: "{{ route('emite-invoice') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'codInv': codInv,
                        'email': email,
                        'emailKnapp': emailKnapp,
                        'tipInv': 2
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
            } else {
                alert('E-mail do destinatário deve ser um e-mail KNAPP!');
            }
        }
    </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@endsection
