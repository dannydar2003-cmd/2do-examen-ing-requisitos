@extends('layouts.base')

@section('titulo', 'Editar especialidad · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Editar especialidad</h1>
        <a href="{{ route('especialidades.index') }}" class="btn btn-outline-secondary btn-sm">Cancelar</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('especialidades.update', $especialidad) }}" class="needs-validation" novalidate>
                @csrf
                @method('PUT')
                @include('especialidades._formulario', ['especialidad' => $especialidad])

                <p class="small text-secondary">
                    Cambiar la duración afecta solo a las citas nuevas: las ya agendadas conservan su horario.
                </p>
                <button type="submit" class="btn btn-clinica">Guardar cambios</button>
            </form>
        </div>
    </div>
@endsection
