@extends('layouts.app')

@section('content')
    <div class="divWrapLoginPage">
        <form method="POST" action="{{ route('login') }}">
            @csrf
            <span class="login100-form-title p-b-20"><a href="{{ url('/') }}" rel="home"><img
                        src="https://www.knapp.com/wp-content/themes/knapp/assets/img/knapp_logo.svg"
                        alt="Logo Knapp" class="logo" width="188" height="60"></a></span>
            <span class="login100-form-title p-b-20"></span>
            <div class="wrap-input">
                <input id="user" type="user" class="input100 @error('user') is-invalid @enderror" name="user"
                       value="{{ old('user') }}"
                       placeholder="Usuário" required autocomplete="email" autofocus>
            </div>
            <div class="wrap-input">
                <input id="password" type="password" class="input100 @error('password') is-invalid @enderror"
                       name="password"
                       placeholder="Senha" required autocomplete="current-password">
            </div>

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                    <strong>Erro:</strong> {{ session('error') }}
                </div>
            @endif

            <div class="container-login100-form-btn">
                <div class="wrap-login100-form-btn">
                    <div class="login100-form-bgbtn"></div>
                    <button type="submit" class="login100-form-btn">
                        {{ __('Login') }}
                    </button>
                </div>
            </div>
            <div class="text-center p-t-75">
                @if (Route::has('password.request'))
                    <!--<a class="txt2" href="{{ route('password.request') }}">
                        {{ __('Esqueceu sua Senha?') }}
                    </a>-->
                @endif
            </div>
        </form>
    </div>
@endsection
