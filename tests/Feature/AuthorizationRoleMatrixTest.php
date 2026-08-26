<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\Authorization\Permission;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AuthorizationRoleMatrixTest extends TestCase
{
    /** @var array<string, string> */
    private const MODULES = [
        'administracion' => Permission::ADMIN_DASHBOARD,
        'usuarios' => Permission::USERS_VIEW,
        'recursos-humanos' => Permission::HR_ACCESS,
        'nominas' => Permission::PAYROLL_ACCESS,
        'imss' => Permission::IMSS_ACCESS,
        'operaciones' => Permission::OPERATIONS_ACCESS,
        'contabilidad' => Permission::ACCOUNTING_ACCESS,
        'monitoreo-mapa' => Permission::MAP_VIEW,
        'supervisores' => Permission::SUPERVISORS_ACCESS,
        'custodios' => Permission::CUSTODIANS_ACCESS,
        'mensajeria' => Permission::MESSAGES_ACCESS,
        'tokens-api' => Permission::TOKENS_MANAGE,
    ];

    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('rol')->nullable();
            $table->rememberToken();
            $table->softDeletes();
            $table->timestamps();
        });

        foreach (self::MODULES as $module => $permission) {
            Route::middleware(['web', 'auth', "permission:$permission"])
                ->get("/_authorization/matrix/$module", fn () => response('ok'));
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    /** @param list<string> $allowedModules */
    #[DataProvider('roleAccessMatrix')]
    public function test_each_role_only_enters_its_expected_modules(string $role, array $allowedModules): void
    {
        $user = User::factory()->create(['rol' => $role]);

        foreach (array_keys(self::MODULES) as $module) {
            $response = $this->actingAs($user)->get("/_authorization/matrix/$module");

            if (in_array($module, $allowedModules, true)) {
                $response->assertOk("$role should access $module");
            } else {
                $response->assertForbidden("$role should not access $module");
            }
        }
    }

    /** @return array<string, array{string, list<string>}> */
    public static function roleAccessMatrix(): array
    {
        return [
            'Administrador' => ['admin', array_keys(self::MODULES)],
            'Recursos Humanos' => ['Recursos Humanos', ['usuarios', 'recursos-humanos', 'mensajeria']],
            'Auxiliar RH' => ['Auxiliar RH', ['usuarios', 'recursos-humanos', 'mensajeria']],
            'Auxiliar Nóminas' => ['Auxiliar Nóminas', ['usuarios', 'nominas', 'mensajeria']],
            'Auxiliar Administrativo' => ['Auxiliar Administrativo', ['usuarios', 'imss', 'mensajeria']],
            'Auxiliar IMSS' => ['Auxiliar IMSS', ['usuarios', 'imss', 'mensajeria']],
            'Operaciones' => ['Operaciones', ['usuarios', 'operaciones', 'mensajeria']],
            'Contadora' => ['Contadora', ['contabilidad', 'mensajeria']],
            'Auxiliar Contabilidad' => ['Auxiliar Contabilidad', ['contabilidad', 'mensajeria']],
            'Monitorista' => ['Monitorista', ['monitoreo-mapa', 'mensajeria']],
            'Jurídico' => ['Jurídico', ['mensajeria']],
            'Supervisor' => ['Supervisor', ['supervisores', 'mensajeria']],
            'Custodios' => ['Custodios', ['custodios', 'mensajeria']],
            'Guardia' => ['Guardia', ['mensajeria']],
            'Rol desconocido' => ['05 DE FEBRERO S/N', ['mensajeria']],
        ];
    }
}
