@extends('adminlte::page')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6">
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item">
                                    <a href="{{route('home')}}">
                                        <i class="fa fa-dashboard"></i>Home
                                    </a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Picking - Dispatch</b>
            </div>
            <div class="card-body">

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <b>Movimentação -
                                <strong>{{ \Illuminate\Support\Facades\Session::get('idmov') }}</strong></b>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <form data-action="{{ route('picking.store') }}" method="POST" id="add-user-form">
                            <div class="form-group">
                                <input type="text" class="form-control" id="txtEtiqueta"
                                       placeholder="Etiqueta"
                                       onblur="getEtiqueta()">
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12 mb-2">
                        <div class="form-group">
                            <a type="submit" href="{{ url('dispatch/5850') }}" class="btn btn-secondary">
                                Alterar Movimentação
                            </a>
                        </div>
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
            let codMov;

            codSep = parseInt(document.getElementById('txtEtiqueta').value);
            ccuIni = 5850;
            codMov = {{\Illuminate\Support\Facades\Session::get('idmov')}};

            $.ajax({
                url: "{{ route('dispatch.store_dispatch') }}",
                type: 'POST',
                data: {
                    '_token': '{{csrf_token()}}',
                    'ccuIni': ccuIni,
                    'codSep': codSep,
                    'codMov': codMov
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
