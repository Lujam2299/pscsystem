<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Módulos deshabilitados
    |--------------------------------------------------------------------------
    |
    | Estos switches permiten apagar accesos sin eliminar controladores,
    | modelos, tablas ni datos históricos. Los módulos de Supervisores y
    | Custodios del ERP quedan deshabilitados por indicación operativa.
    |
    */
    'disabled' => [
        'erp_supervisores' => true,
        'erp_custodios' => true,
    ],
];
