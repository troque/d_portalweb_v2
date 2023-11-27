<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200" style="text-align: center;">
                    {{ __('¡Bienvenido!') }}
                </div>
            </div>
        </div>
    </div>

    {{-- @if ($logAuditoria->count > 0)
        <p> Lista de accesos y visualización al portal </p>
        <table class="table table-striped table-bordered table-hover">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Información Interesado</th>
                    <th>Información Transacción</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logAuditoria as $log)
                    <tr>
                        <td>{{ $log->created_at }}</td>
                        <td>{{ $log->informacionInteresado }}</td>
                        <td>{{ $log->informacionEquipo }}</td>
                        <td>{{ $log->detalle }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>No se ha encontrado registros del usuario en nuestra base de datos.</p>
    @endif --}}
</x-app-layout>
