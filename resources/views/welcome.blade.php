<!DOCTYPE html>

<head>
    <title>Portal Web</title>
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
    <x-auth-validate>

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

            <!-- Número de documento -->
            <div class="mt-4">
                <div style="text-align: justify; font-size: 14px; line-height: 19px;">
                    <p>Autorización para el tratamiento de datos personales
                        Señor(a) usuario(a), autoriza a la Personería de Bogotá, D.C., ubicada en la Carrera 7 No. 21 -
                        24,
                        conmutador: (601)3820450/80 o línea de atención 143, para la recolección, consulta,
                        almacenamiento,
                        uso, traslado o eliminación de sus datos personales, con el fin de: adelantar las gestiones,
                        actuaciones e intervenciones que permitan el restablecimiento y goce de sus derechos, invitar a
                        eventos de participación ciudadana u organizados por la entidad, elaborar acciones
                        constitucionales,
                        caracterizar usuarios con fines estadísticos, enviar información a entidades autorizadas,
                        evaluar la
                        calidad del servicio, contactar al titular en los casos que se considere necesario, así como
                        defender el interés y patrimonio público dentro del marco de las funciones legales de la
                        Entidad.

                        Recuerde que no es obligatorio para la prestación del servicio, suministrar los datos personales
                        de
                        carácter sensible o de niños, niñas y adolescentes que le sean solicitados. Se exime el
                        tratamiento
                        de datos de niños, niñas y adolescentes, salvo aquellos datos que sean de naturaleza pública.
                        Como
                        titular de la información tiene derecho a conocer, actualizar, rectificar sus datos personales y
                        en
                        los casos en que sea procedente, suprimir o revocar la autorización otorgada para su
                        tratamiento,
                        solicitar prueba de la autorización otorgada al responsable del tratamiento y ser informado
                        sobre el
                        uso que le han dado a los mismos, presentar quejas ante la SIC por infracción a la ley y acceder
                        en
                        forma gratuita a sus datos personales.

                        Para profundizar sobre sus derechos como titular y otros aspectos relevantes, consulte nuestra
                        Política de Tratamiento de Datos Personales en el siguiente link: Politica de tratamiento.
                    </p>
                    <a
                        href="https://www.personeriabogota.gov.co/mecanismos-de-contacto-con-el-sujeto-obligado/proteccion-de-datos-personales/politica-de-proteccion-de-datos-personales">
                        https://www.personeriabogota.gov.co/mecanismos-de-contacto-con-el-sujeto-obligado/proteccion-de-datos-personales/politica-de-proteccion-de-datos-personales
                    </a>
                </div>
            </div>

            <div style="display: flex; justify-content: space-around; margin-top: 33px;">
                <div style="margin-top: 2px;">
                    <a class="btn btn-primary text-sm" href="{{ route('login') }}"
                        style="background: #00a0da; border-radius: 20px; font-weight: bold"">
                        {{ __('Aceptar') }}
                    </a>
                </div>
            </div>
        </form>
    </x-auth-validate>
</x-guest-layout>

</html>
