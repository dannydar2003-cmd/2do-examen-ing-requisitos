@extends('layouts.base')

@section('titulo', 'Editar horario · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">
            Editar horario
            <small class="text-secondary">{{ $horario->medico->persona->nombre_completo }}</small>
        </h1>
        <a href="{{ route('horarios.medico', $horario->medico_id) }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('horarios.update', $horario) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                @include('horarios._formulario', ['horario' => $horario])

                <p class="text-secondary small mt-3">
                    No se puede modificar un bloque si dejaría fuera de la atención a una cita ya agendada.
                </p>
                <button type="submit" class="btn btn-clinica">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection
