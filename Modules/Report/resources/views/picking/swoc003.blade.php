@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Report'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Imprimir Movimentação</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="txtMovimentacao">Informe o Código da Movimentação</label>
                            <input type="number" class="form-control" id="txtMovimentacao" autocomplete="off">
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
            let numMov;

            numMov = parseInt(document.getElementById('txtMovimentacao').value);

            if(validField()) {
                $.ajax({
                    url: "{{ route('swoc003') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'numMov': numMov,
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
