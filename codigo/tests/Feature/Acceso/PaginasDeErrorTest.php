<?php

namespace Tests\Feature\Acceso;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\CreaCuentas;
use Tests\TestCase;

/**
 * Las páginas de error salen en español y con el diseño de la clínica, no con las de Laravel.
 */
class PaginasDeErrorTest extends TestCase
{
    use CreaCuentas;
    use RefreshDatabase;

    public function test_403_explica_que_falta_permiso_y_ofrece_volver_al_inicio(): void
    {
        $this->actingAs($this->crearCuenta('RECEPCIONISTA'))
            ->get(route('usuarios.index'))
            ->assertForbidden()
            ->assertSee('Acceso no permitido')
            ->assertSee('no tiene permiso')
            ->assertSee(route('inicio'), false)
            ->assertDontSee('Forbidden');
    }

    public function test_404_avisa_que_la_pagina_no_existe_con_y_sin_sesion(): void
    {
        $this->get('/esta-ruta-no-existe')->assertNotFound()->assertSee('Página no encontrada')->assertDontSee('Not Found');

        $this->actingAs($this->crearCuenta('ADMINISTRADOR'))
            ->get('/esta-ruta-no-existe')
            ->assertNotFound()
            ->assertSee('Página no encontrada')
            ->assertSee('Usuarios'); // conserva el menú del usuario
    }

    public function test_404_cuando_el_registro_no_existe(): void
    {
        $this->actingAs($this->crearCuenta('ADMINISTRADOR'))
            ->get('/usuarios/999999/editar')
            ->assertNotFound()
            ->assertSee('Página no encontrada');
    }

    public function test_419_explica_que_el_formulario_caduco(): void
    {
        // En el entorno de pruebas Laravel no exige el token CSRF; se simula otro entorno para exigirlo.
        $this->app['env'] = 'local';

        $this->post(route('login.autenticar'), ['identificador' => 'x', 'contrasena' => 'y'])
            ->assertStatus(419)
            ->assertSee('La página caducó')
            ->assertSee('30 minutos')
            ->assertDontSee('Page Expired');
    }

    public function test_429_ya_estaba_en_espanol(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('login.autenticar'), ['identificador' => 'nadie'.$i.'@clinica.test', 'contrasena' => 'x']);
        }

        $this->post(route('login.autenticar'), ['identificador' => 'nadie@clinica.test', 'contrasena' => 'x'])
            ->assertStatus(429)
            ->assertSee('Demasiadas solicitudes');
    }

    public function test_500_no_depende_del_menu_ni_de_la_sesion(): void
    {
        Route::get('/_prueba-error-500', fn () => abort(500));

        $this->get('/_prueba-error-500')
            ->assertStatus(500)
            ->assertSee('Ocurrió un error inesperado')
            ->assertDontSee('Server Error')
            ->assertDontSee('Cerrar sesión');
    }
}
