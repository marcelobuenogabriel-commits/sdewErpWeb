@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Pré-Fatura'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">

        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-close">
                            </div>
                            <div class="card-header d-flex align-items-center">
                                <span class="col-md-10">
                                    <h3 class="h4">Pré-Faturas disponíveis</h3>
                                </span>
                                <span class="col-md-2 text-right">
                                    <a href="{{route('recebimento.prefatura.impressao')}}" class="btn btn-secondary">
                                        <i class="fas fa-print"></i>
                                    </a>
                                </span>
                            </div>
                            <div class="card-body">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <table id="table_pre_fatura" class="table table-striped">
                                            <thead>
                                            <tr>
                                                <th>Pré-Faturas</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($prefaturas as $prefatura)
                                                    <tr>
                                                        <td>
                                                            <a
                                                                type="button"
                                                                class="btn btn-warning col-md-12 itemModal"
                                                                href="{{route('recebimento.prefatura.show', ['numane' => $prefatura->numane, 'numpfa' => $prefatura->numpfa])}}">
                                                                <b>{{$prefatura->numane}}-{{$prefatura->numpfa}}</b>
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script type="module">
            $(document).ready(function(){
                $('#table_pre_fatura').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.25/i18n/Portuguese-Brasil.json"
                    },
                    'order': [[0, 'desc']],
                });
            });
        </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
