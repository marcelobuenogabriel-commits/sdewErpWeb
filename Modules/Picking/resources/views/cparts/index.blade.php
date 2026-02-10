@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Picking - C-Parts'])

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Picking - CParts</b>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <form data-action="{{ route('cparts.store') }}" method="POST" id="add-user-form">
                            <div class="form-group">
                                <input type="text" class="form-control" id="txtEtiqueta"
                                       placeholder="Etiqueta"
                                       onblur="getEtiqueta()">
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </section>

    <script>
        function getEtiqueta() {
            let codSep;
            let ccuIni;

            codSep = parseInt(document.getElementById('txtEtiqueta').value);
            ccuIni = 7600;

            $.ajax({
                url: "{{ route('cparts.store') }}",
                type: 'POST',
                data: {
                    '_token': '{{csrf_token()}}',
                    'ccuIni': ccuIni,
                    'codSep': codSep
                },
                dataType: 'json',
                async: true,
                success: function (point) {
                    if (point != "\"Processado com Sucesso.\"") {
                        mdtoast(
                            point.replace(/"/g, ''), {
                                type: 'warning',
                                duration: 5000
                            });

                        document.getElementById('txtEtiqueta').value = '';
                    } else {
                        mdtoast(
                            point.replace(/"/g, ''), {
                                type: 'success',
                                duration: 5000
                            });

                        document.getElementById('txtEtiqueta').value = '';
                    }
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
