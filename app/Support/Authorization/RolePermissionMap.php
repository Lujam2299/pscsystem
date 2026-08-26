<?php

namespace App\Support\Authorization;

use App\Models\User;

final class RolePermissionMap
{
    /** @var list<string> */
    private const COMMON = [
        Permission::MESSAGES_ACCESS,
        Permission::MESSAGES_CREATE,
        Permission::MESSAGES_SEND,
        Permission::VACATIONS_VIEW_OWN,
        Permission::VACATIONS_REQUEST_OWN,
    ];

    /** @return list<string> */
    public static function permissionsFor(User $user): array
    {
        $permissions = match (RoleNormalizer::for($user)) {
            RoleNormalizer::ADMIN => Permission::all(),
            RoleNormalizer::HUMAN_RESOURCES => [
                Permission::HR_ACCESS,
                Permission::HIRES_VIEW,
                Permission::HIRES_CREATE,
                Permission::HIRES_REVIEW,
                Permission::HIRES_APPROVE,
                Permission::HIRES_REJECT,
                Permission::TERMINATIONS_VIEW,
                Permission::TERMINATIONS_CREATE,
                Permission::TERMINATIONS_APPROVE,
                Permission::TERMINATIONS_REJECT,
                Permission::REEMPLOYMENT_MANAGE,
                Permission::EMPLOYEE_FILES_VIEW,
                Permission::EMPLOYEE_FILES_MANAGE,
                Permission::VACATIONS_VIEW_ALL,
                Permission::VACATIONS_REVIEW,
                Permission::VACATIONS_APPROVE,
                Permission::VACATIONS_REJECT,
                Permission::VACATIONS_CANCEL,
                Permission::VACATIONS_VIEW_KARDEX,
                Permission::VACATIONS_EXPORT,
                Permission::ATTENDANCE_VIEW,
                Permission::USERS_VIEW,
                Permission::COMPLAINTS_VIEW,
            ],
            RoleNormalizer::PAYROLL => [
                Permission::PAYROLL_ACCESS,
                Permission::PAYROLL_VIEW,
                Permission::PAYROLL_CALCULATE,
                Permission::PAYROLL_SAVE,
                Permission::PAYROLL_EXPORT,
                Permission::PAYROLL_UPLOAD_FILES,
                Permission::PAYROLL_MANAGE_DEDUCTIONS,
                Permission::PAYROLL_MANAGE_PIECEWORK,
                Permission::PAYROLL_PROCESS_SEVERANCE,
                Permission::ATTENDANCE_VIEW,
                Permission::VACATIONS_VIEW_ALL,
                Permission::VACATIONS_VIEW_KARDEX,
                Permission::USERS_VIEW,
            ],
            RoleNormalizer::IMSS => [
                Permission::IMSS_ACCESS,
                Permission::IMSS_VIEW,
                Permission::IMSS_UPLOAD_RECORDS,
                Permission::IMSS_MANAGE_DISABILITIES,
                Permission::IMSS_MANAGE_WORK_RISKS,
                Permission::IMSS_EXPORT,
                Permission::USERS_VIEW,
                Permission::EMPLOYEE_FILES_VIEW,
            ],
            RoleNormalizer::OPERATIONS => [
                Permission::OPERATIONS_ACCESS,
                Permission::ATTENDANCE_VIEW,
                Permission::ATTENDANCE_CAPTURE,
                Permission::ATTENDANCE_FINALIZE,
                Permission::ABSENCES_JUSTIFY,
                Permission::SPECIAL_PERMISSIONS_MANAGE,
                Permission::TEMPORARY_WORKERS_MANAGE,
                Permission::FOOD_VOUCHERS_CREATE,
                Permission::FOOD_VOUCHERS_UPLOAD_PROOF,
                Permission::USERS_VIEW,
            ],
            RoleNormalizer::ACCOUNTING => [
                Permission::ACCOUNTING_ACCESS,
                Permission::SEVERANCE_CHECKS_MANAGE,
                Permission::FOOD_VOUCHERS_REVIEW,
                Permission::FOOD_VOUCHERS_APPROVE,
                Permission::FOOD_VOUCHERS_REJECT,
                Permission::VOUCHER_PROOFS_REVIEW,
                Permission::ACCOUNTING_EXPORT,
                Permission::TEMPORARY_WORKERS_MANAGE,
            ],
            RoleNormalizer::MONITORING => [Permission::MAP_VIEW],
            RoleNormalizer::LEGAL => [Permission::TERMINATIONS_VIEW],
            RoleNormalizer::SUPERVISOR => [Permission::SUPERVISORS_ACCESS],
            RoleNormalizer::CUSTODIAN => [Permission::CUSTODIANS_ACCESS],
            RoleNormalizer::USER => [],
        };

        return array_values(array_unique([...self::COMMON, ...$permissions]));
    }

    public static function allows(User $user, string $permission): bool
    {
        return in_array($permission, self::permissionsFor($user), true);
    }
}
