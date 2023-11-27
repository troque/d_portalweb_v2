<!DOCTYPE html>

@if (Auth::user())
    <html>

    <head>
        <title>Portal Web - Detalle Proceso</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <link rel="stylesheet"
            href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
        <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
        <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
        <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
        <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
        <script src="https://kit.fontawesome.com/614eacb451.js" crossorigin="anonymous"></script>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css"
            integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10.16.6/dist/sweetalert2.all.min.js"></script>
        <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/sweetalert2@10.10.1/dist/sweetalert2.min.css'>
        <link rel="icon" href="/assets/images/favicon.ico" />
    </head>

    <body>
        <x-app-layout>

            <div class="py-12">
                <div class="max-w-7xl mx-auto"
                    style="width: 84%;
                    box-shadow: 0px 0px 12px 2px #bababb87;
                    border-radius: 6px;">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200" style="text-align: center;">
                            <div style="text-align: center; font-size: 20px;" class="mb-4">
                                <h1><b style="color: #0374a6;">Detalle del proceso</b></h1>
                            </div>
                            <div style="text-align: left;" class="mb-4">
                                <h3><b style="color: #0374a6;">Datos del proceso</b></h3>
                                <div class="mt-4" style="margin-left: 30px;">
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Radicado: </b> {{ isset($informacion->radicado) ? $informacion->radicado : '-' }} - {{ isset($informacion->vigencia) ? $informacion->vigencia : '-' }} </p>
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Fecha de registro: </b> {{ isset($informacion->fecha_registro) ? $informacion->fecha_registro : '-' }}</p>
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Fecha de ingreso: </b> {{ isset($informacion->fecha_ingreso) ? $informacion->fecha_ingreso : '-' }} </p>
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Etapa actual: </b> {{ isset($informacion->etapa) ? $informacion->etapa : '-' }} </p>
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Delegada encargada: </b> {{ isset($informacion->nombre_dependencia) ? $informacion->nombre_dependencia : '-' }} </p>
                                    <p class="mb-2" style="color: #939393;"><b style="color: #0374a6;">Antecedente: </b> {{ isset($informacion->descripcion) ? $informacion->descripcion : '-' }} </p>
                                </div>
                            </div>

                            <div style="text-align: left;" class="mt-5">
                                <div style="margin-top: 2px;">
                                    <a class="btn text-sm" href="{{ route('notifications') }}"
                                        style="color: white; background-color: #00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                                        {{ __('Notificaciones') }}
                                    </a>
                                </div>
                            </div>

                            <div style="text-align: left;" class="mt-4">
                                @if (isset($informacionPermisoUsuario) && $informacionPermisoUsuario == 1)
                                    <div style="text-align: left; margin-top: 50px;" class="mb-3">
                                        <h3><b style="color: #0374a6;">Sujetos Procesales</b></h3>
                                    </div>
                                    <div class="mt-4" style="margin-left: 30px;">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr style="color: #1576a9">
                                                    <th>Tipo documento</th>
                                                    <th>Número documento</th>
                                                    <th>Nombres</th>
                                                    <th>Tipo</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($informacionInteresados as $infInt)
                                                    <tr style="color: #939393;">
                                                        <td>{{ $infInt->nombre }}</td>
                                                        <td>{{ $infInt->numero_documento }}</td>
                                                        <td style="text-transform: capitalize">{{ mb_strtolower($infInt->nombre_interesado, 'UTF-8') }}</td>
                                                        <td>{{ $infInt->tipo_interesado }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            @if (Session::has('error'))
                                <script>
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        html: '{{ Session::get('msjRespuesta') }}'
                                    })
                                </script>
                            @endif

                            <div style="text-align: left;" class="mt-4">
                                @if (isset($informacionDataActuaciones) && $informacionDataActuaciones == 1)
                                    <div style="text-align: left;  margin-top: 50px;" class="mb-3">
                                        <h3><b style="color: #0374a6;">Actuaciones</b></h3>
                                    </div>
                                    <div class="mt-4" style="margin-left: 30px;">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr style="color: #1576a9">
                                                    <th>Fecha</th>
                                                    <th>Número de auto</th>
                                                    <th>Nombre actuación</th>
                                                    <th>Auto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($informacionActuaciones as $infAct)
                                                    <tr style="color: #939393;">
                                                        <td>{{ $infAct->created_at }}</td>
                                                        <td>{{ $infAct->auto }}</td>
                                                        <td style="text-transform: capitalize">{{ mb_strtolower($infAct->nombre_actuacion, 'UTF-8') }}</td>
                                                        <td> <button>
                                                                <a href="{{ route('download.file.actuacion', $infAct->uuid) }}"
                                                                    class="btn btn-sm"
                                                                    style="color: white; background-color: #00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;"
                                                                    <i class="fa fa-download"></i>Descargar
                                                                </a>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @elseif (isset($informacionDataActuaciones) && $informacionDataActuaciones == 0)
                                    <div style="text-align: left;  margin-top: 50px;" class="mb-3">
                                        <h3><b style="color: #939393;">No existe información de actuaciones aprobadas en este proceso.</b></h3>
                                    </div>
                                @endif
                            </div>

                            <div style="text-align: center;" class="mt-5">
                                <div style="margin-top: 2px;">
                                    <a class="btn text-sm" href="{{ route('dashboard') }}"
                                        style="color:#00a0da; border-radius: 20px; border-color:#00a0da; height: 90%; font-weight: bold; padding: 6px 25px 7px 25px;">
                                        {{ __('Regresar') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-layout>

    </body>

    </html>
@else
    <script>
        window.location = "/login";
    </script>
@endif
