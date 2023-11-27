<!DOCTYPE html>

<head>
    <title>Portal Web - Inicio de sesión</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
</head>

<style>
    .loader {
        font-size: 10px;
        margin: 50px auto;
        text-indent: -9999em;
        width: 11em;
        height: 11em;
        border-radius: 50%;
        background: #00a1db;
        background: -moz-linear-gradient(left, #00a1db 10%, rgba(255, 255, 255, 0) 42%);
        background: -webkit-linear-gradient(left, #00a1db 10%, rgba(255, 255, 255, 0) 42%);
        background: -o-linear-gradient(left, #00a1db 10%, rgba(255, 255, 255, 0) 42%);
        background: -ms-linear-gradient(left, #00a1db 10%, rgba(255, 255, 255, 0) 42%);
        background: linear-gradient(to right, #00a1db 10%, rgba(255, 255, 255, 0) 42%);
        position: relative;
        -webkit-animation: load3 1.4s infinite linear;
        animation: load3 1.4s infinite linear;
        -webkit-transform: translateZ(0);
        -ms-transform: translateZ(0);
        transform: translateZ(0);
    }

    .loader:before {
        width: 50%;
        height: 50%;
        background: #00a1db;
        border-radius: 100% 0 0 0;
        position: absolute;
        top: 0;
        left: 0;
        content: '';
    }

    .loader:after {
        background: #ffffff;
        width: 75%;
        height: 75%;
        border-radius: 50%;
        content: '';
        margin: auto;
        position: absolute;
        top: 0;
        left: 0;
        bottom: 0;
        right: 0;
    }

    @-webkit-keyframes load3 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }

    @keyframes load3 {
        0% {
            -webkit-transform: rotate(0deg);
            transform: rotate(0deg);
        }

        100% {
            -webkit-transform: rotate(360deg);
            transform: rotate(360deg);
        }
    }
</style>
<x-guest-layout>
    <x-auth-card>

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        {{-- <div class="loader">Cargando...</div> --}}

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div>
                <x-slot name="logo">
                    <a>
                        <x-application-logo class="w-auto h-20 fill-current text-gray-500" />
                    </a>
                </x-slot>
            </div>

            <!-- Tipo de documento -->
            <div>
                <x-input-label for="tipo_documento" :value="__('Tipo de documento')" style="font-weight: bold; color: #0671a2fc;" />

                {{-- <x-text-input id="tipo_documento" class="block mt-1 w-full" type="text" name="tipo_documento" :value="old('tipo_documento')" required autofocus /> --}}
                <select class="form-control block mt-1 w-full" id="tipo_documento" name="tipo_documento"
                    :value="old('tipo_documento')" required autofocus>
                    <option value="1">Cédula de Ciudadanía</option>
                    <option value="2">Cédula de Extranjería</option>
                    <option value="3">Pasaporte</option>
                </select>

                <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
            </div>

            <!-- Número de documento -->
            <div class="mt-4">
                <x-input-label for="numero_documento" :value="__('Número de documento')" style="font-weight: bold; color: #0671a2fc;" />

                <x-text-input id="numero_documento" class="block mt-1 w-full" type="number" name="numero_documento"
                    :value="old('numero_documento')" required autofocus />

                <x-input-error :messages="$errors->get('numero_documento')" class="mt-2" />
            </div>

            <!-- Contraseña -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Contraseña')" style="font-weight: bold; color: #0671a2fc;" />

                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required
                    autocomplete="current-password" />

                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            @if ($errors->has('error'))
                <div class="mt-4">
                    <ul class="text-sm text-red-600 space-y-1 mt-2">
                        <li>{{ $errors->first('msjRespuesta') }}</li>
                    </ul>
                </div>
            @endif

            @if ($errors->has('ok'))
                <div class="mt-4">
                    <ul class="text-sm text-green-600 space-y-1 mt-2">
                        <li>{{ $errors->first('msjRespuesta') }}</li>
                    </ul>
                </div>
            @endif

            @if ($errors->has('inactivo'))
                <div class="mt-4">
                    <ul class="text-sm text-red-600 space-y-1 mt-2">
                        <li>{{ $errors->first('msjRespuesta') }}</li>
                    </ul>
                </div>
            @endif

            <!-- Remember Me -->
            <div class="mt-4" style="display: flex; justify-content: space-between;">
                <div>
                    <label for="remember_me" class="inline-flex items-align-left">
                        <input id="remember_me" type="checkbox"
                            class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                            name="remember">
                        <span class="ml-2 text-sm text-gray-600"
                            style="font-weight: bold; color: #0671a2fc;">{{ __('Recordarme') }}</span>
                    </label>
                </div>

                <div>
                    @if (Route::has('password.request'))
                        <a class="underline text-sm text-gray-600 hover:text-gray-900"
                            href="{{ route('password.request') }}" style="color: #807b7b">
                            {{ __('¿Olvidó su contraseña?') }}
                        </a>
                    @endif
                </div>
            </div>

            <div style="display: flex; justify-content: space-around; margin-top: 33px;">
                <div style="margin-top: 2px;">
                    <a class="btn text-sm" href="{{ route('register') }}"
                    style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                    {{ __('REGISTRARSE') }}
                    </a>
                </div>
                <div>
                    <x-primary-button class="ml-3 btn btn-primary"
                    style="background: #00a0da; border-radius: 20px; font-weight: bold">
                    {{ __('Iniciar sesión') }}
                    </x-primary-button>
                </div>
            </div>

            @if ($errors->has('inactivo'))
                <x-text-input id="inactivo" name="inactivo" value="1" hidden />
                <div style="text-align: center; margin-top: 10px;">
                    <div>
                        <a class="btn text-sm" href="{{ route('resend') }}"
                            style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                            {{ __('REENVIAR CORREO DE ACTIVACIÓN DE CUENTA') }}
                        </a>
                    </div>
                </div>
            @endif
        </form>
    </x-auth-card>
</x-guest-layout>

</html>
