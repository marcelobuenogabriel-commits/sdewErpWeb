<div class="row">
    <div class="col-md-12">
        <hr>Address of Importer<hr>
        @component('form._form_group', ['field' => 'endCli'])
            {{Form::label('endCli', 'Endereço', ['class' => 'control-label'])}}
            {{Form::text('endCli', isset($endereco->endCli) ? $endereco->endCli : null, ['class' => 'form-control', 'id' => 'endCli', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'conCli'])
            {{Form::label('conCli', 'Contato', ['class' => 'control-label'])}}
            {{Form::text('conCli', isset($endereco->conCli) ? $endereco->conCli : null, ['class' => 'form-control', 'id' => 'conCli', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'cidCli'])
            {{Form::label('cidCli', 'Cidade', ['class' => 'control-label'])}}
            {{Form::text('cidCli', isset($endereco->cidCli) ? $endereco->cidCli : null, ['class' => 'form-control', 'id' => 'cidCli', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'paiCli'])
            {{Form::label('paiCli', 'País', ['class' => 'control-label'])}}
            {{Form::text('paiCli', isset($endereco->paiCli) ? $endereco->paiCli : null, ['class' => 'form-control', 'id' => 'paiCli', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <hr>Final Destination<hr>
        @component('form._form_group', ['field' => 'endDes'])
            {{Form::label('endDes', 'Endereço do Cliente', ['class' => 'control-label'])}}
            {{Form::text('endDes', isset($endereco->endDes) ? $endereco->endDes : null, ['class' => 'form-control', 'id' => 'endDes', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'conDes'])
            {{Form::label('conDes', 'Contato do Cliente', ['class' => 'control-label'])}}
            {{Form::text('conDes', isset($endereco->conDes) ? $endereco->conDes : null, ['class' => 'form-control', 'id' => 'conDes', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'cidDes'])
            {{Form::label('cidDes', 'Cidade do Cliente', ['class' => 'control-label'])}}
            {{Form::text('cidDes', isset($endereco->cidDes) ? $endereco->cidDes : null, ['class' => 'form-control', 'id' => 'cidDes', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent

        @component('form._form_group', ['field' => 'paiDes'])
            {{Form::label('paiDes', 'País do Cliente', ['class' => 'control-label'])}}
            {{Form::text('paiDes', isset($endereco->paiDes) ? $endereco->paiDes : null, ['class' => 'form-control', 'id' => 'paiDes', 'required' => 'required'])}}
            @include('partials.error')
        @endcomponent
    </div>
</div>
