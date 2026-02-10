@extends('adminlte::page')

@section('content')

    <div class="row">
        <div class="col-md-12">
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-right">
                                <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-dashboard"></i>Home</a></li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <section class="tables">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <h2>{{ $idordem }} &nbsp; @if (session()->has('bench'))
                                        Bancada atual: {{ session('bench') }}</p>
                                    @else
                                        Nenhuma bancada selecionada.</p>
                                    @endif</h2>
                            </div>
                            <div class="card-body">
                                <b>Teste de Conexão com a API Siemens:</b> {{ $message }}
                                <hr/>
                                <h4>Informações da Ordem</h4>
                                <table class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>Nome</th>
                                        <th>Descrição</th>
                                        <th>Status da Ordem</th>
                                        <th>Quantidade</th>
                                        <th>Bloqueio?</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($infoOrder as $order)
                                        <tr>
                                            <td>{{$order->name}}</td>
                                            <td>{{$order->description}}</td>
                                            <td>{{$order->Status}}</td>
                                            <td>{{$order->workOrderQuantity}}</td>
                                            <td>{{$order->IsBlocked}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <hr/>
                                <h4>Testes Automáticos</h4>
                                @if($booleanTeste)
                                    <b>{!! $mensagemTestes !!}</b>
                                    <hr/>
                                    <h4>Enviar Movimentações</h4>
                                    @if($infoOrder == [])
                                        <b>Ordem não habilitada para realização do HandlingUnit ou não encontrada no OCX</b>
                                    @elseif($infoOrder[0]->Status == "Completed" && !$infoOrder[0]->IsBlocked)
                                        <form action="{{ route('movimentacao.salvarmovimentacao') }}" method="post">
                                            @csrf
                                            <div class="form-group col-md-12">
                                                <label for="codMov" class="control-label">Número Movimentação</label>
                                                <input class="form-control" name="codMov" type="text" id="codMov">
                                            </div>
                                            <!-- Campo oculto com o valor da ordem -->
                                            <input type="hidden" name="numOrp" value={{$idordem}}> <!-- Substitua '12345' pelo valor da ordem -->

                                            <button type="submit" class="btn btn-secondary mr-1">Enviar Movimentação</button>
                                        </form>
                                    @else
                                        <b>{!! $mensagemTestes !!}</b>
                                    @endif
                                @else
                                    <b>{!! $mensagemTestes !!}</b>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                </div>
        </section>
    </section>

    <!-- Modal -->
    @if($showPopup)
        <div class="modal fade" id="benchModal" tabindex="-1" role="dialog" aria-labelledby="benchModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="benchModalLabel">Selecione sua Bancada</h5>
                    </div>
                    <div class="modal-body">
                        <form id="benchForm" method="POST" action="{{ route('setBench') }}">
                            @csrf
                            <label for="bench">Escolha uma bancada:</label>
                            <select class="form-control mb-3" name="bench" id="bench" required>
                                <option value="">Selecione a Bancada</option>
                                @foreach($availableBenches as $bench)
                                    <option value="{{ $bench }}">{{ $bench }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-primary">Confirmar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
        <script>
        $(document).ready(function () {
                $('#benchModal').modal('show'); // Use '$' como jQuery novamente
            });
        </script>
    @endif

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
