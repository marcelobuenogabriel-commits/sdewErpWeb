@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',
                ['route' => 'importacao.index',
                 'class' => 'Invoices - Importação',
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
                        {{Form::open(['route' => ['importacao.store', 'pedidos'], 'id' => 'form_pedidos'])}}

                            @include('importacao::_form')

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

    </script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
