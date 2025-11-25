<?php

namespace App\Exports;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat; // Asegúrate que esta línea esté
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;

class AltasPorCorteExport
{
    protected $inicio;
    protected $fin;

    public function __construct($inicio, $fin)
    {
        $this->inicio = Carbon::parse($inicio)->startOfDay();
        $this->fin = Carbon::parse($fin)->endOfDay();
    }

    public function generateFile(): BinaryFileResponse
    {
        $fileName = "ALTAS_" . $this->inicio->format('Y-m-d') . "_al_" . $this->fin->format('Y-m-d') . ".xlsx";
        $tempFilePath = storage_path("app/public/{$fileName}");

        $this->createExcelFile($tempFilePath);

        return response()->download(
            $tempFilePath,
            $fileName,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    private function createExcelFile(string $filePath): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Altas por Corte');

        // Establecer márgenes de página
        $sheet->getPageMargins()
              ->setTop(0.75)
              ->setRight(0.7)
              ->setLeft(0.7)
              ->setBottom(0.75);

        // Formatear fechas
        $inicioFormatted = $this->inicio->translatedFormat('j \d\e F \d\e\l Y');
        $finFormatted = $this->fin->translatedFormat('j \d\e F \d\e\l Y');

        // --- ENCABEZADO PRINCIPAL ---
        $headerRow = 1;
        $endCol = 'J'; // Ajusta según el número de columnas
        $headerCell = "A{$headerRow}";

        $sheet->setCellValue($headerCell, "Altas registradas del periodo del {$inicioFormatted} al {$finFormatted}");
        $sheet->mergeCells("{$headerCell}:{$endCol}{$headerRow}");

        // Aplicar estilo al encabezado principal
        $sheet->getStyle($headerCell)->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 16,
                'color' => ['rgb' => '2C3E50']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D6EAF8'] // Fondo azul claro
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ],
            'borders' => [ // Borde exterior marcado, como prefieres
                'outline' => [
                    'borderStyle' => Border::BORDER_MEDIUM, // Mediano
                    'color' => ['rgb' => '2C3E50']
                ],
            ],
        ]);
        // Ajustar altura de la fila del encabezado
        $sheet->getRowDimension($headerRow)->setRowHeight(25);

        // --- ESPACIO EN BLANCO ---
        $row = 3;

        // --- CABECERA DE COLUMNAS ---
        $headers = [
            'Número de empleado',
            'Punto',
            'Nombre',
            'Fecha de ingreso',
            'CURP',
            'RFC',
            'NSS',
            'Empresa',
            'Salario diario',
            'Salario diario integrado'
        ];

        $col = 'A';
        $headerStartRow = $row;
        foreach ($headers as $header) {
            $cell = "{$col}{$row}";
            $sheet->setCellValue($cell, $header);
            $sheet->getStyle($cell)->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '2980B9'] // Azul oscuro
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '1A5276'] // Borde oscuro para encabezado
                    ]
                ],
            ]);
            $col++;
        }
        $headerEndRow = $row;
        $row++;

        // --- DATOS ---
        $usuarios = User::with('solicitudAlta')
            ->whereBetween('fecha_ingreso', [$this->inicio, $this->fin])
            ->orderBy('name')
            ->get();

        $dataStartRow = $row;
        $dataEndRow = $dataStartRow + count($usuarios) - 1; // Última fila de datos

        $isEvenRow = false; // Para alternar colores
        foreach ($usuarios as $index => $usuario) {
            $nombreCompleto = trim(
                ($usuario->solicitudAlta->apellido_paterno ?? '') . ' ' .
                ($usuario->solicitudAlta->apellido_materno ?? '') . ' ' .
                ($usuario->solicitudAlta->nombre ?? '')
            );

            // Definir colores para la fila
            $bgColor = $isEvenRow ? 'F8F9FA' : 'FFFFFF'; // Gris muy claro y blanco
            $textColor = '2C3E50'; // Gris oscuro para el texto

            $sheet->setCellValue("A{$row}", $usuario->num_empleado ?? '');
            $sheet->setCellValue("B{$row}", $usuario->punto ?? '');
            $sheet->setCellValue("C{$row}", $nombreCompleto);

            // Formato de fecha
            $fechaIngreso = $usuario->fecha_ingreso ? Carbon::parse($usuario->fecha_ingreso)->format('d/m/Y') : '';
            $sheet->setCellValue("D{$row}", $fechaIngreso);
            if ($fechaIngreso) {
                $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
            }

            $sheet->setCellValue("E{$row}", $usuario->solicitudAlta->curp ?? '');
            $sheet->setCellValue("F{$row}", $usuario->solicitudAlta->rfc ?? '');
            $sheet->setCellValue("G{$row}", $usuario->solicitudAlta->nss ?? '');
            $sheet->setCellValue("H{$row}", $usuario->empresa ?? '');

            // Formato de número para salarios
            $sd = $usuario->solicitudAlta->sd ?? 0;
            $sdi = $usuario->solicitudAlta->sdi ?? 0;
            $sheet->setCellValue("I{$row}", $sd);
            $sheet->setCellValue("J{$row}", $sdi);
            // Aplicar formato de moneda *después* de asignar el valor
            $sheet->getStyle("I{$row}:J{$row}")->getNumberFormat()->setFormatCode('[$$-es-MX]#,##0.00'); // Formato de moneda MXN

            // Aplicar estilo a la fila completa
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'font' => [
                    'size' => 11,
                    'color' => ['rgb' => $textColor]
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => $bgColor]
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'E0E0E0']
                    ]
                ],
            ]);

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("I{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
            $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
            $isEvenRow = !$isEvenRow;
        }
        $dataEndRow = $row - 1;

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setWidth(12);
        $sheet->getColumnDimension('C')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(12);
        $sheet->getColumnDimension('E')->setWidth(18);
        $sheet->getColumnDimension('F')->setWidth(14);
        $sheet->getColumnDimension('G')->setWidth(12);
        $sheet->getColumnDimension('H')->setAutoSize(true);
        $sheet->getColumnDimension('I')->setWidth(15);
        $sheet->getColumnDimension('J')->setWidth(18);

        for ($r = $dataStartRow; $r <= $dataEndRow; $r++) {
            $sheet->getRowDimension($r)->setRowHeight(18);
        }

        $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A4);
        $sheet->getPageSetup()->setFitToPage(true);
        $sheet->getPageSetup()->setFitToWidth(1);
        $sheet->getPageSetup()->setFitToHeight(0);

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
