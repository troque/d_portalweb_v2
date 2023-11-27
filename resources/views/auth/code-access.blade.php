<!DOCTYPE html>

<head>
    <title>Portal Web - Validar Codigo Acceso</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
</head>

<x-guest-layout>
    <x-auth-card>
        <x-slot name="logo">
            <a>
                <x-application-logo class="w-auto h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <!-- Se valida la sessión del usuario -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form method="POST" action="{{ route('code-access') }}">
            @csrf

            @if (app('request')->input('token') &&
                app('request')->input('auth') &&
                app('request')->input('tip') &&
                app('request')->input('ced') &&
                !$errors->has('error'))

                <!-- INPUTS OCULTOS A ENVIAR EN EL FORMULARIO -->
                <input type="hidden" id="numero_documento" name="numero_documento" value="{{ $numeroDocumento }}">
                <input type="hidden" id="tipo_documento" name="tipo_documento" value="{{ $tipoDocumento }}">
                <input type="hidden" id="password" name="password" value="{{ $contrasena }}">
                <input type="hidden" id="portalUserId" name="portalUserId" value="{{ $portalUserId }}">

                <!-- Input del código de acceso -->
                <div class="mt-4">
                    <x-input-label for="codigo_acceso" :value="__('Código de acceso')" style="font-weight: bold; color: #0671a2fc;" />

                    <x-text-input id="codigo_acceso" class="block mt-1 w-full" type="text" name="codigo_acceso"
                        :value="old('codigo_acceso')" required autofocus />

                    <x-input-error :messages="$errors->get('codigo_acceso')" class="mt-2" />
                </div>

                <div style="display: flex; justify-content: space-around; margin-top: 33px;">
                    <div style="margin-top: 2px;">
                        <a class="btn text-sm" href="{{ route('login') }}"
                            style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                            {{ __('REGRESAR') }}
                        </a>
                    </div>
                    <div>
                        <x-primary-button class="ml-3 btn btn-primary"
                            style="background: #00a0da; border-radius: 20px; font-weight: bold">
                            {{ __('INGRESAR') }}
                        </x-primary-button>
                    </div>
                </div>
            @else
                <div style="text-align: center;">
                    @if ($errors->has('error'))
                        <div class="mt-4" style="margin-bottom: 22px;">
                            <ul class="text-sm text-red-600 space-y-1 mt-2">
                                <li>{{ $errors->first('msjRespuesta') }}</li>
                            </ul>
                        </div>

                        <div style="margin-top: 2px; margin-right: 10px; text-align: center;">
                            <a class="btn text-sm"
                                style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;"
                                href="{{ route('login') }}">
                                {{ __('REGRESAR') }}
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </form>
    </x-auth-card>
</x-guest-layout>

</html>
