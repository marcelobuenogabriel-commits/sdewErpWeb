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
                                    <form id="form-filtrar-empresa" method="GET" action="{{ route('kpi.headcount') }}">
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
                                        @php
                                            $centros = $headcount['centroCusto'] ?? [];
                                            $totaisPorPeriodo = $headcount['totaisPorPeriodo'] ?? [];
                                            $periodos = array_keys($totaisPorPeriodo);
                                        @endphp
                                        <table id="" class="table table-striped table-hover table-bordered">
                                            <thead>
                                                <tr>
                                                    <th style="width: 350px;">Centro de Custo</th>
                                                    @foreach($headcount['totaisPorPeriodo'] as $mes => $totais)
                                                        @php if (array_key_last($headcount['totaisPorPeriodo']) === $mes) : @endphp
                                                            <th class="text-center" style="background-color: #dcdf31ff;">{{ $mes }}</th>
                                                        @else
                                                            <th class="text-center">{{ $mes }}</th>
                                                        @endif
                                                    @endforeach

                                                    <th></th>
                                                    <th>Solicitações de Vagas</th>
                                                    <th class="text-center" style="width: 200px;">Desligados -</th>
                                                    <th class="text-center" style="width: 200px;">Contratados +</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                               @foreach($centros as $cc)
                                                    <tr>
                                                        <td>{{ $cc }}</td>
                                                        @foreach($periodos as $period)
                                                            @php if (array_key_last($headcount['totaisPorPeriodo']) == $period) : @endphp
                                                                <td class="text-center" style="background-color: #cecf86ff;"> {{ $totaisPorPeriodo[$period][$cc] ?? 0 }}</td>
                                                            @else
                                                                <td class="text-center">
                                                                    {{ $totaisPorPeriodo[$period][$cc] ?? 0 }}
                                                                </td>
                                                            @endif
                                                        @endforeach

                                                        <td></td>

                                                        @php $count = 0; @endphp

                                                        @foreach($headcount['vagasAbertas'] as $codEmp => $data)
                                                          
                                                            @php $centrocusto = (int) preg_replace('/.*\D(\d+)$/', '$1', $cc); @endphp

                                                            @if(isset($data['cc_counts'][$centrocusto]))
                                                                <td class="text-center">
                                                                    {{ $data['cc_counts'][$centrocusto]}}
                                                                </td>
                                                                @php $count = 1; @endphp
                                                            @elseif($count != 1 && ($codEmp == $headcount['empresaSelecionada'] || $codEmp == 2))
                                                                <td class="text-center">0</td>
                                                                @php $count = 0; @endphp
                                                            @endif
                                                        @endforeach
                                                        
                                                        
                                                        @foreach($headcount['totaisDesligados'] as $ccDesligados => $value)
                                                
                                                            @if($ccDesligados == $cc)
                                                                <td class="text-center">
                                                                    {{ $value ?? 0 }}
                                                                </td>
                                                            @endif
                                                        @endforeach

                                                        @foreach($headcount['totaisContratados'] as $ccContratados => $value)
                                                        
                                                            @if($ccContratados == $cc)
                                                                <td class="text-center">
                                                                    {{ $value ?? 0 }}
                                                                </td>
                                                            @endif
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>

                                            <tfoot class="font-weight-bold">
                                                <tr>
                                                    <td>Total</td>

                                                    @foreach($periodos as $period)
                                                        @php $sum = array_sum($totaisPorPeriodo[$period] ?? []); @endphp

                                                        @php if ($period === $mes) : @endphp
                                                            <td class="text-center" style="background-color: #dcdf31ff;">{{ $sum }}</td>
                                                        @else
                                                            <td class="text-center">{{ $sum }}</td>
                                                        @endif
                                                    @endforeach

                                                    <td></td>

                                                    <td class="text-center">{{ $headcount['totalVagas'] ?? 0 }}</td>

                                                    <td class="text-center">
                                                        {{ array_sum($headcount['totaisDesligados'] ?? []) }}
                                                    </td>

                                                    <td class="text-center">
                                                        {{ array_sum($headcount['totaisContratados'] ?? []) }}
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <code>{{$headcount['logError']}}</code>
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
