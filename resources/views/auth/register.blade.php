<!DOCTYPE html>

<head>
    <title>Portal Web - Registrar Usuario</title>
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

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <!-- Tipo de documento -->
            <div>
                <x-input-label for="tipo_documento" :value="__('Tipo de documento')" style="font-weight: bold; color: #0671a2fc;" />

                {{-- <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus /> --}}
                <select class="form-control block mt-1 w-full" id="tipo_documento" name="tipo_documento"
                    :value="old('tipo_documento')" required autofocus>
                    <option value="1">Cédula de Ciudadanía</option>
                    <option value="2">Cédula de Extranjería</option>
                    <option value="3">Pasaporte</option>
                </select>

                <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
            </div>

            <!-- Numero de documento -->
            <div class="mt-4">
                <x-input-label for="numero_documento" :value="__('Número de documento')" style="font-weight: bold; color: #0671a2fc;" />

                <x-text-input id="numero_documento" class="block mt-1 w-full" type="number" name="numero_documento"
                    :value="old('numero_documento')" required />

                <x-input-error :messages="$errors->get('numero_documento')" class="mt-2" />
            </div>

            @if ($errors->has('ok'))
                <div class="mt-4">
                    <ul class="text-sm text-green-600 space-y-1 mt-2">
                        <li>{{ $errors->first('msjRespuesta') }}</li>
                    </ul>
                </div>
            @elseif ($errors->has('error'))
                <div class="mt-4">
                    <ul class="text-sm text-red-600 space-y-1 mt-2">
                        <li>{{ $errors->first('msjRespuesta') }}</li>
                    </ul>
                </div>
            @endif

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
                        {{ __('REGISTRAR') }}
                    </x-primary-button>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>

</html>
