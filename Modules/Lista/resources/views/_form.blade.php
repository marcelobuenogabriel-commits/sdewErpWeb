<input name="seqPro" id="seqPro" hidden="hidden" value="{{$lista->USU_SEQPRO}}"/>

@component('form._form_group', ['field' => 'codPlt'])
    {{Form::label('codPlt', 'Pallet', ['class' => 'control-label'])}}
    {{Form::text('codPlt', null, ['class' => 'form-control'])}}
@endcomponent
