<?php

namespace Tests\Feature;

use App\Http\Controllers\SupervisorController;
use App\Http\Middleware\EnsureRoutePermission;
use App\Models\User;
use App\Support\Authorization\Permission;
use Illuminate\Support\Facades\Route;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
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
        ], [
            'admin.vacaciones.aceptar', 'admin.vacaciones.rechazar',
            'admin.actualizarUsuario', 'admin.actualizarDocumentacionUsuario',
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

    public function test_administrative_edit_routes_keep_user_permissions_without_supervisor_restrictions(): void
    {
        foreach (['admin.actualizarUsuario', 'admin.actualizarDocumentacionUsuario'] as $name) {
            $route = Route::getRoutes()->getByName($name);
            $this->assertNotNull($route);
            $this->assertRouteHasMiddleware($name, ['web', 'auth']);
            $this->assertContains(EnsureRoutePermission::class, app('router')->gatherRouteMiddleware($route));
            $this->assertSame(Permission::USERS_UPDATE, config('route-permissions')[$name]);
            $this->assertNotContains('permission:supervisors.access', $route->gatherMiddleware());
            $this->assertNotContains('module.enabled:erp_supervisores', $route->gatherMiddleware());
        }
    }

    #[DataProvider('administrativeEditors')]
    public function test_real_edit_routes_authorize_expected_roles_with_supervisor_module_disabled(string $role, bool $allowed): void
    {
        config(['modules.disabled.erp_supervisores' => true]);
        $user = (new User)->forceFill([
            'id' => 123, 'name' => 'Editor de prueba', 'rol' => $role,
        ])->setRelation('solicitudAlta', null);

        // Keep real routing and middleware, but never execute persistence or file uploads.
        $controller = Mockery::mock(SupervisorController::class)->makePartial();
        foreach (['editarInformacionSolicitud', 'subirArchivosEditados'] as $method) {
            if ($allowed) {
                $controller->shouldReceive($method)->once()->andReturn(response('authorized'));
            } else {
                $controller->shouldNotReceive($method);
            }
        }
        $this->app->instance(SupervisorController::class, $controller);

        foreach (['admin.actualizarUsuario', 'admin.actualizarDocumentacionUsuario'] as $name) {
            $response = $this->actingAs($user)->post(route($name, 999));
            if ($allowed) {
                $response->assertOk()->assertSeeText('authorized');
            } else {
                $response->assertForbidden();
            }
        }
    }

    public static function administrativeEditors(): array
    {
        return [
            ['admin', true], ['Administrador', true],
            ['AUXILIAR RH', true], ['AUXILIAR RECURSOS HUMANOS', true],
            ['JEFA RECURSOS HUMANOS', true],
            ['SUPERVISOR', false], ['APOYO SUPERVISOR', false],
            ['GUARDIA', false], ['Operaciones', false], ['MONITORISTA', false],
        ];
    }

    public function test_guests_cannot_submit_administrative_edits(): void
    {
        foreach (['admin.actualizarUsuario', 'admin.actualizarDocumentacionUsuario'] as $name) {
            $this->postJson(route($name, 999))->assertUnauthorized();
        }
    }

    private function assertRoleResponse(string $role, string $uri, int $status): void
    {
        $user = (new User)->forceFill([
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
    private function assertControllerRoutesHaveMiddleware(string $controller, array $middleware, array $excludedRoutes = []): void
    {
        $routes = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($route) => str_contains($route->getActionName(), $controller))
            ->reject(fn ($route) => in_array($route->getName(), $excludedRoutes, true));

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
