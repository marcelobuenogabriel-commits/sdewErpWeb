@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Report'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Imprimir Etiqueta Estoque</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numPro">Nome do Projeto</label>
                            <input type="text" class="form-control" id="numPro" autocomplete="off">
                            @include('partials.error')
                        </div>

                        <div class="form-group">
                            <label for="codPro">Código do Produto</label>
                            <input type="text" class="form-control" id="codPro" autocomplete="off">
                            @include('partials.error')
                        </div>

                        <div class="form-group">
                            <label for="codDep">Depósito</label>
                            <input type="number" class="form-control" id="codDep" autocomplete="off">
                            @include('partials.error')
                        </div>

                        <div class="form-group">
                            <label for="qtdPct">Quantidade do Pacote</label>
                            <input type="number" class="form-control" id="qtdPct" autocomplete="off">
                            @include('partials.error')
                        </div>

                    </div>
                </div>
            </div>
            <div class="card-footer">
                <input type="submit" class="btn btn-warning" onclick="printer()" value="Imprimir"/>
            </div>
        </div>
    </section>

    <script>
        function printer() {
            let numPro;
            let codPro;
            let codDep;
            let qtdPct;

            numPro = parseInt(document.getElementById('numPro').value);
            codPro = parseInt(document.getElementById('codPro').value);
            codDep = parseInt(document.getElementById('codDep').value);
            qtdPct = parseInt(document.getElementById('qtdPct').value);

            if(validField()) {
                $.ajax({
                    url: "{{ route('swoc004') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'numPro': numPro,
                        'codPro': codPro,
                        'codDep': codDep,
                        'qtdPct': qtdPct,
                        'printer': "{{$printer}}"
                    },
                    dataType: 'json',
                    async: true,
                    success: function (point) {
                        window.location.reload();
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        var error = jqXHR.responseText;
                        var content = error.content;
                        console.log(content.message);
                        if (content.display_exceptions)
                            console.log(content.exception.xdebug_message);
                    }
                });
            }
        }
    </script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
