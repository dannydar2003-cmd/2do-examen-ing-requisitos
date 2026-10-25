@extends('layouts.base')

@section('titulo', 'Pacientes · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Pacientes</h1>
        <a href="{{ route('pacientes.create') }}" class="btn btn-clinica">Registrar paciente</a>
    </div>

    <form method="GET" action="{{ route('pacientes.index') }}" class="row g-2 mb-3" role="search">
        <div class="col-12 col-md-8 col-lg-6">
            <label for="q" class="visually-hidden">Buscar paciente</label>
            <input type="search" id="q" name="q" value="{{ $texto }}" class="form-control"
                   placeholder="Buscar por CI, nombre, apellido o código (PAC-000042)">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            @if ($texto !== '')
                <a href="{{ route('pacientes.index') }}" class="btn btn-link">Limpiar</a>
            @endif
        </div>
    </form>

    @if ($pacientes->isEmpty())
        <div class="alert alert-info">
            @if ($texto !== '') No hay pacientes que coincidan con «{{ $texto }}». @else Todavía no hay pacientes registrados. @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>CI</th>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>Ficha</th>
                            <th>Acceso</th>
                            <th>Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($pacientes as $paciente)
                            <tr>
                                <td class="text-nowrap">{{ $paciente->codigo }}</td>
                                <td class="text-nowrap">{{ $paciente->persona->ci }}</td>
                                <td>{{ $paciente->persona->nombre_completo }}</td>
                                <td class="text-nowrap">{{ $paciente->persona->telefono ?? '—' }}</td>
                                <td>
                                    @if ($paciente->verificado)
                                        <span class="badge text-bg-success">Verificada</span>
                                    @else
                                        <span class="badge text-bg-warning">Pendiente</span>
                                    @endif
                                </td>
                                <td class="small">{{ $paciente->persona->usuario?->estadoAcceso() ?? 'Sin cuenta' }}</td>
                                <td>
                                    @if ($paciente->estado)
                                        <span class="badge text-bg-primary">Activo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('pacientes.show', $paciente) }}" class="btn btn-outline-secondary btn-sm">Ver</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $pacientes->links() }}</div>
    @endif
@endsection
