@extends('layouts.base')

@section('titulo', 'La página caducó · Clínica Vida Salud')

@section('contenido')
    @include('errors._mensaje', [
        'codigo' => 419,
        'titulo' => 'La página caducó',
        'texto' => 'Pasó demasiado tiempo desde que abrió el formulario y la sesión se cerró por inactividad (dura 30 minutos). Vuelva a abrirlo e inténtelo de nuevo.',
        'url' => route('inicio'),
        'boton' => 'Ir al inicio',
    ])
@endsection
