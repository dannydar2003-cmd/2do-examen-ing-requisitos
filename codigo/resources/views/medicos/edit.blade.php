@extends('layouts.base')

@section('titulo', 'Editar médico · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Editar médico <small class="text-secondary">{{ $medico->codigo }}</small></h1>
        <a href="{{ route('medicos.index') }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('medicos.update', $medico) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                @include('medicos._formulario', ['medico' => $medico])

                <p class="text-secondary small">
                    No se puede quitar una especialidad con la que el médico ya tiene citas registradas.
                </p>
                <button type="submit" class="btn btn-clinica">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection
