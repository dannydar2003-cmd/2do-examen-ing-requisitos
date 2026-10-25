@extends('layouts.base')

@section('titulo', 'Médicos · Clínica Vida Salud')

@php
    $vacio = [
        'activas' => 'No hay médicos activos.',
        'inactivas' => 'No hay médicos inactivos.',
        'todas' => 'Todavía no hay médicos registrados.',
    ];
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Médicos</h1>
        <a href="{{ route('medicos.create') }}" class="btn btn-clinica">Registrar médico</a>
    </div>

    <form method="GET" action="{{ route('medicos.index') }}" class="row g-2 mb-3" role="search">
        @if (request()->has('estado'))
            <input type="hidden" name="estado" value="{{ $vista }}">
        @endif
        <div class="col-12 col-md-8 col-lg-6">
            <label for="q" class="visually-hidden">Buscar médico</label>
            <input type="search" id="q" name="q" value="{{ $texto }}" class="form-control"
                   placeholder="Buscar por nombre, especialidad, CI, registro o código (MED-000042)">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            @if ($texto !== '')
                <a href="{{ route('medicos.index') }}" class="btn btn-link">Limpiar</a>
            @endif
        </div>
    </form>

    @if ($texto !== '' && ! request()->has('estado'))
        <p class="small text-secondary">La búsqueda incluye a los médicos inactivos.</p>
    @endif

    <x-pestanas :pestanas="$pestanas" :activa="$vista" titulo="Estado de los médicos" />

    @if ($medicos->isEmpty())
        <div class="alert alert-info">
            @if ($texto !== '') No hay médicos que coincidan con «{{ $texto }}» en esta pestaña. @else {{ $vacio[$vista] }} @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Código</th>
                            <th>Nombre</th>
                            <th>Registro</th>
                            <th>Especialidades</th>
                            <th>Contacto</th>
                            <th>Acceso</th>
                            <th>Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($medicos as $medico)
                            <tr @class(['fila-inactiva' => ! $medico->estado])>
                                <td class="text-nowrap">{{ $medico->codigo }}</td>
                                <td>
                                    <span class="fw-semibold">{{ $medico->persona->nombre_completo }}</span>
                                    <div class="small">CI {{ $medico->persona->ci }}</div>
                                </td>
                                <td class="text-nowrap">{{ $medico->nro_registro_profesional }}</td>
                                <td>
                                    @foreach ($medico->especialidades->sortBy('nombre') as $especialidad)
                                        <span @class(['badge', 'text-bg-primary' => $especialidad->estado, 'text-bg-secondary' => ! $especialidad->estado])>{{ $especialidad->nombre }}</span>
                                    @endforeach
                                </td>
                                <td class="small">
                                    {{ $medico->persona->telefono }}<br>{{ $medico->persona->correo }}
                                </td>
                                <td class="small">{{ $medico->persona->usuario?->estadoAcceso() ?? 'Sin cuenta' }}</td>
                                <td>
                                    @if ($medico->estado)
                                        <span class="badge text-bg-primary">Activo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    @if ($medico->estado)
                                        <a href="{{ route('horarios.medico', $medico) }}" class="btn btn-outline-secondary btn-sm">Horarios</a>
                                    @endif
                                    <a href="{{ route('medicos.edit', $medico) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('medicos.estado', $medico) }}" class="d-inline"
                                          onsubmit="return confirm('{{ $medico->estado ? 'Se desactivará al médico: no recibirá citas nuevas y su cuenta de acceso, si la tiene, quedará desactivada. El historial se conserva.' : 'Se reactivará al médico. Su cuenta de acceso no se reactiva sola: hágalo en Usuarios.' }}')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="activo" value="{{ $medico->estado ? 0 : 1 }}">
                                        <input type="hidden" name="vista" value="{{ $vista }}">
                                        <input type="hidden" name="q" value="{{ $texto }}">
                                        @if ($medico->estado)
                                            <button type="submit" class="btn btn-outline-danger btn-sm">Desactivar</button>
                                        @else
                                            <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                                        @endif
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $medicos->links() }}</div>
    @endif
@endsection
