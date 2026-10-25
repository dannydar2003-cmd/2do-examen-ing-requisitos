<?php

namespace Tests\Feature\Consola;

use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Usuario;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Datos de demostración: cuentas con contraseña conocida que solo existen en el entorno local.
 */
class DemoSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_crea_nada_fuera_del_entorno_local(): void
    {
        $this->app['env'] = 'production';

        $this->artisan('db:seed', ['--class' => DemoSeeder::class, '--force' => true])->assertSuccessful();

        $this->assertSame(0, Usuario::count());
    }

    public function test_crea_una_cuenta_de_cada_rol_en_el_entorno_local(): void
    {
        $this->app['env'] = 'local';

        $this->artisan('db:seed', ['--class' => DemoSeeder::class])->assertSuccessful();

        $roles = Usuario::with('rol')->get()->pluck('rol.codigo')->sort()->values()->all();
        $this->assertSame(['ADMINISTRADOR', 'MEDICO', 'PACIENTE', 'PACIENTE', 'RECEPCIONISTA'], $roles);

        $this->assertSame(1, Medico::count());
        $this->assertSame(2, Medico::firstOrFail()->horarios()->count());
        $this->assertSame([true, false], Paciente::orderBy('id')->pluck('verificado')->all());
    }

    public function test_las_cuentas_entran_con_la_contrasena_de_demostracion(): void
    {
        $this->app['env'] = 'local';
        $this->artisan('db:seed', ['--class' => DemoSeeder::class]);
        $this->app['env'] = 'testing'; // en otro entorno Laravel exigiría el token CSRF en las pruebas

        $this->post(route('login.autenticar'), ['identificador' => 'demo.medico@clinica.test', 'contrasena' => DemoSeeder::CONTRASENA])
            ->assertRedirect(route('inicio'));
        $this->post(route('logout'));

        $this->post(route('login.autenticar'), ['identificador' => '8000003', 'contrasena' => DemoSeeder::CONTRASENA])
            ->assertRedirect(route('inicio'));
    }

    public function test_es_idempotente_y_no_toca_lo_que_ya_existe(): void
    {
        $this->app['env'] = 'local';
        Persona::create(['ci' => '8000001', 'primer_nombre' => 'Existente', 'apellido_paterno' => 'Previa']);

        $this->artisan('db:seed', ['--class' => DemoSeeder::class])->assertSuccessful();
        $this->artisan('db:seed', ['--class' => DemoSeeder::class])->assertSuccessful();

        $this->assertSame(4, Usuario::count(), 'administrador, médico y dos pacientes; la recepcionista se omite porque su CI ya existía');
        $this->assertSame('Existente', Persona::where('ci', '8000001')->firstOrFail()->primer_nombre);
        $this->assertNull(Persona::where('ci', '8000001')->firstOrFail()->usuario, 'no se le crea cuenta a una persona que ya existía');
        $this->assertSame(1, Persona::where('ci', '8000002')->count());
    }
}
