@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',
                ['route' => 'inv-contratos',
                 'class' => 'Invoices - Contratos',
                 'variable' => 'Nova invoice'])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informações da Invoice de Contrato</h3>
                        <div class="card-tools"></div>
                    </div>
                    <div class="card-body">
                        @include('form._form_errors')
                        {{Form::open(['route' => ['invoice.store', 'contratos'], 'onsubmit' => 'return validField()'])}}
                        @include('financeiro::invoice.contratos._form_contratos')
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
        function selectContratos() {
            let codEmp;

            codEmp = $('#codEmp').find(":selected")[0].value;

            $.ajax({
                url: "{{route('consulta-contratos')}}",
                async: true,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codEmp': codEmp
                },
                success: function (point) {
                    var options = '';
                    options += '<option value="">Selecione o Contrato</option>';
                    for (var i = 0; i < point.length; i++) {
                        options += '<option value="' + point[i].NUMCTR + '">' + point[i].NUMCTR + ' - ' + point[i].NOMCLI + '</option>';
                    }
                    $("#numCtr").html(options);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    var error = jqXHR.responseText;
                }
            });
        }

        function selectContrato() {
            let numCtr;
            let codEmp;

            numCtr = $('#numCtr').find(":selected")[0].value;
            codEmp = $('#codEmp').find(":selected")[0].value;

            $.ajax({
                url: "{{route('consulta-contrato')}}",
                async: true,
                type: 'POST',
                dataType: 'json',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codEmp': codEmp,
                    'numCtr': numCtr
                },
                success: function (point) {
                    if(point[0].CODMOE != '01') {
                        $('#codMoeda').attr('hidden', true);
                        $('#cotMoeda').attr('hidden', true);
                        $('#datMoeda').attr('hidden', true);

                        $('#codMoe').attr('hidden', true);
                        $('#cotMoe').attr('hidden', true);
                    } else {
                        $('#codMoeda').attr('hidden', false);
                        $('#cotMoeda').attr('hidden', false);
                        $('#datMoeda').attr('hidden', false);

                        $('#codMoe').attr('hidden', false);
                        $('#cotMoe').attr('hidden', false);
                    }

                    $('#desPro')[0].value = point[0].CPLCVS;
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
