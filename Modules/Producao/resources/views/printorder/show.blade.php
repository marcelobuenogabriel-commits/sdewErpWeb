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
                                <li class="breadcrumb-item"><a href="{{route('home')}}"><i class="fa fa-dashboard"></i>Home</a>
                                </li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('partials.alerts')

    <div class="row">
        <!-- Coluna da esquerda: Hierarquia de ordens -->
        <div class="col-md-4">
            <h4>Ordens por Nível</h4>

            <ul>
                @foreach ($resultListOrder as $topsl => $ordens)
                    <li onclick="toggle(this)">📁 TOPSL: {{ $topsl }}
                        <ul class="hidden">
                            @foreach ($ordens as $ordem)
                                <li>
                                    <a href="{{ route('printorder.showorder', ['topsl' => $id, 'numorp' => $ordem->usu_numorp]) }}">
                                        📄 {{ $ordem->usu_numorp }} | {{ $ordem->usu_codid }} | {{ $ordem->usu_codniv }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                @endforeach
            </ul>

            <script>
                function toggle(element) {
                    const childUl = element.querySelector("ul");
                    if (childUl) {
                        childUl.classList.toggle("hidden");
                    }
                }
            </script>

        </div>

        <!-- Coluna da direita: PDF -->
        <div class="col-md-8">
            <h4>Arquivo PDF</h4>
            @if($pdfContent ?? false)
                <iframe
                    src="data:application/pdf;base64,{{ $pdfContent }}"
                    width="100%"
                    height="100%"
                    style="border: 1px solid #ccc;">
                </iframe>
            @else
                <p>Selecione uma ordem para visualizar o PDF.</p>
            @endif
        </div>
    </div>

    <script>
        function toggle(element) {
            const childUl = element.querySelector("ul");
            if (childUl) {
                childUl.classList.toggle("hidden");
            }
        }
    </script>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
