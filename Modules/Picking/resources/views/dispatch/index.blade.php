@extends('adminlte::page')

@section('content')

    @include('partials.breadcrumb_index', ['class' => 'Picking - Dispatch'])

    @include('partials.alerts')

    <section style="min-height: 82Vh">
        <div class="card">
            <div class="card-header">
                <b>Picking - Dispatch</b>
            </div>
            <div class="card-body">
                {{Form::open(['route' => 'dispatch.store'])}}

                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('codmov', 'Número Movimentação', ['class' => 'control-label'])}}
                        {{Form::number('codmov', null,  ['class' =>  'form-control'. ($errors->has('codmov')? ' is-invalid':NULL)])}}
                    </div>

                    @error('codmov')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        {{Form::label('numpro', 'Informe o Projeto - somente números Z119-XXXXXX', ['class' => 'control-label'])}}
                        {{Form::text('numpro', null,  ['class' => 'form-control'. ($errors->has('numpro') ? ' is-invalid':NULL),
                                                           'placeholder' => '0000',
                                                           ($errors->has('numpro') ? NULL : 'readonly')
                                                            ])}}
                    </div>

                    @error('numpro')
                    <div class="alert alert-danger">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-warning" name="search" value="search">Pesquisar
                        Movimentação
                    </button>
                </div>

                <div class="col-md-3 mb-2">
                    <button type="submit" class="btn btn-secondary" name="create" value="create">Criar
                        Movimentação
                    </button>
                </div>
                {{Form::close()}}
            </div>
            <div class="card-footer">

            </div>
        </div>
    </section>
@endsection

@section('footer')
    KNAPP Sudamérica Logística e Automação LTDA {{now()->format('Y')}}
@stop
