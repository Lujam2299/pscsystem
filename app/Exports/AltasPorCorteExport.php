<?php

namespace App\Exports;

use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
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

        // Formatear fechas
        $inicioFormatted = $this->inicio->translatedFormat('j \d\e F \d\e\l Y');
        $finFormatted = $this->fin->translatedFormat('j \d\e F \d\e\l Y');

        // Encabezado principal
        $sheet->setCellValue('A1', "Altas registradas del periodo del {$inicioFormatted} al {$finFormatted}");
        $sheet->mergeCells('A1:J1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 14,
                'color' => ['rgb' => '2C3E50']
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'ECF0F1']
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER
            ]
        ]);

        // Espacio en blanco
        $row = 3;

        // Cabecera de columnas (ahora con 10 columnas)
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
        foreach ($headers as $header) {
            $sheet->setCellValue("{$col}{$row}", $header);
            $sheet->getStyle("{$col}{$row}")->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '3498DB']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]);
            $col++;
        }

        $row++;

        // Datos
        $usuarios = User::with('solicitudAlta')
            ->whereBetween('fecha_ingreso', [$this->inicio, $this->fin])
            ->orderBy('name')
            ->get();

        foreach ($usuarios as $usuario) {
            $nombreCompleto = trim(
                ($usuario->solicitudAlta->apellido_paterno ?? '') . ' ' .
                ($usuario->solicitudAlta->apellido_materno ?? '') . ' ' .
                ($usuario->solicitudAlta->nombre ?? '')
            );

            $sheet->setCellValue("A{$row}", $usuario->num_empleado ?? '');
            $sheet->setCellValue("B{$row}", $usuario->punto ?? '');
            $sheet->setCellValue("C{$row}", $nombreCompleto);
            $sheet->setCellValue("D{$row}", $usuario->fecha_ingreso ? Carbon::parse($usuario->fecha_ingreso)->format('d/m/Y') : '');
            $sheet->setCellValue("E{$row}", $usuario->solicitudAlta->curp ?? '');
            $sheet->setCellValue("F{$row}", $usuario->solicitudAlta->rfc ?? '');
            $sheet->setCellValue("G{$row}", $usuario->solicitudAlta->nss ?? '');
            $sheet->setCellValue("H{$row}", $usuario->empresa ?? '');
            $sheet->setCellValue("I{$row}", $usuario->solicitudAlta->sd ?? '');
            $sheet->setCellValue("J{$row}", $usuario->solicitudAlta->sdi ?? '');

            // Estilo de fila
            $sheet->getStyle("A{$row}:J{$row}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => 'E0E0E0']
                    ]
                ]
            ]);

            $row++;
        }

        // Autosize
        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ajustar ancho mínimo para algunas columnas
        $sheet->getColumnDimension('C')->setWidth(30); // Nombre
        $sheet->getColumnDimension('E')->setWidth(15); // CURP
        $sheet->getColumnDimension('F')->setWidth(12); // RFC
        $sheet->getColumnDimension('B')->setWidth(12); // Punto

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
