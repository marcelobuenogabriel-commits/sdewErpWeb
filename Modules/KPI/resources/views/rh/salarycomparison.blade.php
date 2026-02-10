@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Headcount'])

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
                                <span class="col-md-8">
                                    <h3><b>{{$title}}</b></h3> <h6>{{$description}}</h6>
                                </span>

                                <div class="col-md-4 d-flex justify-content-end">
                                    <form id="form-filtrar-empresa" method="GET" action="{{ route('kpi.salarycomparison') }}">
                                        <div class="input-group">
                                            <select name="empresa" id="select-empresa" class="form-control form-control-sm">
                                                <option value="">Todas as empresas</option>
                                                <option value="1" {{ request('empresa') == '1' ? 'selected' : '' }}>KNAPP Sistemas de Automação</option>
                                                <option value="2" {{ request('empresa') == '2' ? 'selected' : '' }}>MAIS Inteligência</option>
                                            </select>
                                            <div class="input-group-append">
                                                <button type="submit" class="btn btn-sm btn-primary">Filtrar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            </div>
                            <div class="card-body">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        
                                        <table id="table_id" class="table table-striped table-hover table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 350px;">Empresa</th>
                                                    <th style="width: 350px;">Colaborador</th>
                                                    <th class="text-center" style="width: 200px;">Salário Anterior</th>
                                                    <th class="text-center" style="width: 200px;">Salário Atual</th>
                                                    <th class="text-center">Percentual de Reajuste %</td>
                                                </tr>
                                            </thead>
                                                <tbody>
                                                    @foreach ($salarios as $item)
                                                        <tr>
                                                            <td>
                                                            @switch($item->NUMEMP)
                                                                @case(1)
                                                                    KNAPP Sistemas de Automação
                                                                    @break
                                                                @case(2)
                                                                    MAIS Inteligência
                                                                    @break
                                                                @default
                                                                    Desconhecida
                                                            @endswitch</td>
                                                            <td>{{ $item->NOMFUN }}</td>
                                                            <td class="text-center">{{ number_format($item->SalarioAnterior, 2, ',', '.') }}</td>
                                                            <td class="text-center">{{ number_format($item->SalarioAtual, 2, ',', '.') }}</td>
                                                            <td class="text-center">{{ number_format($item->PercentualReajuste, 2, ',', '.') }}</td>
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
    </section>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@endsection
