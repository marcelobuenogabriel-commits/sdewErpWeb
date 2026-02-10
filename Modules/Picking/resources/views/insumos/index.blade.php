@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Picking - Insumos'])

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Picking - Insumos</b>
            </div>
            <div class="card-body">
                <form data-action="{{ route('insumos.store') }}" method="POST" id="add-user-form">
                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" id="txtQuantidade" placeholder="Quantidade">
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <input type="text" class="form-control" id="txtEtiqueta" placeholder="Etiqueta"
                                   onblur="getEtiqueta()">
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer">

            </div>
        </div>
    </section>

    <script>
        function getEtiqueta() {
            let codPro;
            let qtdMov;

            codPro = parseInt(document.getElementById('txtEtiqueta').value);
            qtdMov = document.getElementById('txtQuantidade').value;

            $.ajax({
                url: "{{ route('insumos.store') }}",
                type: 'POST',
                data: {
                    '_token': '{{csrf_token()}}',
                    'codPro': codPro,
                    'qtdMov': qtdMov
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
