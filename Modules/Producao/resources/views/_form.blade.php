<input hidden="hidden" id="codEmp" name="codEmp" class="codEmp" value="1">
<input hidden="hidden" id="codFil" name="codFil" class="codFil" value="1">

@component('form._form_group', ['field' => 'numPro'])
    {{Form::label('numPro', 'Projeto*', ['class' => 'control-label'])}}
    {{Form::text('numPro', null, ['class' => 'form-control', 'id' => 'numPro', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'codFam'])
    {{Form::label('codFam', 'Família*', ['class' => 'control-label'])}}
    {{Form::text('codFam', null, ['class' => 'form-control', 'id' => 'codFam', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'codSta'])
    {{Form::label('codSta', 'Station', ['class' => 'control-label'])}}
    {{Form::text('codSta', null, ['class' => 'form-control', 'id' => 'codSta'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'codIdPr'])
    {{Form::label('codIdPr', 'ID Resultante', ['class' => 'control-label'])}}
    {{Form::text('codIdPr', null, ['class' => 'form-control', 'id' => 'codIdPr'])}}
    @include('partials.error')
@endcomponent
