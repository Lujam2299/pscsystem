<?php

use App\Support\Authorization\Permission;

return [
    // Administración de usuarios y panel general.
    'admin.crearUsuarioForm' => Permission::USERS_CREATE,
    'registrarUsuario' => Permission::USERS_CREATE,
    'admin.editarUsuarioForm' => Permission::USERS_UPDATE,
    'admin.darDeBajaUsuario' => Permission::USERS_DISABLE,
    'admin.reingreso' => Permission::USERS_RESTORE,
    'admin.import.unify-duplicates' => Permission::USERS_MERGE_DUPLICATES,
    'admin.verUsuarios' => Permission::USERS_VIEW,
    'admin.verBuzon' => Permission::COMPLAINTS_VIEW,
    'admi.verSolicitudesAltas' => Permission::HIRES_REVIEW,
    'admin.solicitudesVacaciones' => Permission::VACATIONS_VIEW_ALL,
    'admin.verTableroSupervisores' => Permission::SUPERVISORS_ACCESS,
    'admin.custodiosDashboard' => Permission::CUSTODIANS_ACCESS,
    'admin.mapaGeocercas' => Permission::CUSTODIANS_ACCESS,
    'admin.geocercasActivasRealtime' => Permission::CUSTODIANS_ACCESS,
    'admin.detalleMision' => Permission::CUSTODIANS_ACCESS,
    'admin.*' => Permission::ADMIN_DASHBOARD,

    // Módulos opcionales. El permiso se conserva aunque el módulo esté apagado.
    'sup.*' => Permission::SUPERVISORS_ACCESS,
    'solicitud-vacaciones.subir-archivo' => Permission::SUPERVISORS_ACCESS,
    'asistencias.confirmarFaltas' => Permission::SUPERVISORS_ACCESS,
    'asistencias.finalizar' => Permission::SUPERVISORS_ACCESS,
    'custodios.*' => Permission::CUSTODIANS_ACCESS,
    'misiones.*' => Permission::CUSTODIANS_ACCESS,

    // Recursos Humanos, vacaciones y expedientes.
    'rh.*' => Permission::HR_ACCESS,
    'solicitudes.baja.actualizar' => Permission::HR_ACCESS,
    'reingresos.procesar' => Permission::REEMPLOYMENT_MANAGE,
    'kardex-vacaciones' => [Permission::VACATIONS_VIEW_KARDEX, Permission::VACATIONS_VIEW_OWN],
    'kardex.vacaciones' => [Permission::VACATIONS_VIEW_KARDEX, Permission::VACATIONS_VIEW_OWN],
    'api.usuarios.vacaciones' => [Permission::HR_ACCESS, Permission::OPERATIONS_ACCESS],
    'api.usuarios.permisos' => [Permission::HR_ACCESS, Permission::OPERATIONS_ACCESS],
    'api.verificar.asistencia' => [Permission::HR_ACCESS, Permission::OPERATIONS_ACCESS],
    'exportar.altas' => [Permission::HR_ACCESS, Permission::PAYROLL_ACCESS],
    'exportar.altas.corte' => [Permission::HR_ACCESS, Permission::PAYROLL_ACCESS],
    'exportar.bajas' => [Permission::HR_ACCESS, Permission::PAYROLL_ACCESS],
    'exportar.vacaciones*' => [Permission::VACATIONS_EXPORT, Permission::PAYROLL_ACCESS],
    'exportar.asistencias' => [Permission::HR_ACCESS, Permission::PAYROLL_ACCESS],

    // IMSS y cumplimiento documental.
    'aux.*' => Permission::IMSS_ACCESS,
    'documentacion.*' => Permission::IMSS_UPLOAD_RECORDS,
    'confrontas.upload' => Permission::IMSS_UPLOAD_RECORDS,
    'riesgos-trabajo.actualizar' => Permission::IMSS_MANAGE_WORK_RISKS,
    'reporte.incapacidades.pdf' => Permission::IMSS_EXPORT,
    'auxadmin.*' => Permission::IMSS_ACCESS,

    // Nómina.
    'nominas.*' => Permission::PAYROLL_ACCESS,
    'vistaNominas' => Permission::PAYROLL_VIEW,
    'registrarNominas' => Permission::PAYROLL_SAVE,
    'registrarFiniquitos' => Permission::PAYROLL_PROCESS_SEVERANCE,
    'guardar.calculo.finiquito' => Permission::PAYROLL_PROCESS_SEVERANCE,
    'guardarFiniquitoManual' => Permission::PAYROLL_PROCESS_SEVERANCE,
    'finiquitos.archivo' => Permission::PAYROLL_PROCESS_SEVERANCE,
    'crearDeduccion' => Permission::PAYROLL_MANAGE_DEDUCTIONS,
    'guardarDeduccion' => Permission::PAYROLL_MANAGE_DEDUCTIONS,
    'solicitar.constancia' => Permission::PAYROLL_ACCESS,
    'exportar.destajos' => Permission::PAYROLL_EXPORT,
    'updateDestajos' => Permission::PAYROLL_MANAGE_PIECEWORK,
    'importar.excel' => [Permission::VACATIONS_EXPORT, Permission::PAYROLL_ACCESS],
    'importar.personal.activo' => Permission::USERS_IMPORT,

    // Operaciones.
    'operaciones.*' => Permission::OPERATIONS_ACCESS,
    'eventuales.detalles' => [Permission::OPERATIONS_ACCESS, Permission::ACCOUNTING_ACCESS],
    'api.empleados.buscar' => Permission::OPERATIONS_ACCESS,
    'vales.comida.crear' => Permission::FOOD_VOUCHERS_CREATE,
    'vales.comprobantes.formulario' => Permission::FOOD_VOUCHERS_UPLOAD_PROOF,
    'vales.comprobantes.subir' => Permission::FOOD_VOUCHERS_UPLOAD_PROOF,

    // Contabilidad.
    'auxcont.*' => Permission::ACCOUNTING_ACCESS,
    'subir.cheque' => Permission::SEVERANCE_CHECKS_MANAGE,
    'vales.comida.aceptar' => Permission::FOOD_VOUCHERS_APPROVE,
    'vales.comida.rechazar' => Permission::FOOD_VOUCHERS_REJECT,
    'vales.comprobacion.aprobar' => Permission::VOUCHER_PROOFS_REVIEW,
    'vales.comprobacion.rechazar' => Permission::VOUCHER_PROOFS_REVIEW,
    'vales.comprobantes.api' => Permission::VOUCHER_PROOFS_REVIEW,
    'vales.comida.exportar' => Permission::ACCOUNTING_EXPORT,
    'registros.eventuales.exportar' => Permission::ACCOUNTING_EXPORT,

    // Comunicación y mapa.
    'mensajes.*' => Permission::MESSAGES_ACCESS,
    'api.usuarios.buscar' => [Permission::MESSAGES_ACCESS, Permission::USERS_VIEW],
    'monitoreo.mapa' => Permission::MAP_VIEW,
    'monitoreo.unidades-gps.*' => Permission::MAP_VIEW,
    'monitoreo.deducciones' => Permission::MAP_VIEW,
    'vehiculos.*' => Permission::MAP_VIEW,
    'servicios.*' => Permission::MAP_VIEW,
    'servicio.*' => Permission::MAP_VIEW,
    'siniestros.*' => Permission::MAP_VIEW,
    'gastos.*' => Permission::MAP_VIEW,
    'gasolinas.*' => Permission::MAP_VIEW,
    'compras.*' => Permission::MAP_VIEW,
];
