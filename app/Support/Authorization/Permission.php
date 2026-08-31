<?php

namespace App\Support\Authorization;

final class Permission
{
    public const ADMIN_DASHBOARD = 'admin.dashboard';

    public const TOKENS_MANAGE = 'tokens.manage';

    public const AUDIT_VIEW = 'audit.view';

    public const SUPERVISORS_ACCESS = 'supervisors.access';

    public const CUSTODIANS_ACCESS = 'custodians.access';

    public const USERS_VIEW = 'users.view';

    public const USERS_CREATE = 'users.create';

    public const USERS_UPDATE = 'users.update';

    public const USERS_DISABLE = 'users.disable';

    public const USERS_RESTORE = 'users.restore';

    public const USERS_IMPORT = 'users.import';

    public const USERS_MERGE_DUPLICATES = 'users.merge-duplicates';

    public const COMPLAINTS_VIEW = 'complaints.view';

    public const COMPLAINTS_MANAGE = 'complaints.manage';

    public const HR_ACCESS = 'hr.access';

    public const HIRES_VIEW = 'hires.view';

    public const HIRES_CREATE = 'hires.create';

    public const HIRES_REVIEW = 'hires.review';

    public const HIRES_APPROVE = 'hires.approve';

    public const HIRES_REJECT = 'hires.reject';

    public const TERMINATIONS_VIEW = 'terminations.view';

    public const TERMINATIONS_CREATE = 'terminations.create';

    public const TERMINATIONS_APPROVE = 'terminations.approve';

    public const TERMINATIONS_REJECT = 'terminations.reject';

    public const REEMPLOYMENT_MANAGE = 'reemployment.manage';

    public const EMPLOYEE_FILES_VIEW = 'employee-files.view';

    public const EMPLOYEE_FILES_MANAGE = 'employee-files.manage';

    public const VACATIONS_VIEW_OWN = 'vacations.view-own';

    public const VACATIONS_REQUEST_OWN = 'vacations.request-own';

    public const VACATIONS_VIEW_ALL = 'vacations.view-all';

    public const VACATIONS_REVIEW = 'vacations.review';

    public const VACATIONS_APPROVE = 'vacations.approve';

    public const VACATIONS_REJECT = 'vacations.reject';

    public const VACATIONS_CANCEL = 'vacations.cancel';

    public const VACATIONS_VIEW_KARDEX = 'vacations.view-kardex';

    public const VACATIONS_EXPORT = 'vacations.export';

    public const PAYROLL_ACCESS = 'payroll.access';

    public const PAYROLL_VIEW = 'payroll.view';

    public const PAYROLL_CALCULATE = 'payroll.calculate';

    public const PAYROLL_SAVE = 'payroll.save';

    public const PAYROLL_EXPORT = 'payroll.export';

    public const PAYROLL_UPLOAD_FILES = 'payroll.upload-files';

    public const PAYROLL_MANAGE_DEDUCTIONS = 'payroll.manage-deductions';

    public const PAYROLL_MANAGE_PIECEWORK = 'payroll.manage-piecework';

    public const PAYROLL_PROCESS_SEVERANCE = 'payroll.process-severance';

    public const IMSS_ACCESS = 'imss.access';

    public const IMSS_VIEW = 'imss.view';

    public const IMSS_UPLOAD_RECORDS = 'imss.upload-records';

    public const IMSS_MANAGE_DISABILITIES = 'imss.manage-disabilities';

    public const IMSS_MANAGE_WORK_RISKS = 'imss.manage-work-risks';

    public const IMSS_EXPORT = 'imss.export';

    public const OPERATIONS_ACCESS = 'operations.access';

    public const ATTENDANCE_VIEW = 'attendance.view';

    public const ATTENDANCE_CAPTURE = 'attendance.capture';

    public const ATTENDANCE_FINALIZE = 'attendance.finalize';

    public const ABSENCES_JUSTIFY = 'absences.justify';

    public const SPECIAL_PERMISSIONS_MANAGE = 'special-permissions.manage';

    public const TEMPORARY_WORKERS_MANAGE = 'temporary-workers.manage';

    public const FOOD_VOUCHERS_CREATE = 'food-vouchers.create';

    public const FOOD_VOUCHERS_UPLOAD_PROOF = 'food-vouchers.upload-proof';

    public const ACCOUNTING_ACCESS = 'accounting.access';

    public const SEVERANCE_CHECKS_MANAGE = 'severance-checks.manage';

    public const FOOD_VOUCHERS_REVIEW = 'food-vouchers.review';

    public const FOOD_VOUCHERS_APPROVE = 'food-vouchers.approve';

    public const FOOD_VOUCHERS_REJECT = 'food-vouchers.reject';

    public const VOUCHER_PROOFS_REVIEW = 'voucher-proofs.review';

    public const ACCOUNTING_EXPORT = 'accounting.export';

    public const MESSAGES_ACCESS = 'messages.access';

    public const MESSAGES_CREATE = 'messages.create';

    public const MESSAGES_SEND = 'messages.send';

    public const MAP_VIEW = 'map.view';

    public const INSPECTIONS_VIEW = 'inspections.view';

    public const INSPECTIONS_MANAGE = 'inspections.manage';

    /** @return list<string> */
    public static function all(): array
    {
        return array_values((new \ReflectionClass(self::class))->getConstants());
    }
}
