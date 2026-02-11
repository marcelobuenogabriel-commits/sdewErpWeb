@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Acompanhamento'])

    @include('partials.alerts')

    <section class="tables">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-close">
                        </div>
                        <div class="card-header d-flex flex-column align-items-start">
                            <h4>{{$title}}</h4> 
                            <br>
                            <h6>{{$description}}</h6>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <table id="table_id" class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th>Fornecedor</th>
                                            <th>Quantidade</th>
                                            <th>Data Emissão</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @foreach($notasSDE as $nota)
                                            <tr>
                                                <td>{{ $nota->NOMFOR }}</td>
                                                <td>{{ round($nota->QTDREC, 2, PHP_ROUND_HALF_UP) }}</td>
                                                <td data-order="{{ $nota->DATEMI }}">{{ date('d-m-Y', strtotime($nota->DATEMI)) }}</td>
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
        document.addEventListener('DOMContentLoaded', function() {
            $('#table_id').DataTable({
                searching: true,
                pageLength: 25,
                info: false,
                select: {
                    info: false
                },
                order: [[3, 'desc']],
                language: {
                    paginate: {
                        next: 'Próximo',
                        previous: 'Anterior'
                    },
                    search: 'Pesquisar',
                    lengthMenu: '',
                    info: ''
                }
            });
        });
    </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop