@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',
                ['route' => 'inv-pedidos',
                 'class' => 'Invoices - Pedidos',
                 'variable' => 'Nova invoice'])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informações da Invoice de Pedido</h3>
                        <div class="card-tools"></div>
                    </div>
                    <div class="card-body">
                        @include('form._form_errors')
                        {{Form::open(['route' => ['invoice.store', 'pedidos'], 'onsubmit' => 'return validField()'])}}
                        @include('financeiro::invoice.pedidos._form_pedidos')
                        <button type="submit" class="btn btn-secondary">Salvar</button>
                        {{Form::close()}}
                    </div>
                    <div class="card-footer">

                    </div>
                </div>
            </section>
        </div>
    </div>

    <script>
        function selectPedidos() {
            let codEmp;

            codEmp = $('#codEmp').find(":selected")[0].value;

            $.ajax({
                url: "{{route('consulta-pedidos')}}",
                async: true,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codEmp': codEmp
                },
                success: function (point) {
                    var options = '';
                    options += '<option value="">Selecione o Pedido</option>';
                    for (var i = 0; i < point.length; i++) {
                        options += '<option value="' + point[i].NUMPED + '">' + point[i].NUMPED + ' - ' + point[i].NOMCLI + '</option>';
                    }
                    $("#codPed").html(options);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var error = jqXHR.responseText;
                }
            });
        }

        function selectPedido() {
            let codPed;
            let codEmp;

            codPed = $('#codPed').find(":selected")[0].value;
            codEmp = $('#codEmp').find(":selected")[0].value;

            $.ajax({
                url: "{{route('consulta-pedido')}}",
                async: true,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codEmp': codEmp,
                    'codPed': codPed
                },
                success: function (point) {
                    $('#vlrTot')[0].value = point[0].TOTIPD == null ? point[0].TOTISP : point[0].TOTIPD;
                    $('#vlrFat')[0].value = '';
                    $('#perFat')[0].value = '';

                    if(point[0].CODMOE != '01' && codEmp == '1') {
                        $('#codMoeda').attr('hidden', true);
                        $('#cotMoeda').attr('hidden', true);

                        $('#codMoe').attr('hidden', true);
                        $('#cotMoe').attr('hidden', true);
                    } else if(point[0].CODMOE != '08' && codEmp == '3') {
                        $('#codMoeda').attr('hidden', true);
                        $('#cotMoeda').attr('hidden', true);

                        $('#codMoe').attr('hidden', true);
                        $('#cotMoe').attr('hidden', true);
                    } else {
                        $('#codMoeda').attr('hidden', false);
                        $('#cotMoeda').attr('hidden', false);

                        $('#codMoe').attr('hidden', false);
                        $('#cotMoe').attr('hidden', false);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var error = jqXHR.responseText;
                }
            });
        }

        function converteValorPedido() {
            let perFat;
            let totPed;
            let vlrFat;

            totPed = $('#vlrTot')[0].value;
            perFat = $('#perFat')[0].value;

            vlrFat = (perFat / 100) * totPed;

            $('#vlrFat')[0].value = vlrFat.toFixed(2);

        }

    </script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
