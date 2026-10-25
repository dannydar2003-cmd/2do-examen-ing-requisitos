{{-- No usa el layout de la aplicación: si el error vino de la base de datos o de la sesión, el menú también fallaría. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error del servidor · Clínica Vida Salud</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/clinica.css') }}">
</head>
<body class="bg-body-tertiary d-flex flex-column min-vh-100">
<main class="container py-5 flex-grow-1">
    <div class="row justify-content-center">
        <div class="col-md-6 text-center py-4">
            <p class="display-4 fw-bold text-secondary mb-2">500</p>
            <h1 class="h3 fw-semibold">Ocurrió un error inesperado</h1>
            <p class="text-secondary">No pudimos completar la operación. Intente de nuevo en unos minutos; si el problema continúa, avise al Administrador.</p>
            <a href="/" class="btn btn-clinica">Ir al inicio</a>
        </div>
    </div>
</main>
<footer class="py-3 text-center text-secondary small">
    Clínica Vida Salud &middot; Sistema de gestión de citas médicas
</footer>
</body>
</html>
