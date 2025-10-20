@php use Illuminate\Support\Str; @endphp
@php
    $inicioPeriodoSolicitado = \Carbon\Carbon::parse($user->fecha_ingreso)->addYears($solicitud->periodo);
    $finPeriodoSolicitado = \Carbon\Carbon::parse($user->fecha_ingreso)->addYears($solicitud->periodo+1);
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitud de Vacaciones</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
        }
        h1, h2, h3, p {
            margin: 0;
            padding: 0;
        }
        .titulo-con-logo {
            width: 100%;
            border-bottom: 2px solid #000;
            margin-bottom: 20px;
        }

        .titulo-con-logo td {
            vertical-align: middle;
        }

        .logo {
            width: 80px;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            margin-bottom: 20px;
        }
        .seccion {
            margin-top: 20px;
        }
        .subrayado {
            text-decoration: underline;
        }
        .firma {
            margin-top: 40px;
            text-align: center;
        }
        .bloque {
            border-top: 1px solid black;
            padding-top: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <table class="titulo-con-logo">
        <tr>
            <td class="logo">
                @if($user->empresa === 'PSC')
                    <img src="{{ public_path('images/psc.png') }}" width="70">
                @elseif($user->empresa === 'Montana')
                    <img src="{{ public_path('images/montana.png') }}" width="70">
                @elseif($user->empresa === 'SPYT')
                    <img src="{{ public_path('images/spyt.png') }}" width="100">
                @elseif($user->empresa === 'CPKC')
                @else
                @endif
            </td>
            <td class="titulo">
                    @if($user->empresa === 'PSC')
                        <h3>PRIVATE SECURITY CONTRACTORS DE MEXICO, S.A. DE C.V.</h3>
                    @elseif($user->empresa === 'Montana')
                        <h3>SUMINISTROS COMERCIALES MONTANA, S.A. DE C.V.</h3>
                    @elseif($user->empresa === 'SPYT')
                        <h3>SERVICIOS DE PROTECCION Y TRASLADO, S.A. DE C.V.</h3>
                    @elseif($user->empresa === 'CPKC')
                        <h3>CANADIAN PACIFIC KANSAS CITY</h3>
                    @else
                    @endif
                    <br>
                    <h3>SOLICITUD DE VACACIONES DISFRUTADAS</h3>
            </td>
            <td style="width: 80px;"></td>
        </tr>
    </table>


    <p style="margin-top: 50px;"><strong>NOMBRE:</strong> <span class="subrayado">{{ $user->name }}</span></p><br>
    <p><strong>FECHA DE INGRESO:</strong> {{ \Carbon\Carbon::parse($user->fecha_ingreso)->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}</p>

    <p>
        <strong>SOLICITADAS PARA EL PERIODO Nº{{ $solicitud->periodo }} COMPRENDIDO DEL</strong>
        {{ $inicioPeriodoSolicitado->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}
        <strong>AL</strong>
        {{ $finPeriodoSolicitado->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}
    </p>

    <div class="seccion">
        <p><strong>OBSERVACIONES:</strong></p>
        <p style="text-align: justify;">
            TRABAJADOR DISFRUTARÁ {{ $solicitud->dias_solicitados }}
            @if($solicitud->dias_solicitados == 1)
                DÍA
            @else
                DÍAS
            @endif
            @if(Str::contains($antiguedad, ['MES', 'MESES']))
                <strong>POR ADELANTADO </strong>
            @elseif($solicitud->periodo > $antiguedad)
                <strong>POR ADELANTADO </strong>
            @else
            @endif
            DE VACACIONES DEL PERIODO Nº{{$solicitud->periodo}}.
            ({{ $solicitud->dias_solicitados }}
            @if($solicitud->dias_solicitados == 1)
                DÍA
            @else
                DÍAS
            @endif
            ) (DEL {{ \Carbon\Carbon::parse($solicitud->fecha_inicio)->translatedFormat('d \\d\\e F') }} AL {{ \Carbon\Carbon::parse($solicitud->fecha_fin)->translatedFormat('d \\d\\e F \\d\\e\\l Y') }}) @if($solicitud->turno_doble !== null) <strong>(Turno Doble)</strong> @endif,
            @if($solicitud->dias_disponibles - $solicitud->dias_solicitados >1)
                QUEDANDO {{ $solicitud->dias_disponibles - $solicitud->dias_solicitados}} DÍAS DISPONIBLES DE LAS VACACIONES DEL MISMO PERIODO
            @elseif($solicitud -> dias_disponibles - $solicitud->dias_solicitados == 1)
                QUEDANDO 1 DÍA DISPONIBLE DE LAS VACACIONES DEL MISMO PERIODO
            @else
                QUEDANDO SALDADOS LOS DÍAS DE LAS VACACIONES DEL MISMO PERIODO
            @endif
            Nº{{$solicitud->periodo}}.
        </p>
    </div>

    <div class="firma">
        <p><strong>EL SOLICITANTE</strong></p>
    </div>

    <div class="bloque">
        <p><strong>PARA USO EXCLUSIVO DE RECURSOS HUMANOS</strong></p>
        <p style="margin-top: 40px; text-decoration: underline;"><strong>ANTIGÜEDAD:</strong> {{ $antiguedad }}</p><br><br><br>
    </div>

    <table style="width: 100%; text-align: center;">
        <tr>
            <td>
                @if($user->empresa === 'Montana')
                    <p style="border-top: 1px solid black;">JEFE INMEDIATO</p>
                @else
                    @if(Auth::user()->rol == 'admin' || Auth::user()->empresa == 'Montana')
                        <p style="border-top: 1px solid black;">JEFE INMEDIATO</p>
                    @else
                        <p style="border-top: 1px solid black;">SUPERVISOR EN TURNO</p>
                    @endif
                @endif
            </td>
        </tr>
        <tr style="margin-top: 75px;">
            <td>
                <br><br><br><br>
                <p style="border-top: 1px solid black;">AUTORIZACIÓN DE RECURSOS HUMANOS</p>
            </td>
        </tr>
    </table>
</body>
</html>
