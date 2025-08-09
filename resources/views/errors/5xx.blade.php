@extends('errors::message')

@section('title', __('Server Error'))
@section('code', $exception->getStatusCode())

@section('message')
    {!! nl2br(__("WRETCHED FOOL!\n’Tis THY HAND that wrought this doom!") )!!}
@endsection
