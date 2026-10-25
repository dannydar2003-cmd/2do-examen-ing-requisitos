@extends('layouts.base')

@section('titulo', 'Registrar usuario · Clínica Vida Salud')

@php
    $rolElegido = old('rol');
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Registrar usuario del personal</h1>
        <a href="{{ route('usuarios.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('usuarios.store') }}" class="needs-validation" novalidate id="formulario-usuario">
                @csrf

                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label for="rol" class="form-label">Rol <span class="text-danger" aria-hidden="true">*</span></label>
                        <select id="rol" name="rol" required class="form-select @error('rol') is-invalid @enderror">
                            <option value="">Elija el rol</option>
                            @foreach ($roles as $rol)
                                <option value="{{ $rol->codigo }}" @selected($rolElegido === $rol->codigo)>{{ $rol->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">@error('rol') {{ $message }} @else Elija el rol de la cuenta. @enderror</div>
                    </div>
                </div>

                {{-- Rol Médico: la cuenta se vincula a un médico ya registrado y usa sus datos. --}}
                <section id="seccion-medico" class="mb-4 {{ $rolElegido === 'MEDICO' ? '' : 'd-none' }}">
                    <h2 class="h6 text-uppercase text-secondary mb-3">Médico</h2>
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="medico_id" class="form-label">Médico al que se vincula la cuenta <span class="text-danger" aria-hidden="true">*</span></label>
                            <select id="medico_id" name="medico_id" class="form-select @error('medico_id') is-invalid @enderror"
                                    @disabled($rolElegido !== 'MEDICO')>
                                <option value="">Elija el médico</option>
                                @foreach ($medicos as $medico)
                                    <option value="{{ $medico->id }}" @selected((int) old('medico_id') === $medico->id)>
                                        {{ $medico->persona->nombre_completo }} · {{ $medico->codigo }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">@error('medico_id') {{ $message }} @else Elija el médico. @enderror</div>
                            @if ($medicos->isEmpty())
                                <div class="form-text text-warning">
                                    No hay médicos activos sin cuenta. Registre uno en <a href="{{ route('medicos.index') }}">Médicos</a>.
                                </div>
                            @else
                                <div class="form-text">Entra con el correo que tiene registrado como médico.</div>
                            @endif
                        </div>
                    </div>
                </section>

                {{-- Los demás roles: se ingresan los datos de la persona. --}}
                <section id="seccion-persona" class="mb-4 {{ $rolElegido === 'MEDICO' ? 'd-none' : '' }}">
                    <h2 class="h6 text-uppercase text-secondary mb-3">Datos personales</h2>
                    @include('usuarios._datos-personales', ['persona' => null])
                    <p class="text-secondary small mt-3 mb-0">
                        Si el CI ya pertenece a otra persona sin cuenta (por ejemplo, un paciente), se reutiliza esa persona
                        y sus datos personales no se modifican.
                    </p>
                </section>

                <p class="text-secondary small">
                    Al registrar el usuario el sistema genera una contraseña temporal de 72 horas, que se muestra una sola vez.
                </p>
                <button type="submit" class="btn btn-clinica">Registrar usuario</button>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        // Con el rol Médico se pide el médico; con los demás roles, los datos de la persona. Lo oculto
        // se deshabilita para que no se envíe ni se valide.
        (function () {
            var rol = document.getElementById('rol');
            var seccionMedico = document.getElementById('seccion-medico');
            var seccionPersona = document.getElementById('seccion-persona');

            function activar(seccion, visible) {
                seccion.classList.toggle('d-none', !visible);
                seccion.querySelectorAll('input, select, textarea').forEach(function (campo) {
                    campo.disabled = !visible;
                });
            }

            function actualizar() {
                var esMedico = rol.value === 'MEDICO';
                activar(seccionMedico, esMedico);
                activar(seccionPersona, !esMedico);

                var medico = document.getElementById('medico_id');
                medico.required = esMedico;
            }

            rol.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
@endpush
