<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class OptionalModuleAuthorizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', 'auth', 'permission:supervisors.access', 'module.enabled:erp_supervisores'])
            ->get('/_authorization/optional/supervisors', fn () => response('ok'));

        Route::middleware(['web', 'auth', 'permission:custodians.access', 'module.enabled:erp_custodios'])
            ->get('/_authorization/optional/custodians', fn () => response('ok'));
    }

    public function test_enabled_supervisor_module_only_allows_supervisor_and_administrator(): void
    {
        config(['modules.disabled.erp_supervisores' => false]);

        $this->assertRoleResponse('Supervisor', '/_authorization/optional/supervisors', 200);
        $this->assertRoleResponse('Administrador', '/_authorization/optional/supervisors', 200);
        $this->assertRoleResponse('Guardia', '/_authorization/optional/supervisors', 403);
        $this->assertRoleResponse('Operaciones', '/_authorization/optional/supervisors', 403);
    }

    public function test_enabled_custodians_module_only_allows_custodian_and_administrator(): void
    {
        config(['modules.disabled.erp_custodios' => false]);

        $this->assertRoleResponse('Custodios', '/_authorization/optional/custodians', 200);
        $this->assertRoleResponse('Administrador', '/_authorization/optional/custodians', 200);
        $this->assertRoleResponse('Guardia', '/_authorization/optional/custodians', 403);
        $this->assertRoleResponse('Supervisor', '/_authorization/optional/custodians', 403);
    }

    public function test_disabled_modules_reject_even_their_authorized_roles(): void
    {
        config([
            'modules.disabled.erp_supervisores' => true,
            'modules.disabled.erp_custodios' => true,
        ]);

        $this->assertRoleResponse('Supervisor', '/_authorization/optional/supervisors', 403);
        $this->assertRoleResponse('Custodios', '/_authorization/optional/custodians', 403);
        $this->assertRoleResponse('Administrador', '/_authorization/optional/supervisors', 403);
        $this->assertRoleResponse('Administrador', '/_authorization/optional/custodians', 403);
    }

    public function test_real_optional_module_routes_keep_both_security_layers(): void
    {
        $this->assertControllerRoutesHaveMiddleware('SupervisorController', [
            'permission:supervisors.access', 'module.enabled:erp_supervisores',
        ]);
        $this->assertControllerRoutesHaveMiddleware('CustodiosController', [
            'permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios',
        ]);

        $this->assertRouteHasMiddleware('admin.verTableroSupervisores', [
            'permission:supervisors.access', 'module.enabled:erp_supervisores',
        ]);
        $this->assertRouteHasMiddleware('admin.custodiosDashboard', [
            'permission:custodians.access', 'custodios.role', 'module.enabled:erp_custodios',
        ]);
    }

    private function assertRoleResponse(string $role, string $uri, int $status): void
    {
        $user = (new User())->forceFill([
            'id' => abs(crc32($role.$uri)),
            'name' => "Prueba $role",
            'email' => strtolower(str_replace(' ', '.', $role)).'@example.test',
            'rol' => $role,
        ])->setRelation('solicitudAlta', null);

        $this->actingAs($user)->get($uri)->assertStatus($status);
    }

    /** @param list<string> $middleware */
    private function assertRouteHasMiddleware(string $routeName, array $middleware): void
    {
        $route = Route::getRoutes()->getByName($routeName);

        $this->assertNotNull($route, "No se encontró la ruta $routeName.");

        foreach ($middleware as $item) {
            $this->assertContains($item, $route->gatherMiddleware(), "$routeName no contiene $item.");
        }
    }

    /** @param list<string> $middleware */
    private function assertControllerRoutesHaveMiddleware(string $controller, array $middleware): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_contains($route->getActionName(), $controller));

        $this->assertNotEmpty($routes, "No se encontraron rutas para $controller.");

        foreach ($routes as $route) {
            foreach ($middleware as $item) {
                $this->assertContains(
                    $item,
                    $route->gatherMiddleware(),
                    "{$route->getName()} no contiene $item.",
                );
            }
        }
    }
}
