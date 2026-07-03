<?php

namespace Tests\Feature\Custodios;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class CustodiosAccessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'custodios.role'])
            ->get('/_tests/custodios-access', fn () => response('OK'));
    }

    public function test_allowed_roles_are_normalized_and_can_access(): void
    {
        foreach (['CUSTODIOS', 'custodios', ' Custodios ', 'ADMIN', 'admin', ' ADMINISTRADOR '] as $index => $role) {
            $user = (new User)->forceFill([
                'id' => $index + 1,
                'name' => 'Usuario autorizado',
                'email' => "custodios{$index}@example.test",
                'rol' => $role,
            ]);

            $this->actingAs($user)
                ->get('/_tests/custodios-access')
                ->assertOk();
        }
    }

    public function test_other_roles_are_forbidden(): void
    {
        foreach (['GUARDIA', 'AUXILIAR MONITORISTA', 'JEFE', ''] as $index => $role) {
            $user = (new User)->forceFill([
                'id' => $index + 100,
                'name' => 'Usuario no autorizado',
                'email' => "forbidden{$index}@example.test",
                'rol' => $role,
            ]);

            $this->actingAs($user)
                ->get('/_tests/custodios-access')
                ->assertForbidden();
        }
    }

    public function test_guest_is_redirected_to_login_on_real_module_route(): void
    {
        $this->get(route('custodios.nuevaMisionForm'))
            ->assertRedirect(route('login'));
    }

    public function test_all_custodios_routes_have_role_middleware(): void
    {
        $routeNames = [
            'admin.custodiosDashboard',
            'admin.mapaGeocercas',
            'admin.geocercasActivasRealtime',
            'admin.detalleMision',
            'custodios.nuevaMisionForm',
            'custodios.agentesDisponibles',
            'misiones.store',
            'custodios.misiones',
            'custodios.elementos',
            'custodios.historialMisiones',
            'custodios.misionesTerminadas',
            'misiones.edit',
            'misiones.update',
            'misiones.itinerarios.show',
            'misiones.itinerarios.pdf',
            'misiones.gastos.show',
            'misiones.gastos.pdf',
        ];

        foreach ($routeNames as $routeName) {
            $route = Route::getRoutes()->getByName($routeName);

            $this->assertNotNull($route, "No se encontró la ruta {$routeName}.");
            $this->assertContains(
                'custodios.role',
                $route->gatherMiddleware(),
                "La ruta {$routeName} no está protegida.",
            );
        }
    }
}
