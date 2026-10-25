@extends('layouts.base')

@section('titulo', 'Página no encontrada · Clínica Vida Salud')

@section('contenido')
    @include('errors._mensaje', [
        'codigo' => 404,
        'titulo' => 'Página no encontrada',
        'texto' => 'La página que busca no existe o ya no está disponible.',
        'url' => route('inicio'),
        'boton' => 'Ir al inicio',
    ])
@endsection
