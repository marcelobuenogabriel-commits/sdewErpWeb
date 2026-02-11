@extends('picking::layouts.master')

@section('content')
    <h1>Hello World</h1>

    <p>Module: {!! config('picking.name') !!}</p>
@endsection
