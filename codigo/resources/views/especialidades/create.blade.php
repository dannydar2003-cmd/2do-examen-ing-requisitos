@extends('layouts.base')

@section('titulo', 'Registrar especialidad · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Registrar especialidad</h1>
        <a href="{{ route('especialidades.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('especialidades.store') }}" class="needs-validation" novalidate>
                @csrf
                @include('especialidades._formulario', ['especialidad' => null])

                <button type="submit" class="btn btn-clinica">Registrar especialidad</button>
            </form>
        </div>
    </div>
@endsection
