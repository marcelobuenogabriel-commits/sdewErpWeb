@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_show',
                ['route' => 'importacao.index',
                 'class' => 'Invoices - Importação',
                 'variable' => 'Adicionar Endereços'])

    @include('partials.alerts')

    <div class="row">
        <div class="col-md-12">
            <section class="content">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Informações de Entrega da Invoice</h3>
                        <div class="card-tools"></div>
                    </div>
                    <div class="card-body">
                        {{Form::model($invoice, ['route' => ['importacao.updateendereco', $invoice->codInv, 'codEmp' => $invoice->codEmp], 'method' => 'PUT'])}}
                            @include('importacao::_form_endereco')

                            <button type="submit" class="btn btn-secondary">Salvar</button>

                        {{Form::close()}}
                    </div>
                    <div class="card-footer">

                    </div>
                </div>
            </section>
        </div>
    </div>

@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
