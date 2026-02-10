@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Report'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Etiqueta x Ordem de Compra</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="numOcp">Informe a Ordem de Compra</label>
                            <input type="number" class="form-control" id="numOcp" autocomplete="off">
                            @include('partials.error')
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <input type="submit" class="btn btn-warning" onclick="relOcoc()" value="Imprimir"/>
            </div>
        </div>
    </section>

    <script>
        function relOcoc() {
            let numOcp;

            numOcp = parseInt(document.getElementById('numOcp').value);

            if(validField()) {
                $.ajax({
                    url: "{{ route('swoc001') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'numOcp': numOcp
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
