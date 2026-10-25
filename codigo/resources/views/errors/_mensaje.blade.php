{{-- Cuerpo común de las páginas de error: código, título, explicación y un botón para salir. --}}
<div class="row justify-content-center">
    <div class="col-md-6 text-center py-4">
        <p class="display-4 fw-bold text-secondary mb-2">{{ $codigo }}</p>
        <h1 class="h3 fw-semibold">{{ $titulo }}</h1>
        <p class="text-secondary">{{ $texto }}</p>
        <a href="{{ $url }}" class="btn btn-clinica">{{ $boton }}</a>
    </div>
</div>
