@extends('layouts.base')

@section('titulo', 'Horarios de '.$medico->persona->nombre_completo.' · Clínica Vida Salud')

@php
    use App\Models\HorarioAtencion;
@endphp

@section('contenido')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h1 class="h3 fw-semibold mb-1">{{ $medico->persona->nombre_completo }}</h1>
            <div>
                <span class="text-secondary small me-1">{{ $medico->codigo }}</span>
                @foreach ($medico->especialidades->sortBy('nombre') as $especialidad)
                    <span class="badge text-bg-primary">{{ $especialidad->nombre }}</span>
                @endforeach
                @unless ($medico->estado)
                    <span class="badge text-bg-secondary">Médico inactivo</span>
                @endunless
            </div>
        </div>
        <a href="{{ route('horarios.index') }}" class="btn btn-outline-secondary btn-sm">Volver a los médicos</a>
    </div>

    @unless ($medico->estado)
        <div class="alert alert-warning">
            El médico está desactivado: sus horarios no se pueden modificar. Reactívelo desde
            <a href="{{ route('medicos.index', ['estado' => 'inactivas']) }}">Médicos</a>.
        </div>
    @endunless

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-secondary">Semana de atención</h2>

                    @if ($horarios->isEmpty())
                        <div class="alert alert-warning mb-0">Este médico todavía no tiene horarios: no se le podrán agendar citas.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    @foreach (HorarioAtencion::DIAS as $numero => $nombre)
                                        <tr>
                                            <th class="text-nowrap" style="width: 7rem;">{{ $nombre }}</th>
                                            <td>
                                                @forelse ($horarios[$numero] ?? [] as $horario)
                                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 py-1">
                                                        <div>
                                                            <strong>{{ $horario->desde() }} – {{ $horario->hasta() }}</strong>
                                                            @if ($horario->consultorio)
                                                                <span class="text-secondary small ms-2">{{ $horario->consultorio }}</span>
                                                            @endif
                                                        </div>
                                                        @if ($medico->estado)
                                                            <div class="text-nowrap">
                                                                <a href="{{ route('horarios.edit', $horario) }}" class="btn btn-outline-secondary btn-sm">Editar</a>
                                                                <form method="POST" action="{{ route('horarios.destroy', $horario) }}" class="d-inline"
                                                                      onsubmit="return confirm('Se eliminará el bloque {{ $horario->descripcion() }}.')">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-outline-danger btn-sm">Eliminar</button>
                                                                </form>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @empty
                                                    <span class="text-secondary">—</span>
                                                @endforelse
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            @if ($medico->estado)
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h2 class="h6 text-uppercase text-secondary">Agregar un bloque</h2>
                        <form method="POST" action="{{ route('horarios.store', $medico) }}" class="needs-validation" novalidate>
                            @csrf
                            @include('horarios._formulario', ['horario' => null, 'compacto' => true])
                            <button type="submit" class="btn btn-clinica mt-3">Agregar horario</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card shadow-sm">
                <div class="card-body">
                    <h2 class="h6 text-uppercase text-secondary">Duración de las citas</h2>
                    <p class="small text-secondary">
                        No se define aquí: la determina la especialidad con la que se reserva.
                    </p>
                    <ul class="mb-0 small">
                        @foreach ($medico->especialidades->sortBy('nombre') as $especialidad)
                            <li>{{ $especialidad->nombre }}: <strong>{{ $especialidad->duracion_min }} min</strong></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
@endsection
