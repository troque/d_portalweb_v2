<!DOCTYPE html>

<head>
    <title>Portal Web - Validar Usuario</title>
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

        <!-- Session Status -->
        <x-auth-session-status class="mb-4" :status="session('status')" />

        <form>
            @csrf

            <!-- Email Address -->
            <div style="text-align: center; margin-top: 35px;">
                <x-input-label>¡El usuario ha sido creado satisfactoriamente!</x-input-label>
            </div>

            <div style="display: flex; justify-content: space-around; margin-top: 33px;">
                <div style="margin-top: 2px;">
                    <a class="btn text-sm" href="{{ route('login') }}"
                        style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                        {{ __('REGRESAR') }}
                    </a>
                </div>
            </div>
        </form>
    </x-auth-card>
</x-guest-layout>

</html>
