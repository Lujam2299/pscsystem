<?php

namespace Tests\Unit;

use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\RoleNormalizer;
use App\Support\Authorization\RolePermissionMap;
use PHPUnit\Framework\TestCase;

class RolePermissionMapTest extends TestCase
{
    public function test_administrator_has_every_catalogued_permission(): void
    {
        $user = $this->userWithRole('Administrador');

        $this->assertSame(RoleNormalizer::ADMIN, RoleNormalizer::for($user));

        foreach (Permission::all() as $permission) {
            $this->assertTrue(RolePermissionMap::allows($user, $permission), $permission);
        }
    }

    public function test_auxiliary_administrator_is_not_treated_as_super_administrator(): void
    {
        $user = $this->userWithRole('Auxiliar Administrativo');

        $this->assertSame(RoleNormalizer::IMSS, RoleNormalizer::for($user));
        $this->assertTrue(RolePermissionMap::allows($user, Permission::IMSS_ACCESS));
        $this->assertFalse(RolePermissionMap::allows($user, Permission::ADMIN_DASHBOARD));
    }

    public function test_known_department_roles_keep_their_existing_access(): void
    {
        $payroll = $this->userWithRole('Auxiliar Nóminas');
        $accounting = $this->userWithRole('Contadora');
        $operations = $this->userWithRole('Operaciones');

        $this->assertTrue(RolePermissionMap::allows($payroll, Permission::PAYROLL_ACCESS));
        $this->assertTrue(RolePermissionMap::allows($accounting, Permission::ACCOUNTING_ACCESS));
        $this->assertTrue(RolePermissionMap::allows($operations, Permission::OPERATIONS_ACCESS));
    }

    public function test_regular_user_only_receives_common_self_service_permissions(): void
    {
        $user = $this->userWithRole('Guardia');

        $this->assertTrue(RolePermissionMap::allows($user, Permission::MESSAGES_ACCESS));
        $this->assertTrue(RolePermissionMap::allows($user, Permission::VACATIONS_REQUEST_OWN));
        $this->assertFalse(RolePermissionMap::allows($user, Permission::USERS_VIEW));
        $this->assertFalse(RolePermissionMap::allows($user, Permission::PAYROLL_ACCESS));
    }

    public function test_optional_module_roles_do_not_receive_each_others_access(): void
    {
        $supervisor = $this->userWithRole('Supervisor');
        $custodian = $this->userWithRole('Custodios');

        $this->assertTrue(RolePermissionMap::allows($supervisor, Permission::SUPERVISORS_ACCESS));
        $this->assertFalse(RolePermissionMap::allows($supervisor, Permission::CUSTODIANS_ACCESS));
        $this->assertTrue(RolePermissionMap::allows($custodian, Permission::CUSTODIANS_ACCESS));
        $this->assertFalse(RolePermissionMap::allows($custodian, Permission::SUPERVISORS_ACCESS));
    }

    private function userWithRole(string $role): User
    {
        return (new User())
            ->forceFill(['rol' => $role])
            ->setRelation('solicitudAlta', null);
    }
}
