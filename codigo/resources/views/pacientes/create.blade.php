@extends('layouts.base')

@section('titulo', 'Registrar paciente · Clínica Vida Salud')

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h1 class="h3 fw-semibold mb-0">Registrar paciente</h1>
        <a href="{{ route('pacientes.index') }}" class="btn btn-outline-secondary btn-sm">Volver al listado</a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('pacientes.store') }}" class="needs-validation" novalidate>
                @csrf
                @include('pacientes._formulario', ['paciente' => null])

                <p class="text-secondary small">
                    La ficha queda verificada porque la registra la recepción. Después se puede crear el acceso del paciente.
                </p>
                <button type="submit" class="btn btn-clinica">Registrar paciente</button>
            </form>
        </div>
    </div>
@endsection
