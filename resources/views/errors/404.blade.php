@extends('errors::message')

@section('title', __('Not Found'))
@section('code', '404')

@section('message')
{!! nl2br(__("FORSOOTH!\nHere be naught but empty echoes.") )!!}
@endsection

@section('style.base')
    #background {
        background: url('/bg_not_found.png');
        background-repeat: no-repeat;
        background-position: center bottom;
        background-size: cover;
        position: fixed;
        z-index: -50;

        width: 100%;
        height: 100%;
    }

    .error-message-code {
        --tw-border-opacity: 1;
        border-color: rgb(138 240 254 / var(--tw-border-opacity, 1));
    }

    .error-message {
        --tw-text-opacity: 1;
        --shadow-color: rgba(32,64,255,1.0);
        --shadow-blur-size: 2px;
        text-shadow: 0px 1px var(--shadow-blur-size) var(--shadow-color), 1px 0px var(--shadow-blur-size) var(--shadow-color),
                     0px -1px var(--shadow-blur-size) var(--shadow-color), -1px 0px var(--shadow-blur-size) var(--shadow-color);

        color: rgb(71 224 253 / var(--tw-text-opacity, 1));
    }
@endsection
