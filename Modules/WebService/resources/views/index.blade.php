@extends('webservice::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('webservice.name') !!}</p>
@endsection
