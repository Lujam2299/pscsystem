<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GpsOperationalReportExport
{
    public function download(array $report): StreamedResponse
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen GPS');
        $headers = ['Unidad', 'Distancia km', 'Vel. máxima', 'Vel. promedio', 'Horas motor', 'Combustible', 'Viajes', 'Paradas', 'Horas detenida', 'Desconexiones', 'Excesos Traccar', 'Límite km/h', 'Viajes sobre límite'];
        $sheet->fromArray($headers, null, 'A1');

        foreach ($report['rows'] as $index => $row) {
            $sheet->fromArray([
                $row['device_name'], $row['distance_km'], $row['max_speed_kmh'], $row['average_speed_kmh'],
                $row['engine_hours'], $row['spent_fuel'], $row['trips_count'], $row['stops_count'],
                $row['stopped_hours'], $row['offline_events'], $row['overspeed_events'],
                $row['speed_limit_kmh'], $row['trips_over_limit'],
            ], null, 'A'.($index + 2));
        }

        $sheet->getStyle('A1:M1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:M1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3A8A');
        $sheet->getStyle('A:M')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        foreach (range('A', 'M') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, 'reporte-operativo-gps-'.now()->format('Ymd-His').'.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
