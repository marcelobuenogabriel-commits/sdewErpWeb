<input hidden="hidden" id="vlrTot" class="vlrTot">

@component('form._form_group',['field' => 'codEmp'])
    {{Form::label('codEmp', 'Empresa', ['class' => 'control-label'])}}
   
    {{Form::select('codEmp',
                    [
                        '' => 'Selecione a Empresa',
                        '1' => 'KNAPP Sudamérica Logística e Automação Ltda'
                    ],
                        isset($invoice->codEmp) ? $invoice->codEmp : null,
                    [
                        'id' => 'codEmp', 
                        'class' => 'form-control' , 
                        'required' => 'required', 
                        'onblur' => 'selectPedidos()'
                    ])
    }}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'codPed'])
    {{Form::label('codPed', 'Pedido', ['class' => 'control-label'])}}
    <select class="form-control" id="codPed" name="codPed">
        <option value="{{isset($pedidos['id']) ? $pedidos['id'] : null}}">{{isset($pedidos['numped']) ? $pedidos['numped'] : null}}</option>
    </select>
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'ordBy'])
    {{Form::label('ordBy', 'Pedido por', ['class' => 'control-label'])}}
    {{Form::text('ordBy', isset($invoice->ordBy) ? $invoice->ordBy : null, ['class' => 'form-control', 'id' => 'ordBy', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'sitInv'])
    {{Form::label('sitInv', 'Situação da Invoice', ['class' => 'control-label', 'required' => 'required'])}}
    {{Form::select('sitInv',
                    ['' => 'Selecione a Situação da Invoice',
                     '1' => 'Em Elaboração',
                     '2' => 'Finalizado',
                     '3' => 'Cancelado'
                     ],
                    isset($invoice->sitInv) ? $invoice->sitInv : null,
                    ['id' => 'sitInv', 'class' => 'form-control', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'tipInc'])
    {{Form::label('tipInc', 'Visível', ['class' => 'control-label'])}}
    {{Form::select('tipInc',
                    [   '' => 'Selecione o Tipo',
                        'CPT' => 'CPT',
                        'DAP' => 'DAP',
                        'EXW' => 'EXW',
                        'FCA' => 'FCA',
                        'CIP' => 'CIP',
                        'CIF' => 'CIF',
                        'FOB' => 'FOB',
                        'DDP' => 'DDP',
                        'DPU' => 'DPU'
                     ],
                    isset($invoice->tipInc) ? $invoice->tipInc : null,
                    ['id' => 'tipInc', 'class' => 'form-control', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'madFor'])
    {{Form::label('madFor', 'Forma de Pagamento', ['class' => 'control-label'])}}
    {{Form::text('madFor', isset($invoice->madFor) ? $invoice->madFor : null, ['class' => 'form-control', 'id' => 'madFor', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'numVol'])
    {{Form::label('numVol', 'Quantidade de Volumes', ['class' => 'control-label'])}}
    {{Form::number('numVol', isset($invoice->numVol) ? $invoice->numVol : null, ['class' => 'form-control', 'id' => 'numVol', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'dimCax'])
    {{Form::label('dimCaxa', 'Dimensão da Caixa (cm)', ['class' => 'control-label'])}}
    {{Form::text('dimCax', isset($invoice->dimCax) ? $invoice->dimCax : null, ['class' => 'form-control', 'id' => 'dimCax', 'required' => 'required'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'gasTot'])
    {{Form::label('gasTot', 'Gastos Locais', ['class' => 'control-label'])}}
    {{Form::text('gasTot', isset($invoice->gasTot) ? $invoice->gasTot : null, ['class' => 'form-control money', 'id' => 'gasTot'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'gasFre'])
    {{Form::label('gasFre', 'Gastos com Frete', ['class' => 'control-label'])}}
    {{Form::text('gasFre', isset($invoice->gasFre) ? $invoice->gasFre : null, ['class' => 'form-control money', 'id' => 'gasFre'])}}
    @include('partials.error')
@endcomponent
 
@component('form._form_group', ['field' => 'gasSeg'])
    {{Form::label('gasSeg', 'Gastos com Seguro', ['class' => 'control-label'])}}
    {{Form::text('gasSeg', isset($invoice->gasSeg) ? $invoice->gasSeg : null, ['class' => 'form-control money', 'id' => 'gasSeg'])}}
    @include('partials.error')
@endcomponent
    
@component('form._form_group', ['field' => 'gasDin'])
    {{Form::label('gasDin', 'Gastos em Destino', ['class' => 'control-label'])}}
    {{Form::text('gasDin', isset($invoice->gasDin) ? $invoice->gasDin : null, ['class' => 'form-control money', 'id' => 'gasDin'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'pesBru'])
    {{Form::label('pesBru', 'Peso Bruto (kg)', ['class' => 'control-label'])}}
    {{Form::number('pesBru', isset($invoice->pesBru) ? $invoice->pesBru : null, ['class' => 'form-control', 'id' => 'pesBru', 'required' => 'required', 'step' => '0.01'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'pesLiq'])
    {{Form::label('pesLiq', 'Peso Líquido (kg)', ['class' => 'control-label'])}}
    {{Form::number('pesLiq', isset($invoice->pesLiq) ? $invoice->pesLiq : null, ['class' => 'form-control', 'id' => 'pesLiq', 'required' => 'required', 'step' => '0.01'])}}
    @include('partials.error')
@endcomponent