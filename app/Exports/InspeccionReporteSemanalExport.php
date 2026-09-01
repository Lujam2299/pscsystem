<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InspeccionReporteSemanalExport
{
    public function download(array $reporte): StreamedResponse
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Inspecciones validadas');
        $sheet->fromArray([
            'Caso', 'Fecha del reporte', 'Placa', 'Vehículo', 'Evidencias',
            'Inspección', 'Resultado', 'Validada el', 'Revisor',
        ], null, 'A1');

        foreach ($reporte['inspecciones'] as $index => $inspeccion) {
            $sheet->fromArray([
                $inspeccion['caso_id'],
                $inspeccion['fecha_reporte']->format('d/m/Y H:i'),
                $inspeccion['placa'],
                $inspeccion['vehiculo'],
                $inspeccion['evidencias'],
                $inspeccion['inspeccion_id'],
                str($inspeccion['resultado'])->replace('_', ' ')->title()->toString(),
                $inspeccion['validada_at']?->format('d/m/Y H:i'),
                $inspeccion['revisor'],
            ], null, 'A'.($index + 2));
        }

        $sheet->getStyle('A1:I1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:I1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0F766E');
        $sheet->getStyle('A:I')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        foreach (range('A', 'I') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $nombre = sprintf(
            'reporte-evidencias-%s-al-%s.xlsx',
            $reporte['inicio']->format('Ymd'),
            $reporte['fin']->format('Ymd'),
        );

        return response()->streamDownload(function () use ($spreadsheet): void {
            (new Xlsx($spreadsheet))->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $nombre, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
}
