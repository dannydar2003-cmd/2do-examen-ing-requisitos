@extends('layouts.base')

@section('titulo', 'Editar paciente · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Editar paciente <small class="text-secondary">{{ $paciente->codigo }}</small></h1>
        <a href="{{ route('pacientes.show', $paciente) }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('pacientes.update', $paciente) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                @include('pacientes._formulario', ['paciente' => $paciente])

                <button type="submit" class="btn btn-clinica">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection
