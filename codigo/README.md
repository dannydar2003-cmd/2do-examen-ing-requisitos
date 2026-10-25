# Clínica Vida Salud: sistema web de gestión de citas médicas

Segundo examen parcial de Ingeniería de Requisitos. Aplicación en **Laravel 13** (PHP 8.4.1 o superior), **PostgreSQL 16**, plantillas Blade y Bootstrap 5. El informe con el análisis y el diseño está en `../documento/Informe-Final.md`.

## Requisitos

- PHP 8.4.1 o superior con la extensión `pdo_pgsql`
- Composer
- Docker (para PostgreSQL)

## Puesta en marcha

```bash
cp .env.example .env
composer install
php artisan key:generate

docker compose up -d                  # PostgreSQL en el puerto 5433
docker exec clinica_db psql -U clinica -d postgres -c "CREATE DATABASE clinica_test;"   # base de las pruebas

php artisan migrate                   # crea el esquema y carga sucursal, roles y permisos
php artisan db:seed                   # crea el administrador inicial
php artisan serve                     # http://localhost:8000
```

La base de datos escucha en el puerto **5433** (y no en el 5432 habitual) para no chocar con otro PostgreSQL del equipo. Verifique que el `.env` tenga `DB_PORT=5433`; las pruebas ya lo usan en `phpunit.xml`.

`db:seed` muestra **una sola vez** la contraseña temporal del administrador, que vence a las 72 horas y se debe cambiar en el primer ingreso. El correo y el CI del administrador se cambian con `ADMIN_CORREO` y `ADMIN_CI` en `.env` antes de cargar los datos.

## Datos de demostración

Para probar el sistema con cada rol sin crear las cuentas a mano:

```bash
php artisan db:seed --class=DemoSeeder
```

Crea un Administrador, una Recepcionista, un Médico con su cuenta, su especialidad y sus horarios, y dos Pacientes con cuenta (uno con la ficha verificada y otro con la ficha pendiente de verificación). El comando muestra con qué correo o CI entra cada una. La contraseña de demostración es la constante `CONTRASENA` de `database/seeders/DemoSeeder.php`.

Las cuentas tienen una contraseña conocida, así que el seeder **solo corre con `APP_ENV=local`** y se niega en cualquier otro entorno. No modifica datos existentes: si el CI ya existe, esa cuenta se omite, de modo que se puede ejecutar más de una vez.

## Si el Administrador olvida su contraseña

Si no hay otro Administrador que se la renueve, quien administra el servidor puede generarle una contraseña temporal desde la consola:

```bash
php artisan administrador:renovar-password admin@clinicavidasalud.test
```

Solo sirve para cuentas con rol Administrador, pide confirmación (`--force` la omite), muestra la contraseña una sola vez y deja constancia en la bitácora.

## Pruebas

```bash
php artisan test
```

Las pruebas usan la base `clinica_test` y la recrean con las migraciones.

## Reiniciar la base de desarrollo

```bash
docker exec clinica_db psql -U clinica -d clinica_vida_salud \
  -c "DROP SCHEMA public CASCADE; CREATE SCHEMA public;"
php artisan migrate && php artisan db:seed
```

`migrate:rollback` falla a propósito si ya hay citas o cuentas que dependen del catálogo.

## Estructura

| Ruta | Contenido |
|---|---|
| `database/sql/` | Esquema en SQL puro: `001` (Ciclo #1), `002` (citas y notas, Ciclo #2) y `003` (catálogos iniciales) |
| `database/migrations/` | Migraciones que ejecutan esos archivos SQL |
| `app/Models/` | Modelos Eloquent (las entidades del análisis) |
| `app/Services/` | Reglas transversales: autenticación, contraseñas temporales, bitácora |
| `app/Http/` | Controladores, solicitudes de validación y middleware |
| `config/menu.php` | Opciones del menú y el permiso que exige cada una |
| `resources/views/` | Vistas Blade |
| `tests/` | Pruebas unitarias y de funcionalidad |

## Estado

**Ciclo #1:** base de datos con control de acceso (RBAC) y modelos, más:

- **CU-01 iniciar y cerrar sesión:** sesión con cookie, bloqueo por intentos, contraseña temporal, menú por permisos y bitácora.
- **CU-02 registro propio del paciente:** crea la cuenta y la ficha en estado pendiente de verificación; limitado a 10 solicitudes por minuto por IP.
- **CU-04 gestionar pacientes:** alta, búsqueda, edición, desactivación, cuenta con contraseña temporal (se muestra una sola vez), renovación y verificación de la ficha.

- **CU-03 gestionar usuarios y roles:** alta del personal (Administrador, Recepcionista y Médico vinculado a un médico registrado) con contraseña temporal de 72 horas que se muestra una sola vez, edición de datos y rol, desactivación y reactivación, y renovación de contraseña con verificación de identidad. Siempre queda al menos un Administrador activo y nadie se desactiva ni se cambia el rol a sí mismo.
- **CU-08 gestionar mi perfil:** el paciente ve sus datos y, con la ficha verificada, su información médica y contacto de emergencia en solo lectura; edita únicamente su teléfono y cambia su contraseña desde un enlace del perfil.
- **CU-05 gestionar médicos:** alta con sus especialidades (reutiliza la persona si el CI ya existe), edición, desactivación y reactivación (nunca se eliminan). Búsqueda por nombre, especialidad, CI, registro o código, con pestañas Activos, Inactivos y Todos; la búsqueda incluye a los inactivos. No se puede quitar una especialidad con la que el médico ya tiene citas.
- **CU-07 gestionar horarios de atención:** bloques por día de la semana para cada médico activo, con consultorio. No admite bloques superpuestos del mismo médico (los pegados sí) ni cambios que dejen fuera de la atención a una cita ya agendada. La sucursal se asigna sola (hay una única).
- **CU-06 gestionar especialidades:** alta, edición, desactivación y reactivación (nunca se eliminan), con nombre único sin distinguir mayúsculas ni espacios y duración de la cita entre 5 y 240 minutos. El listado muestra por defecto las activas, con pestañas Activas, Inactivas y Todas y su contador.
- **CU-19 renovar contraseña temporal:** la recepcionista renueva la contraseña del paciente que la olvidó, indicando cómo verificó su identidad (queda en la bitácora). No usa correo electrónico.

Las fechas se muestran en hora de Bolivia (`America/La_Paz`), tanto en la aplicación como en la conexión a PostgreSQL.
