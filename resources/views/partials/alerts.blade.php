<div class="col-md-12" id="alertas_custom">
    @if (Session::has('success'))
        <div class="alert alert-success mt-2">
            {{ Session::get('success') }}
        </div>
    @elseif(Session::has('error'))
        <div class="alert alert-danger mt-2">
            {!! nl2br(Session::get('error')) !!}
        </div>
    @endif
</div>
