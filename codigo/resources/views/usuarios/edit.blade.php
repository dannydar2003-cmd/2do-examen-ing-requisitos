@extends('layouts.base')

@section('titulo', 'Editar usuario · Clínica Vida Salud')

@php
    $persona = $usuario->persona;
    $esMedico = $usuario->rol->codigo === 'MEDICO';
    $esUsted = $usuario->id === auth()->id();
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">
            Editar usuario <small class="text-secondary">{{ $persona->nombre_completo }}</small>
            @if ($esUsted) <span class="badge text-bg-light">Usted</span> @endif
        </h1>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    @if ($esMedico)
                        <h2 class="h6 text-uppercase text-secondary mb-3">Cuenta de médico</h2>
                        <dl class="row mb-3">
                            <dt class="col-sm-4">Nombre</dt><dd class="col-sm-8">{{ $persona->nombre_completo }}</dd>
                            <dt class="col-sm-4">CI</dt><dd class="col-sm-8">{{ $persona->ci }}</dd>
                            <dt class="col-sm-4">Correo (usuario)</dt><dd class="col-sm-8">{{ $persona->correo }}</dd>
                            <dt class="col-sm-4">Rol</dt><dd class="col-sm-8"><span class="badge text-bg-primary">{{ $usuario->rol->nombre }}</span></dd>
                        </dl>
                        <p class="text-secondary small mb-0">
                            Los datos de una cuenta de médico se editan en <a href="{{ route('medicos.edit', $persona->medico) }}">Médicos</a>.
                        </p>
                    @else
                        <form method="POST" action="{{ route('usuarios.update', $usuario) }}" class="needs-validation" novalidate>
                            @csrf
                            @method('PUT')

                            <div class="row g-3 mb-4">
                                <div class="col-md-4">
                                    <label for="rol" class="form-label">Rol <span class="text-danger" aria-hidden="true">*</span></label>
                                    <select id="rol" name="rol" required class="form-select @error('rol') is-invalid @enderror"
                                            @disabled($esUsted)>
                                        @foreach ($roles as $rol)
                                            <option value="{{ $rol->codigo }}" @selected(old('rol', $usuario->rol->codigo) === $rol->codigo)>{{ $rol->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @if ($esUsted)
                                        <input type="hidden" name="rol" value="{{ $usuario->rol->codigo }}">
                                        <div class="form-text">No puede cambiar su propio rol.</div>
                                    @endif
                                    <div class="invalid-feedback">@error('rol') {{ $message }} @enderror</div>
                                </div>
                            </div>

                            <h2 class="h6 text-uppercase text-secondary mb-3">Datos personales</h2>
                            @include('usuarios._datos-personales', ['persona' => $persona])

                            <button type="submit" class="btn btn-clinica mt-4">Guardar cambios</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-secondary">Acceso al sistema</h2>
                    <p class="mb-1">Entra con su correo: <strong>{{ $persona->correo }}</strong></p>
                    <p class="mb-3">Estado: <strong>{{ $usuario->estadoAcceso() }}</strong></p>

                    @if ($esUsted)
                        <p class="small text-secondary mb-0">
                            Para cambiar su propia contraseña use «Cambiar contraseña» en la barra superior.
                        </p>
                    @elseif (! $usuario->estado)
                        <p class="small text-secondary mb-0">Reactive la cuenta para poder renovar su contraseña.</p>
                    @else
                        @can('accesos:renovar')
                            <form method="POST" action="{{ route('usuarios.renovar', $usuario) }}" class="needs-validation" novalidate
                                  onsubmit="return !this.checkValidity() || confirm('Se generará una contraseña temporal nueva y la anterior dejará de servir.')">
                                @csrf
                                <label for="verificacion" class="form-label small mb-1">
                                    ¿Cómo verificó la identidad del titular? <span class="text-danger" aria-hidden="true">*</span>
                                </label>
                                <select id="verificacion" name="verificacion" required
                                        class="form-select form-select-sm mb-2 @error('verificacion') is-invalid @enderror">
                                    <option value="">Elija una opción</option>
                                    @foreach (\App\Services\PacienteService::VERIFICACION_IDENTIDAD as $clave => $etiqueta)
                                        <option value="{{ $clave }}" @selected(old('verificacion') === $clave)>{{ ucfirst($etiqueta) }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback mb-2">
                                    @error('verificacion') {{ $message }} @else Indique cómo verificó la identidad del titular. @enderror
                                </div>
                                <p class="form-text mt-0">
                                    La contraseña se entrega en persona o por mensaje al celular registrado, nunca a otro número.
                                </p>
                                <button type="submit" class="btn btn-outline-secondary btn-sm">Renovar contraseña temporal</button>
                            </form>
                        @endcan
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
