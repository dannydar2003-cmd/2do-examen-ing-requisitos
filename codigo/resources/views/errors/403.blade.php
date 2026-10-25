@extends('layouts.base')

@section('titulo', 'Acceso no permitido · Clínica Vida Salud')

@section('contenido')
    @include('errors._mensaje', [
        'codigo' => 403,
        'titulo' => 'Acceso no permitido',
        'texto' => 'Su cuenta no tiene permiso para ver esta página. Si cree que es un error, consulte con el Administrador.',
        'url' => route('inicio'),
        'boton' => 'Ir al inicio',
    ])
@endsection
