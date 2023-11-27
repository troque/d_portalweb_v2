<!DOCTYPE html>

<head>
    <title>Portal Web - @if ($request->route('userId'))
            Restablecer contraseña
        @else
            Registrar usuario
        @endif
    </title>
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
            <a href="/">
                <x-application-logo class="w-auto h-20 fill-current text-gray-500" />
            </a>
        </x-slot>

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Se oculta el input del token y se captura en una variable -->
            <input type="hidden" id="token" name="token" value="{{ $request->route('token') }}">
            <input type="hidden" id="token" name="userId" value="{{ $request->route('userId') }}">

            <!-- Tipo de documento -->
            <div>
                <x-input-label for="tipo_documento" :value="__('Tipo de documento')" style="font-weight: bold; color: #0671a2fc;" />

                {{-- <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus /> --}}
                <select class="block mt-1 w-full" id="tipo_documento" name="tipo_documento"
                    value="{{ $numeroDocumento }}" disabled>
                    <option value="{{ $tipoDocumento }}">{{ $mensajeCedula }}</option>
                </select>

            </div>

            <!-- Numero de documento -->
            <div class="mt-4">
                <x-input-label for="numero_documento" :value="__('Número de documento')" style="font-weight: bold; color: #0671a2fc;" />
                <x-text-input id="numero_documento" class="block mt-1 w-full" type="number" name="numero_documento"
                    value="{{ $numeroDocumento }}" disabled />
            </div>

            <!-- Contraseña -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Contraseña')" style="font-weight: bold; color: #0671a2fc;" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <!-- Confirmar contraseña -->
            <div class="mt-4">
                <x-input-label for="password_confirmation" :value="__('Confirmar contraseña')"
                    style="font-weight: bold; color: #0671a2fc;" />
                <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password"
                    name="password_confirmation" required />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>


            <div style="display: flex; justify-content: space-around; margin-top: 33px;">
                <div>
                    <x-primary-button class="ml-3 btn btn-primary"
                        style="background: #00a0da; border-radius: 20px; font-weight: bold">
                        @if ($request->route('userId'))
                            {{ __('REESTABLECER CONTRASEÑA') }}
                        @else
                            {{ __('REGISTRAR') }}
                        @endif
                    </x-primary-button>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>

</html>
