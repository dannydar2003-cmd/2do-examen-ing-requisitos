@extends('layouts.base')

@section('titulo', 'Especialidades · Clínica Vida Salud')

@php
    $etiquetas = ['activas' => 'Activas', 'inactivas' => 'Inactivas', 'todas' => 'Todas'];
    $pestanas = collect($etiquetas)->map(fn ($etiqueta, $clave) => [
        'etiqueta' => $etiqueta,
        'url' => route('especialidades.index', $clave === 'activas' ? [] : ['estado' => $clave]),
        'cuenta' => $cuentas[$clave],
    ])->all();
    $vacio = [
        'activas' => 'No hay especialidades activas.',
        'inactivas' => 'No hay especialidades inactivas.',
        'todas' => 'Todavía no hay especialidades registradas.',
    ];
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Especialidades</h1>
        <a href="{{ route('especialidades.create') }}" class="btn btn-clinica">Registrar especialidad</a>
    </div>

    <x-pestanas :pestanas="$pestanas" :activa="$vista" titulo="Estado de las especialidades" />

    @if ($especialidades->isEmpty())
        <div class="alert alert-info">{{ $vacio[$vista] }}</div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th class="text-end">Duración</th>
                            <th class="text-end">Médicos</th>
                            <th>Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($especialidades as $especialidad)
                            <tr @class(['fila-inactiva' => ! $especialidad->estado])>
                                <td class="fw-semibold">{{ $especialidad->nombre }}</td>
                                <td class="small">{{ $especialidad->descripcion ?? '—' }}</td>
                                <td class="text-end text-nowrap">{{ $especialidad->duracion_min }} min</td>
                                <td class="text-end">{{ $especialidad->medicos_count }}</td>
                                <td>
                                    @if ($especialidad->estado)
                                        <span class="badge text-bg-primary">Activa</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactiva</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('especialidades.edit', $especialidad) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                                    <form method="POST" action="{{ route('especialidades.estado', $especialidad) }}" class="d-inline"
                                          onsubmit="return confirm('{{ $especialidad->estado ? 'Se desactivará la especialidad: no se ofrecerá para nuevos médicos ni nuevas citas. Lo ya registrado se conserva.' : 'Se reactivará la especialidad.' }}')">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="activa" value="{{ $especialidad->estado ? 0 : 1 }}">
                                        <input type="hidden" name="vista" value="{{ $vista }}">
                                        @if ($especialidad->estado)
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
    @endif
@endsection
