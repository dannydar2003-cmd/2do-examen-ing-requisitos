@extends('layouts.base')

@section('titulo', 'Demasiadas solicitudes · Clínica Vida Salud')

@section('contenido')
    @include('errors._mensaje', [
        'codigo' => 429,
        'titulo' => 'Demasiadas solicitudes',
        'texto' => 'Espere un minuto e intente de nuevo.',
        'url' => url()->previous(),
        'boton' => 'Volver',
    ])
@endsection
