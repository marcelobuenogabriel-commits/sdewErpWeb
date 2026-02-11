@component('partials.breadcrumb')
    <li class="breadcrumb-item"><a href="{{route($route)}}">{{$class}}</a></li>
    <li class="breadcrumb-item active">{{$variable}}</li>
@endcomponent
