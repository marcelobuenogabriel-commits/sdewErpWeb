@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Conferência - Pallet'])

    @include('partials.alerts')

    <div class="container-fluid">
    <div class="row justify-content">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>{{ $title ?? 'Novo Registro' }}</h4>
                    <br>
                    <h6>{{$description}}</h6>
                </div>
                <div class="card-body">
                    @include('partials.alerts')
                    <form action="{{ route('conferencia.create_pallet') }}" method="POST">
                        @csrf
                        <input id="txtOrigemReq" name="txtOrigemReq" hidden="hidden" value="lista"></input>
                        <input id="txtCodFpj" name="txtCodFpj" hidden="hidden"></input>

                        <div class="form-group">
                            <label for="txtNumPrj">Projeto</label>
                            <input type="text" class="form-control" id="txtNumPrj" name="txtNumPrj" required onblur="setCodFpj()">
                        </div>

                        <div class="form-group">
                                <label for="txtTipPal">Tipo de Palete</label>
                                <select class="form-control" id="txtTipPal" name="txtTipPal">
                                    <option value="EL">EL</option>
                                    <option value="P">P</option>
                                    <option value="PG">PG</option>
                                    <option value="RL">RL</option>
                                </select>
                        </div>

                        <button type="submit" class="btn btn-warning btnCriarPallet">Criar Pallet</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function setCodFpj() {
        const numPrj = document.getElementById('txtNumPrj').value;

        if (parseInt(numPrj) == 1) {
            document.getElementById('txtCodFpj').value = 1;
        } else {
            document.getElementById('txtCodFpj').value = 2;
        }
    }
</script>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop