<!DOCTYPE html>
<html>

<head>
    <title>Portal Web - Inicio</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
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
            <div class="max-w-7xl mx-auto"
                style="width: 84%;
                box-shadow: 0px 0px 12px 2px #bababb87;
                border-radius: 6px;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200" style="text-align: center;">
                        <b style="font-weight: bold; color: #0274a9;">
                            {{ __('Lista de procesos donde aparece el usuario') }}
                        </b>
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="max-w-7xl mx-auto"
                style="width: 84%;
                box-shadow: 0px 0px 12px 2px #bababb87;
                border-radius: 6px;">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200" style="text-align: center;">
                        <div class="mb-5">
                            <b>
                                <p></p>
                            </b>
                        </div>
                        <table class="table table-bordered table-sm" id="procesosDisciplinarios"
                            name="procesosDisciplinarios">
                            <thead>
                                <tr style="color: #1576a9">
                                    <th>Número de proceso</th>
                                    <th>Información</th>
                                    <th>Detalle</th>
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

        $('#procesosDisciplinarios').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('dashboard/getInformacion') }}",
            columns: [{
                    data: 'radicado',
                    name: 'radicado',
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
            },
        });

    });
</script>

</html>
