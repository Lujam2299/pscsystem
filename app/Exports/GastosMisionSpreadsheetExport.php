<?php

namespace App\Exports;

use App\Models\Gastos;
use App\Models\Misiones;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GastosMisionSpreadsheetExport
{
    private const MONEY_FORMAT = '#,##0.00;-#,##0.00;-';

    private const HEADERS = [
        'RECARGA DE TAG',
        'FECHA',
        'DETALLE / ESPECIFIQUE LOS VARIOS',
        'MISC.',
        'DESGLOSE TAG',
        'PEAJES SIN TAG',
        'ALIMENTOS',
        'PROPINA',
        'GASOLINA',
        'HOTEL',
        'GASOLINA CON TAG',
        'TOTAL',
    ];

    public function download(Misiones $mision): StreamedResponse
    {
        $spreadsheet = $this->createSpreadsheet($mision);
        $fileName = sprintf('GASTOS_MISION_%d_%s.xlsx', $mision->id, now()->format('Y-m-d'));

        return response()->streamDownload(function () use ($spreadsheet): void {
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(true);
            $writer->save('php://output');
            $spreadsheet->disconnectWorksheets();
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function createSpreadsheet(Misiones $mision): Spreadsheet
    {
        $agentesIds = $mision->agentesIdsNormalizados();
        $usuarios = User::query()
            ->whereIn('id', $agentesIds)
            ->get(['id', 'name'])
            ->keyBy('id');

        $gastos = Gastos::query()
            ->whereIn('user_id', $agentesIds)
            ->whereBetween('Fecha', [$mision->fecha_inicio, $mision->fecha_fin])
            ->orderBy('Fecha')
            ->orderBy('Hora')
            ->get();

        $spreadsheet = new Spreadsheet;
        $spreadsheet->getProperties()
            ->setCreator('Private Security Contractors de México')
            ->setTitle("Gastos misión {$mision->id}")
            ->setSubject('Reporte de gastos por agente');

        if (empty($agentesIds)) {
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Sin agentes');
            $sheet->setCellValue('A1', "La misión #{$mision->id} no tiene agentes asignados.");

            return $spreadsheet;
        }

        foreach ($agentesIds as $indice => $agenteId) {
            $sheet = $indice === 0
                ? $spreadsheet->getActiveSheet()
                : $spreadsheet->createSheet();

            $agente = $usuarios->get($agenteId);
            $nombreAgente = $agente?->name ?? "Usuario #{$agenteId}";
            $sheet->setTitle("Agente {$agenteId}");

            $this->construirHoja(
                $sheet,
                $mision,
                $nombreAgente,
                $gastos->where('user_id', $agenteId)->values()
            );
        }

        $spreadsheet->setActiveSheetIndex(0);

        return $spreadsheet;
    }

    private function construirHoja(
        Worksheet $sheet,
        Misiones $mision,
        string $nombreAgente,
        Collection $gastos
    ): void {
        $sheet->setShowGridlines(false);
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_LETTER)
            ->setFitToWidth(1)
            ->setFitToHeight(0);
        $sheet->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.25)
            ->setBottom(0.35)
            ->setLeft(0.25);

        $sheet->mergeCells('D1:L1');
        $sheet->setCellValue('D1', 'PRIVATE SECURITY CONTRACTORS DE MÉXICO S.A. DE C.V.');
        $sheet->getStyle('D1')->getFont()->setBold(true)->setSize(12);

        $sheet->mergeCells('D2:F2');
        $sheet->setCellValue('D2', 'REPORTE DE VIÁTICOS');
        $sheet->setCellValue('G2', 'Fecha Inicio');
        $sheet->mergeCells('H2:I2');
        $sheet->setCellValue('H2', Carbon::parse($mision->fecha_inicio)->format('d/m/Y'));
        $sheet->setCellValue('J2', 'AL');
        $sheet->setCellValue('K2', 'Fecha Fin');
        $sheet->setCellValue('L2', Carbon::parse($mision->fecha_fin)->format('d/m/Y'));
        $sheet->getStyle('D2:L2')->getFont()->setBold(true);
        $sheet->getStyle('G2:L2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('D3', 'SERVICIO A:');
        $sheet->mergeCells('E3:H3');
        $this->setTextoSeguro($sheet, 'E3', $mision->cliente ?: 'Sin cliente registrado');
        $sheet->setCellValue('I3', 'MISIÓN:');
        $sheet->mergeCells('J3:L3');
        $this->setTextoSeguro(
            $sheet,
            'J3',
            trim("#{$mision->id} ".($mision->nombre_clave ?: $mision->tipo_servicio ?: ''))
        );
        $sheet->getStyle('D3')->getFont()->setBold(true);
        $sheet->getStyle('I3')->getFont()->setBold(true);

        foreach (self::HEADERS as $indice => $header) {
            $columna = chr(ord('A') + $indice);
            $sheet->mergeCells("{$columna}5:{$columna}6");
            $sheet->setCellValue("{$columna}5", $header);
        }

        $sheet->getStyle('A5:L6')->applyFromArray([
            'font' => ['bold' => true, 'size' => 8],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
                'wrapText' => true,
            ],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);
        $sheet->getStyle('E5:E6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF200');
        $sheet->getStyle('K5:K6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF200');
        $sheet->getRowDimension(5)->setRowHeight(22);
        $sheet->getRowDimension(6)->setRowHeight(22);

        $primeraFila = 7;
        $filasDatos = max(31, $gastos->count());
        $ultimaFilaDatos = $primeraFila + $filasDatos - 1;

        foreach ($gastos as $indice => $gasto) {
            $fila = $primeraFila + $indice;
            $sheet->setCellValue("B{$fila}", Date::PHPToExcel($gasto->Fecha));
            $this->setTextoSeguro($sheet, "C{$fila}", $this->descripcionGasto($gasto));
            $sheet->setCellValue($this->columnaCategoria($gasto).$fila, (float) $gasto->Monto);
        }

        for ($fila = $primeraFila; $fila <= $ultimaFilaDatos; $fila++) {
            $sheet->setCellValue("L{$fila}", "=SUM(A{$fila},D{$fila}:K{$fila})");
        }

        $sheet->getStyle("A{$primeraFila}:L{$ultimaFilaDatos}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '666666']],
            ],
            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getStyle("B{$primeraFila}:B{$ultimaFilaDatos}")
            ->getNumberFormat()->setFormatCode('m/d/yy');
        $sheet->getStyle("A{$primeraFila}:A{$ultimaFilaDatos}")
            ->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);
        $sheet->getStyle("D{$primeraFila}:L{$ultimaFilaDatos}")
            ->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);

        $filaTotales = $ultimaFilaDatos + 1;
        foreach (['A', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L'] as $columna) {
            $sheet->setCellValue(
                "{$columna}{$filaTotales}",
                "=SUM({$columna}{$primeraFila}:{$columna}{$ultimaFilaDatos})"
            );
        }
        $sheet->getStyle("A{$filaTotales}:L{$filaTotales}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => [
                'top' => ['borderStyle' => Border::BORDER_MEDIUM],
                'bottom' => ['borderStyle' => Border::BORDER_MEDIUM],
            ],
        ]);
        $sheet->getStyle("A{$filaTotales}:L{$filaTotales}")
            ->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);

        $filaResumen = $filaTotales + 3;
        $sheet->mergeCells("A{$filaResumen}:C{$filaResumen}");
        $sheet->setCellValue("A{$filaResumen}", 'CANTIDAD EN TAG');
        $sheet->setCellValue("D{$filaResumen}", "=A{$filaTotales}");
        $sheet->getStyle("A{$filaResumen}:D{$filaResumen}")->getFont()->setBold(true);

        $sheet->mergeCells("I{$filaResumen}:K{$filaResumen}");
        $sheet->setCellValue("I{$filaResumen}", 'SALDO');
        $sheet->setCellValue("L{$filaResumen}", "=L{$filaTotales}");
        $sheet->getStyle("I{$filaResumen}:L{$filaResumen}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ]);

        $filaTotalGastos = $filaResumen + 2;
        $sheet->mergeCells("I{$filaTotalGastos}:K{$filaTotalGastos}");
        $sheet->setCellValue("I{$filaTotalGastos}", 'TOTAL DE GASTOS');
        $sheet->setCellValue("L{$filaTotalGastos}", "=L{$filaTotales}");
        $sheet->getStyle("I{$filaTotalGastos}:L{$filaTotalGastos}")->applyFromArray([
            'font' => ['bold' => true],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM]],
        ]);

        $filaFirma = $filaTotalGastos + 2;
        $sheet->mergeCells("D{$filaFirma}:G{$filaFirma}");
        $sheet->setCellValue("D{$filaFirma}", 'Nombre Agente');
        $sheet->mergeCells('D'.($filaFirma + 1).':G'.($filaFirma + 1));
        $this->setTextoSeguro($sheet, 'D'.($filaFirma + 1), $nombreAgente);
        $sheet->mergeCells("I{$filaFirma}:L{$filaFirma}");
        $sheet->setCellValue("I{$filaFirma}", 'REVISÓ');
        $sheet->getStyle("D{$filaFirma}:L".($filaFirma + 1))->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D'.($filaFirma + 1).':G'.($filaFirma + 1))->getFont()->setBold(true);

        $sheet->getStyle("D{$filaResumen}:L{$filaTotalGastos}")
            ->getNumberFormat()->setFormatCode(self::MONEY_FORMAT);

        $anchos = [
            'A' => 15,
            'B' => 12,
            'C' => 32,
            'D' => 13,
            'E' => 14,
            'F' => 14,
            'G' => 13,
            'H' => 11,
            'I' => 13,
            'J' => 13,
            'K' => 15,
            'L' => 14,
        ];
        foreach ($anchos as $columna => $ancho) {
            $sheet->getColumnDimension($columna)->setWidth($ancho);
        }

        $sheet->freezePane('A7');
        $sheet->getPageSetup()->setPrintArea('A1:L'.($filaFirma + 1));
    }

    private function columnaCategoria(Gastos $gasto): string
    {
        if ($gasto->Tipo === 'Gasolina') {
            return $gasto->Metodo_pago === 'tag' ? 'K' : 'I';
        }

        return match ($gasto->Categoria) {
            'recarga_tag' => 'A',
            'peaje' => $gasto->Metodo_pago === 'tag' ? 'E' : 'F',
            'alimentos' => 'G',
            'propina' => 'H',
            'hotel' => 'J',
            default => 'D',
        };
    }

    private function descripcionGasto(Gastos $gasto): string
    {
        $descripcion = trim((string) $gasto->Descripcion);

        return $descripcion !== '' ? $descripcion : $gasto->categoria_etiqueta;
    }

    private function setTextoSeguro(Worksheet $sheet, string $celda, string $texto): void
    {
        $sheet->setCellValueExplicit($celda, $texto, DataType::TYPE_STRING);
    }
}
