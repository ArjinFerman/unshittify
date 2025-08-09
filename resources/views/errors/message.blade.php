@extends('layouts.default')

@section('style.base')
    #background {
        background: url('/bg_error.png');
        background-repeat: no-repeat;
        background-position: center center;
        background-size: cover;
        position: fixed;
        z-index: -50;

        width: 100%;
        height: 100%;
    }

    .error-message-code {
        --tw-border-opacity: 1;
        border-color: rgb(254 240 138 / var(--tw-border-opacity, 1));
    }

    .error-message {
        --tw-text-opacity: 1;
        --shadow-color: rgba(255,16,0,1.0);
        --shadow-blur-size: 2px;

        text-shadow: 0px 1px var(--shadow-blur-size) var(--shadow-color), 1px 0px var(--shadow-blur-size) var(--shadow-color),
                     0px -1px var(--shadow-blur-size) var(--shadow-color), -1px 0px var(--shadow-blur-size) var(--shadow-color);
        color: rgb(253 224 71 / var(--tw-text-opacity, 1)) /* #fde047 */;
    }
@endsection

@section('style.responsive')
@endsection

@section('content')
    <div class="relative flex justify-center min-h-[50vh] items-center sm:pt-0">
        <div class="max-w-xl mx-auto sm:px-6 lg:px-8">
            <div class="flex items-center pt-8 sm:justify-start sm:pt-0">
                <div class="px-4 text-lg tracking-wider border-r error-message-code error-message">
                    @yield('code')
                </div>

                <div class="ml-4 text-lg tracking-wider error-message">
                    @yield('message')
                </div>
            </div>
        </div>
    </div>
@endsection

@section('navbar')
@endsection

@section('footer')
@endsection
