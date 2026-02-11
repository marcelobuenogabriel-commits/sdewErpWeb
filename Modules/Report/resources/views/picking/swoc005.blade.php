@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Report'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Imprimir Etiqueta Sequência de Procedimento</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="seqPro">Número do Procedimento</label>
                            <input type="number" class="form-control" id="seqPro" autocomplete="off">
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
            let seqPro;

            seqPro = parseInt(document.getElementById('seqPro').value);

            if(validField()) {
                $.ajax({
                    url: "{{ route('swoc005') }}",
                    type: 'POST',
                    data: {
                        '_token': '{{csrf_token()}}',
                        'seqPro': seqPro
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
