<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\HorarioAtencion;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Datos de demostración para probar el sistema con cada rol y tomar capturas de pantalla.
 *
 * Todas las cuentas comparten una contraseña fija y conocida, así que solo corre en el entorno
 * local: nunca debe existir en una instalación real. No modifica lo que ya existe: una persona
 * con el mismo CI se omite. Se ejecuta con `php artisan db:seed --class=DemoSeeder`.
 */
class DemoSeeder extends Seeder
{
    public const CONTRASENA = 'Demo-2026';

    public function run(): void
    {
        if (! app()->environment('local')) {
            $this->command->error('El DemoSeeder crea cuentas con una contraseña conocida y solo corre con APP_ENV=local.');

            return;
        }

        $creadas = DB::transaction(function () {
            $administrador = $this->cuenta('ADMINISTRADOR', [
                'ci' => '8000005', 'primer_nombre' => 'Ricardo', 'apellido_paterno' => 'Montero',
                'apellido_materno' => 'Cuéllar', 'telefono' => '70010005', 'correo' => 'demo.admin@clinica.test',
            ]);

            $recepcionista = $this->cuenta('RECEPCIONISTA', [
                'ci' => '8000001', 'primer_nombre' => 'Lucía', 'apellido_paterno' => 'Fernández',
                'apellido_materno' => 'Quiroga', 'telefono' => '70010001', 'correo' => 'demo.recepcion@clinica.test',
            ]);

            $medico = $this->medico();

            $paciente = $this->paciente([
                'ci' => '8000003', 'primer_nombre' => 'María', 'segundo_nombre' => 'Fernanda',
                'apellido_paterno' => 'Suárez', 'apellido_materno' => 'Rojas', 'sexo' => 'F',
                'fecha_nacimiento' => '1992-04-18', 'telefono' => '70010003', 'direccion' => 'Av. Banzer 1200, Santa Cruz',
            ], [
                'tipo_sangre' => 'O+', 'alergias' => 'Penicilina', 'enfermedades_cronicas' => 'Ninguna',
                'medicamentos' => 'Ninguno', 'antecedentes' => 'Apendicectomía en 2015',
                'emergencia_nombre' => 'Carlos Suárez', 'emergencia_parentesco' => 'Hermano', 'emergencia_telefono' => '70010099',
            ], verificado: true);

            $pendiente = $this->paciente([
                'ci' => '8000004', 'primer_nombre' => 'Jorge', 'segundo_nombre' => 'Luis',
                'apellido_paterno' => 'Peña', 'apellido_materno' => 'Rivero', 'sexo' => 'M',
                'fecha_nacimiento' => '1985-11-02', 'telefono' => '70010004',
            ], [], verificado: false);

            return array_filter([$administrador, $recepcionista, $medico, $paciente, $pendiente]);
        });

        $this->command->info('Cuentas de demostración (contraseña: la constante DemoSeeder::CONTRASENA):');
        $this->command->table(['Rol', 'Entra con', 'Ficha'], $creadas ?: [['—', 'Ya existían todas las cuentas de demostración', '—']]);
    }

    /** @return array{0: string, 1: string, 2: string}|null */
    private function medico(): ?array
    {
        $datos = [
            'ci' => '8000002', 'primer_nombre' => 'Andrea', 'apellido_paterno' => 'Vaca',
            'apellido_materno' => 'Salvatierra', 'telefono' => '70010002', 'correo' => 'demo.medico@clinica.test',
        ];

        if (Persona::where('ci', $datos['ci'])->exists()) {
            return null;
        }

        $persona = Persona::create($datos);
        $medico = Medico::create(['persona_id' => $persona->id, 'nro_registro_profesional' => 'MP-DEMO-1']);

        $especialidad = Especialidad::firstOrCreate(
            ['nombre' => 'Medicina general'],
            ['descripcion' => 'Consulta médica general', 'duracion_min' => 20],
        );
        $medico->especialidades()->sync([$especialidad->id]);

        $sucursal = Sucursal::firstOrFail();
        foreach ([[1, '08:00', '12:00'], [3, '14:00', '18:00']] as [$dia, $inicio, $fin]) {
            HorarioAtencion::create([
                'medico_id' => $medico->id, 'sucursal_id' => $sucursal->id, 'dia_semana' => $dia,
                'hora_inicio' => $inicio, 'hora_fin' => $fin, 'consultorio' => 'Consultorio 1',
            ]);
        }

        $this->crearUsuario($persona, 'MEDICO');

        return ['Médico', $datos['correo'], 'MED-'.str_pad((string) $medico->id, 6, '0', STR_PAD_LEFT)];
    }

    /** @return array{0: string, 1: string, 2: string}|null */
    private function cuenta(string $rol, array $datos): ?array
    {
        if (Persona::where('ci', $datos['ci'])->exists()) {
            return null;
        }

        $persona = Persona::create($datos);
        $this->crearUsuario($persona, $rol);

        return [ucfirst(strtolower($rol)), $datos['correo'], '—'];
    }

    /** @return array{0: string, 1: string, 2: string}|null */
    private function paciente(array $persona, array $ficha, bool $verificado): ?array
    {
        if (Persona::where('ci', $persona['ci'])->exists()) {
            return null;
        }

        $persona = Persona::create($persona);
        $paciente = Paciente::create(['persona_id' => $persona->id, 'verificado' => $verificado] + $ficha);
        $this->crearUsuario($persona, 'PACIENTE');

        return [
            'Paciente',
            'CI '.$persona->ci,
            'PAC-'.str_pad((string) $paciente->id, 6, '0', STR_PAD_LEFT).($verificado ? ' (verificada)' : ' (pendiente)'),
        ];
    }

    private function crearUsuario(Persona $persona, string $rol): void
    {
        Usuario::create([
            'persona_id' => $persona->id,
            'rol_id' => Rol::where('codigo', $rol)->firstOrFail()->id,
            'password_hash' => self::CONTRASENA,
        ]);
    }
}
