<?php

namespace App\Exports;

use App\Models\ValesComida;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;

class RegistrosValesComidasExport
{
    protected $search;
    protected $fecha_desde;
    protected $fecha_hasta;
    protected $monto_desde;
    protected $monto_hasta;
    protected $estatus;

    public function __construct($search, $fecha_desde, $fecha_hasta, $monto_desde, $monto_hasta, $estatus)
    {
        $this->search = $search;
        $this->fecha_desde = $fecha_desde ? Carbon::parse($fecha_desde)->startOfDay() : null;
        $this->fecha_hasta = $fecha_hasta ? Carbon::parse($fecha_hasta)->endOfDay() : null;
        $this->monto_desde = $monto_desde;
        $this->monto_hasta = $monto_hasta;
        $this->estatus = $estatus;
    }

    public function generateFile(): BinaryFileResponse
    {
        $fileName = "VALES_COMIDA_" . now()->format('Y-m-d_H-i-s') . ".xlsx";
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
        $sheet->setTitle('Vales de Comida');

        // Encabezado principal
        $titulo = "Reporte de Vales de Comida";
        if ($this->fecha_desde || $this->fecha_hasta) {
            $titulo .= " del ";
            if ($this->fecha_desde) {
                $titulo .= $this->fecha_desde->format('d/m/Y');
            } else {
                $titulo .= "inicio";
            }
            $titulo .= " al ";
            if ($this->fecha_hasta) {
                $titulo .= $this->fecha_hasta->format('d/m/Y');
            } else {
                $titulo .= "hoy";
            }
        }

        $sheet->setCellValue('A1', $titulo);
        $sheet->mergeCells('A1:F1');
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

        // Cabecera de columnas
        $headers = [
            'ID',
            'Fecha',
            'Usuario',
            'Monto Total',
            'No. Elementos',
            'Estatus'
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

        // Consulta con filtros
        $query = ValesComida::with('user')
            ->orderBy('fecha', 'desc');

        // Aplicar filtros
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->fecha_desde) {
            $query->whereDate('fecha', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('fecha', '<=', $this->fecha_hasta);
        }

        if ($this->monto_desde !== null) {
            $query->where('monto', '>=', $this->monto_desde);
        }
        if ($this->monto_hasta !== null) {
            $query->where('monto', '<=', $this->monto_hasta);
        }

        if ($this->estatus) {
            $query->where('estatus', $this->estatus);
        }

        $vales = $query->get();

        foreach ($vales as $vale) {
            $sheet->setCellValue("A{$row}", $vale->id);
            $sheet->setCellValue("B{$row}", $vale->fecha ? Carbon::parse($vale->fecha)->format('d/m/Y') : '');
            $sheet->setCellValue("C{$row}", $vale->user?->name ?? 'N/D');
            $sheet->setCellValue("D{$row}", number_format($vale->monto, 2));
            $sheet->setCellValue("E{$row}", $vale->num_elementos);
            $sheet->setCellValue("F{$row}", $vale->estatus);

            // Estilo de fila
            $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
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
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ajustar ancho mínimo para algunas columnas
        $sheet->getColumnDimension('C')->setWidth(25); // Usuario
        $sheet->getColumnDimension('D')->setWidth(15); // Monto Total
        $sheet->getColumnDimension('F')->setWidth(20); // Estatus

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
