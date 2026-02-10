<div class="form-group {{$errors->has($field)? 'has-error': ''}} col-md-12">
    {{ $slot }}
    @include('form._help_block',['field' => $field])
</div>
