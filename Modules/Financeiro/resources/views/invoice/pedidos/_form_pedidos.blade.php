<input hidden="hidden" id="vlrTot" class="vlrTot">

@component('form._form_group',['field' => 'codEmp'])
    {{Form::label('codEmp', 'Empresa', ['class' => 'control-label'])}}
    {{Form::select('codEmp',
                    ['' => 'Selecione a Empresa',
                     '1' => 'KNAPP Sudamérica Logística e Automação Ltda',
                     '2' => 'MAIS Inteligência',
                     '3' => 'KNAPP Chile',
                     '4' => 'KNAPP México',
                     '5' => 'KNAPP Peru'
                     ],
                    isset($invoice->codEmp) ? $invoice->codEmp : null,
                    ['id' => 'codEmp', 'class' => 'form-control' , 'onblur' => 'selectPedidos()'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group',['field' => 'notCre'])
    {{Form::label('notCre', 'Nota de Crédito', ['class' => 'control-label'])}}
    {{Form::select('notCre',
                    ['' => 'Selecione uma opção',
                     '1' => 'Sim',
                     '2' => 'Não'
                     ],
                    isset($invoice->notCre) ? $invoice->notCre : null,
                    ['id' => 'notCre', 'class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'codPed'])
    {{Form::label('codPed', 'Pedido', ['class' => 'control-label'])}}
    <select class="form-control" id="codPed" name="codPed" onblur="selectPedido()">
        <option value="{{isset($pedidos['id']) ? $pedidos['id'] : null}}">{{isset($pedidos['numped']) ? $pedidos['numped'] : null}}</option>
    </select>
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'pedCli'])
    {{Form::label('pedCli', 'Número Pedido do Cliente', ['class' => 'control-label'])}}
    {{Form::text('pedCli', NULL, ['class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'nomUsu'])
    {{Form::label('nomUsu', 'Pedido Por', ['class' => 'control-label'])}}
    {{Form::text('nomUsu', NULL, ['class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'vlrImp'])
    {{Form::label('vlrImp', 'Imposto de Renda (%)', ['class' => 'control-label'])}}
    {{Form::number('vlrImp', NULL, ['class' => 'form-control', 'step' => '0.01'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'perFat'])
    {{Form::label('perFat', 'Percentual de Faturamento (%)', ['class' => 'control-label'])}}
    {{Form::number('perFat', NULL, ['class' => 'form-control', 'step' => '0.00000001', 'onblur' => 'converteValorPedido()'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'vlrFat'])
    {{Form::label('vlrFat', 'Valor Fatura', ['class' => 'control-label'])}}
    {{Form::number('vlrFat', NULL, ['class' => 'form-control', 'step' => '0.01'])}}
    @include('partials.error')
@endcomponent

<div id="codMoeda" @if(isset($invoice->codMoe) == NULL) hidden="" @endif>
    @component('form._form_group', ['field' => 'codMoe'])
        {{Form::label('codMoe', 'Moeda Cotação', ['class' => 'control-label'])}}
        {{Form::select('codMoe',
                       ['' => 'Selecione a Moeda',
                        '03' => 'Dólar',
                        '04' => 'Euro',
                        '99' => 'Real'
                        ],
                       NULL,
                       ['class' => 'form-control'])}}
        @include('partials.error')
    @endcomponent
</div>

<div id="cotMoeda" @if(isset($invoice->codMoe) == NULL) hidden="" @endif>
    @component('form._form_group', ['field' => 'cotMoe'])
        {{Form::label('cotMoe', 'Cotação Invoice', ['class' => 'control-label'])}}
        {{Form::number('cotMoe', NULL, ['class' => 'form-control', 'step' => '0.0001'])}}
        @include('partials.error')
    @endcomponent
</div>
@component('form._form_group', ['field' => 'datVct'])
    {{Form::label('datVct', 'Data de Vencimento', ['class' => 'control-label'])}}
    {{Form::date('datVct', NULL, ['class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'tipPgt'])
    {{Form::label('tipPgt', 'Forma de Pagamento', ['class' => 'control-label'])}}
    {{Form::text('tipPgt', NULL, ['class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'desPro'])
    {{Form::label('desPro', 'Descrição Produto/Serviço', ['class' => 'control-label'])}}
    {{Form::text('desPro', NULL, ['class' => 'form-control'])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'refPed'])
    {{Form::label('refPed', 'Referência', ['class' => 'control-label'])}}
    {{Form::textarea('refPed', NULL, ['class' => 'form-control',  'rows' => 3])}}
    @include('partials.error')
@endcomponent

@component('form._form_group', ['field' => 'obsPar'])
    {{Form::label('obsPar', 'Observações Parcelas (Utilizar * para separar as descrição e > para indicar a Parcela)', ['class' => 'control-label'])}}
    {{Form::textarea('obsPar', NULL, ['class' => 'form-control', 'rows' => 3])}}
@endcomponent
