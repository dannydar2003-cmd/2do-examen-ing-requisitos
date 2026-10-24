# Preparación del entorno local — Clínica Vida Salud

Guía para preparar en Windows el entorno de desarrollo del proyecto del equipo. La aplicación Laravel vive en la subcarpeta `codigo`; los comandos de esta guía se ejecutan desde la carpeta raíz del repositorio, salvo que se indique lo contrario.

## 1. Qué estamos preparando

El **entorno de desarrollo local** reúne los programas y ajustes necesarios para ejecutar y probar el sistema en una computadora antes de publicarlo en la nube.

En este proyecto:

- **Laravel con PHP** ejecuta la aplicación web.
- **Blade** genera las páginas HTML desde Laravel; viene incluido en el framework.
- **Bootstrap** aporta estilos y componentes visuales; el proyecto ya incluye sus archivos en `codigo/public/vendor/bootstrap`.
- **PostgreSQL** guarda los datos de la aplicación.
- **Docker Compose** inicia PostgreSQL como contenedor local.
- **PHPUnit** ejecuta pruebas automatizadas; se instala como dependencia del proyecto.

## 2. Herramientas instaladas

### Laravel Herd

Herd administra versiones de PHP y proporciona comandos para PHP, Composer y Laravel en Windows. En esta instalación fue Herd 2.0.3.

Durante su configuración inicial:

1. Se concedió el permiso de administrador solicitado por Herd para configurar servicios locales.
2. Se omitió la conexión opcional con proveedores de IA y la activación de Herd Pro.
3. El aviso de Windows Firewall apareció al abrir Herd. Se permitió el acceso para que la aplicación pudiera configurar su comunicación local.
4. Si PowerShell no reconoce `herd` o `php`, termina la configuración de Herd, cierra y vuelve a abrir PowerShell. En este caso, los comandos empezaron a reconocerse después de completar la pantalla “Let’s get started”.

### Composer y el instalador de Laravel

Composer descarga las bibliotecas PHP que el proyecto necesita. Laravel Installer ayuda a crear una aplicación nueva, pero **no es el framework dentro del proyecto**. El framework Laravel se obtiene en el siguiente apartado mediante Composer.

Comandos de comprobación:

```powershell
herd --version
php --version
composer --version
laravel --version
```

## 3. Elegir la versión correcta de PHP

La tabla inicial del stack indicaba PHP 8.3. Sin embargo, al intentar instalar las dependencias del repositorio con PHP 8.3.36, Composer informó que varias versiones guardadas en `composer.lock` necesitan **PHP 8.4.1 o superior** (por ejemplo, Symfony 8.1.9).

Por eso se usó PHP 8.5.12, que cumple ese requisito y está disponible en Herd. El procedimiento fue:

```powershell
herd use 8.5
php --version
```

La salida debe mostrar PHP 8.5.x. Herd también puede instalar versiones adicionales; por ejemplo:

```powershell
herd php:install 8.3
herd use 8.3
```

Pero para este repositorio concreto, volver a PHP 8.3 impedirá instalar las dependencias fijadas en el archivo de bloqueo. El equipo debería actualizar su documentación del stack para reflejar el requisito real o acordar un cambio de dependencias. **No ejecutar `composer update` como solución individual**: puede cambiar `composer.lock` y afectar a todos los integrantes.

## 4. Instalar o comprobar Docker Desktop

Docker Desktop ejecuta contenedores en la computadora. Docker Compose lee la configuración del proyecto y crea/inicia los servicios definidos allí.

Comprobaciones:

```powershell
docker --version
docker compose version
docker ps
```

- Los dos primeros muestran las versiones del cliente y de Compose.
- `docker ps` comprueba que el motor de Docker esté activo. Una tabla vacía, sin error, es normal si todavía no hay contenedores.

Si Docker Desktop indica que está desactualizado, actualízalo desde la aplicación. Para usar contenedores locales no es necesario iniciar sesión en Docker Hub.

## 5. Revisar y ejecutar PostgreSQL

El proyecto ya tiene `codigo/docker-compose.yml`; no crear otro archivo. Su configuración define:

- Imagen `postgres:16`.
- Contenedor `clinica_db`.
- Base `clinica_vida_salud`.
- Usuario `clinica`.
- Contraseña local de desarrollo `clinica_dev`.
- Puerto del equipo `5433` conectado al puerto interno de PostgreSQL `5432`.
- Volumen `clinica_pgdata` para conservar los datos.

Desde la raíz del repositorio, iniciar la base:

```powershell
docker compose -f .\codigo\docker-compose.yml up -d
docker ps
```

La primera vez, Docker descarga la imagen de PostgreSQL y crea la red, el volumen y el contenedor. En `docker ps`, comprobar que `clinica_db` figure como `Up` y que el puerto incluya `5433->5432`.

Para detener la base sin eliminar sus datos:

```powershell
docker compose -f .\codigo\docker-compose.yml stop
```

Para iniciarla de nuevo, usar el comando `up -d` anterior. Evitar `down -v`: la opción `-v` elimina el volumen y borra los datos locales.

## 6. Preparar la configuración local de Laravel

Laravel lee los ajustes locales del archivo `codigo/.env`. Este archivo no se comparte en Git porque puede contener claves y credenciales. El repositorio ofrece una plantilla llamada `.env.example`.

Primero, comprobar si existe:

```powershell
Test-Path .\codigo\.env
```

Si la salida es `False`, crear la configuración local copiando la plantilla:

```powershell
Copy-Item .\codigo\.env.example .\codigo\.env
```

La plantilla del proyecto ya contiene la conexión correcta para Docker:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5433
DB_DATABASE=clinica_vida_salud
DB_USERNAME=clinica
DB_PASSWORD=clinica_dev
```

El puerto es 5433 porque Laravel corre directamente en Windows y PostgreSQL está en Docker. Si cada integrante usa la configuración Compose del repositorio, estos valores deben coincidir.

## 7. Instalar las dependencias del proyecto

Desde la raíz del repositorio:

```powershell
composer install --working-dir=.\codigo
```

Composer lee `codigo/composer.lock` y descarga las versiones acordadas por el equipo dentro de `codigo/vendor`. No cambia deliberadamente las versiones fijadas en el lockfile.

En este proyecto se instalaron 110 paquetes, incluidos Laravel Framework 13.38.0 y PHPUnit 12.5.40. La primera ejecución con PHP 8.3 falló por incompatibilidad de versión; al cambiar a PHP 8.5, la instalación terminó correctamente.

## 8. Generar la clave de Laravel

Cada integrante necesita su propia clave local. Desde la raíz del repositorio:

```powershell
php .\codigo\artisan key:generate
```

Laravel guarda la clave en `codigo/.env` y la usa para cifrar información de la aplicación. Se genera una vez por configuración local. **No repetir el comando durante el uso normal**: cambiar la clave puede invalidar sesiones y valores cifrados.

## 9. Crear las tablas en PostgreSQL

Con el contenedor activo, ejecutar desde la raíz:

```powershell
php .\codigo\artisan migrate
```

Laravel compara las migraciones del proyecto con la base de datos y crea las tablas que aún faltan. En esta instalación se ejecutaron:

- `2026_10_23_000001_crear_esquema_base`
- `2026_10_23_000002_crear_citas_y_notas`
- `2026_10_23_000003_cargar_catalogos_iniciales`

La última también carga catálogos iniciales. Las migraciones ya aplicadas quedan registradas y no se vuelven a aplicar en cada inicio.

## 10. Bootstrap, Blade y PHPUnit

- **Bootstrap:** los archivos ya están en `codigo/public/vendor/bootstrap`: `bootstrap.min.css` y `bootstrap.bundle.min.js`. No fue necesario descargarlo aparte.
- **Blade:** está incluido en Laravel y las vistas del proyecto lo usan; no se instala por separado.
- **PHPUnit:** se instaló con `composer install` porque forma parte de las dependencias de desarrollo definidas por el proyecto. Se puede usar más adelante para ejecutar pruebas; esta guía no las ejecuta.

## 11. Iniciar la aplicación

Desde la raíz del repositorio:

```powershell
php .\codigo\artisan serve
```

Mientras el comando siga ejecutándose, abrir en el navegador:

```
http://127.0.0.1:8000
```

El proyecto redirigió a `/login` y mostró la pantalla “Clínica Vida Salud”. Las migraciones terminaron correctamente, por lo que la aplicación pudo conectarse a PostgreSQL.

Mantener abierta la ventana de PowerShell del servidor y Docker Desktop mientras se trabaja. Para detener Laravel, volver a esa ventana y presionar **Ctrl+C**. Para detener PostgreSQL, usar el comando `docker compose ... stop` de la sección 5.

## 12. Orden rápido para otro integrante

Una vez clonado el repositorio y estando en su carpeta raíz:

1. Instalar/abrir Herd y Docker Desktop.
2. En PowerShell: `herd use 8.5`.
3. Confirmar versiones con `php --version`, `composer --version` y `docker compose version`.
4. Iniciar PostgreSQL: `docker compose -f .\codigo\docker-compose.yml up -d`.
5. Crear `.env` si falta: `Copy-Item .\codigo\.env.example .\codigo\.env`.
6. Instalar paquetes: `composer install --working-dir=.\codigo`.
7. Generar la clave local: `php .\codigo\artisan key:generate`.
8. Crear tablas: `php .\codigo\artisan migrate`.
9. Iniciar el sitio: `php .\codigo\artisan serve`.
10. Abrir `http://127.0.0.1:8000`.

Si la raíz del repositorio no se llama `2do-examen-ing-requisitos`, no importa: los comandos relativos siguen funcionando mientras PowerShell esté abierto en esa raíz. No compartir ni subir `codigo/.env`; cada integrante debe conservar su propia configuración local.
