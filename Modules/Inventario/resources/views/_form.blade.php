@component('form._form_group', ['field' => 'datInv'])
    {{Form::label('datInv', 'Data Inventário', ['class' => 'control-label'])}}
    {{Form::date('datInv', null, ['class' => 'form-control'])}}
@endcomponent

@component('form._form_group', ['field' => 'codDep'])
    {{Form::label('codDep', 'Depósito', ['class' => 'control-label'])}}
    {{Form::text('codDep', null, ['class' => 'form-control'])}}
@endcomponent

@component('form._form_group', ['field' => 'numCon'])
    {{Form::label('numCon', 'Contagem', ['class' => 'control-label'])}}
    {{Form::text('numCon', null, ['class' => 'form-control'])}}
@endcomponent

@component('form._form_group', ['field' => 'numDoc'])
    {{Form::label('numDoc', 'Documento', ['class' => 'control-label'])}}
    {{Form::text('numDoc', null, ['class' => 'form-control'])}}
@endcomponent

@component('form._form_group', ['field' => 'codPro'])
    {{Form::label('codPro', 'SKU', ['class' => 'control-label'])}}
    {{Form::text('codPro', null, ['class' => 'form-control'])}}
@endcomponent

@component('form._form_group', ['field' => 'qtdPro'])
    {{Form::label('qtdPro', 'Quantidade', ['class' => 'control-label'])}}
    {{Form::number('qtdPro', null, ['class' => 'form-control'])}}
@endcomponent
