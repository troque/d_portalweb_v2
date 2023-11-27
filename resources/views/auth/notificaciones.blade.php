<!DOCTYPE html>

@if (Auth::user())
    <!DOCTYPE html>
    <html>

    <head>
        <title>Portal Web - Notificaciones</title>
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

    <style>
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0px;
            margin-left: 0px;
            display: inline;
            border: 0px;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            border: 0px;
        }

        .page-item.active .page-link {
            z-index: 1;
            color: #000000;
            background-color: #ffffff;
            border-color: #ffffff;
        }
    </style>

    <body>
        <x-app-layout>

            <div class="py-12">

                <!-- Modal del detalle de la notificación -->
                <div class="modal fade bd-example-modal-lg" id="modalDetalle" tabindex="-1" role="dialog"
                    aria-labelledby="modalDetalleTitle" aria-hidden="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalDetalleTitle"><b>Detalle notificación</b></h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <p name="detalleInformacion" id="detalleInformacion" value="">
                                </p>
                            </div>
                        </div>
                    </div>
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

                <div class="max-w-7xl mx-auto" style="width: 84%;">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="p-6 bg-white border-b border-gray-200" style="text-align: center;">
                            <div class="mt-4 mb-4" style="font-size: 22px;">
                                <b style="color: #1576a9">
                                    <h1>Notificaciones</h1>
                                </b>
                            </div>
                            <div style="font-size: 16px; margin-bottom: 50px; margin-top: 40px; text-align: left;">
                                <b>
                                    <p style="color: #1576a9">Listado de notificaciones recibidas</p>
                                </b>
                            </div>

                            <table class="table table-bordered table-sm" id="notificaciones" name="notificaciones"
                                style="text-align: center;">
                                <thead>
                                    <tr style="color: #1576a9">
                                        <th style="width: 17%;">Fecha notificación</th>
                                        <th>Notificación</th>
                                        <th>Documento</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </x-app-layout>
    </body>

    <script type="text/javascript">
        $(function() {

            $('#notificaciones').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('notifications/getInformacion') }}",
                columns: [{
                        data: 'fecha_notificacion',
                        name: 'fecha_notificacion',
                    },
                    {
                        data: 'informacion',
                        name: 'informacion',
                    },
                    {
                        data: 'detalle',
                        name: 'detalle',
                        orderable: false,
                        searchable: false
                    }
                ],
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                }
            });

            //triggered when modal is about to be shown
            $('#modalDetalle').on('show.bs.modal', function(e) {

                // Se busca el elemento con el valor
                var detalleInfo = $(e.relatedTarget).data('detalle');

                // Se setea el valor
                $("#detalleInformacion").text(detalleInfo);
            });
        });
    </script>

    </html>
@else
    <script>
        window.location = "/login";
    </script>
@endif
