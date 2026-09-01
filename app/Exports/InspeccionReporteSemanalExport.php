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
        $sheet->setTitle('Casos confirmados');
        $sheet->fromArray([
            'Caso', 'Primera evidencia', 'Placa', 'Vehículo', 'Evidencias',
            'Inspección', 'Resultado', 'Confirmado el', 'Revisor',
        ], null, 'A1');

        foreach ($reporte['casos'] as $index => $caso) {
            $sheet->fromArray([
                $caso['caso_id'],
                $caso['primera_evidencia_at']->format('d/m/Y H:i'),
                $caso['placa'],
                $caso['vehiculo'],
                $caso['evidencias'],
                $caso['inspeccion_id'],
                str($caso['resultado'])->replace('_', ' ')->title()->toString(),
                $caso['confirmado_at']?->format('d/m/Y H:i'),
                $caso['revisor'],
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
