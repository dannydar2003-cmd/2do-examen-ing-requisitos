@extends('layouts.base')

@section('titulo', 'Usuarios · Clínica Vida Salud')

@php
    $vacio = [
        'activas' => 'No hay usuarios activos.',
        'inactivas' => 'No hay usuarios inactivos.',
        'todas' => 'Todavía no hay usuarios del personal.',
    ];
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 fw-semibold mb-0">Usuarios del personal</h1>
            <p class="text-secondary small mb-0">
                Las cuentas de los pacientes se gestionan en <a href="{{ route('pacientes.index') }}">Pacientes</a>.
            </p>
        </div>
        <a href="{{ route('usuarios.create') }}" class="btn btn-clinica">Registrar usuario</a>
    </div>

    <form method="GET" action="{{ route('usuarios.index') }}" class="row g-2 mb-3" role="search">
        @if (request()->has('estado'))
            <input type="hidden" name="estado" value="{{ $vista }}">
        @endif
        <div class="col-12 col-md-8 col-lg-6">
            <label for="q" class="visually-hidden">Buscar usuario</label>
            <input type="search" id="q" name="q" value="{{ $texto }}" class="form-control"
                   placeholder="Buscar por nombre, CI, correo o rol">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-outline-secondary">Buscar</button>
            @if ($texto !== '')
                <a href="{{ route('usuarios.index') }}" class="btn btn-link">Limpiar</a>
            @endif
        </div>
    </form>

    @if ($texto !== '' && ! request()->has('estado'))
        <p class="small text-secondary">La búsqueda incluye a los usuarios inactivos.</p>
    @endif

    <x-pestanas :pestanas="$pestanas" :activa="$vista" titulo="Estado de los usuarios" />

    @if ($usuarios->isEmpty())
        <div class="alert alert-info">
            @if ($texto !== '') No hay usuarios que coincidan con «{{ $texto }}» en esta pestaña. @else {{ $vacio[$vista] }} @endif
        </div>
    @else
        <div class="card shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Acceso</th>
                            <th>Estado</th>
                            <th><span class="visually-hidden">Acciones</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($usuarios as $usuario)
                            @php($esUsted = $usuario->id === auth()->id())
                            <tr @class(['fila-inactiva' => ! $usuario->estado])>
                                <td>
                                    <span class="fw-semibold">{{ $usuario->persona->nombre_completo }}</span>
                                    @if ($esUsted) <span class="badge text-bg-light">Usted</span> @endif
                                    <div class="small">CI {{ $usuario->persona->ci }}</div>
                                </td>
                                <td class="small">{{ $usuario->persona->correo }}</td>
                                <td><span class="badge text-bg-primary">{{ $usuario->rol->nombre }}</span></td>
                                <td class="small">{{ $usuario->estadoAcceso() }}</td>
                                <td>
                                    @if ($usuario->estado)
                                        <span class="badge text-bg-primary">Activo</span>
                                    @else
                                        <span class="badge text-bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end text-nowrap">
                                    <a href="{{ route('usuarios.edit', $usuario) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                                    @unless ($esUsted)
                                        <form method="POST" action="{{ route('usuarios.estado', $usuario) }}" class="d-inline"
                                              onsubmit="return confirm('{{ $usuario->estado ? 'Se desactivará la cuenta: la persona ya no podrá iniciar sesión.' : 'Se reactivará la cuenta.' }}')">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="activo" value="{{ $usuario->estado ? 0 : 1 }}">
                                            <input type="hidden" name="vista" value="{{ $vista }}">
                                            <input type="hidden" name="q" value="{{ $texto }}">
                                            @if ($usuario->estado)
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Desactivar</button>
                                            @else
                                                <button type="submit" class="btn btn-success btn-sm">Reactivar</button>
                                            @endif
                                        </form>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">{{ $usuarios->links() }}</div>
    @endif
@endsection
