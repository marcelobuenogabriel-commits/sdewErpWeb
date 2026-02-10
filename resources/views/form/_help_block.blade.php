@if($errors->has($field))
    <span class="help-block text-danger text-sm">
        {{$errors->first($field)}}
    </span>
@endif