<?php

namespace App\Exports;

use App\Models\Eventuales;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;

class RegistrosEventualesExport
{
    protected $search;
    protected $tipo_pago;
    protected $subpunto_id;
    protected $fecha_desde;
    protected $fecha_hasta;

    public function __construct($search, $tipo_pago, $subpunto_id, $fecha_desde, $fecha_hasta)
    {
        $this->search = $search;
        $this->tipo_pago = $tipo_pago;
        $this->subpunto_id = $subpunto_id;
        $this->fecha_desde = $fecha_desde ? Carbon::parse($fecha_desde)->startOfDay() : null;
        $this->fecha_hasta = $fecha_hasta ? Carbon::parse($fecha_hasta)->endOfDay() : null;
    }

    public function generateFile(): BinaryFileResponse
    {
        $fileName = "REGISTROS_EVENTUALES_" . now()->format('Y-m-d_H-i-s') . ".xlsx";
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
        $sheet->setTitle('Registros Eventuales');

        // Encabezado principal
        $titulo = "Reporte de Registros de Eventuales";
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
        $sheet->mergeCells('A1:H1');
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
            'Usuario',
            'Subpunto',
            'Fecha',
            'Turnos',
            'Tipo de Pago',
            'Comprobante',
            'Fecha Registro'
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
        $query = Eventuales::with(['user', 'subpunto'])
            ->orderBy('fecha', 'desc');

        // Aplicar filtros
        if ($this->search) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->tipo_pago) {
            $query->where('tipo_pago', $this->tipo_pago);
        }

        if ($this->subpunto_id) {
            $query->where('subpunto_id', $this->subpunto_id);
        }

        if ($this->fecha_desde) {
            $query->whereDate('fecha', '>=', $this->fecha_desde);
        }
        if ($this->fecha_hasta) {
            $query->whereDate('fecha', '<=', $this->fecha_hasta);
        }

        $registros = $query->get();

        foreach ($registros as $registro) {
            $turnos = is_array($registro->turnos) ? $registro->turnos : [];
            $turnosTexto = implode(', ', array_map('ucfirst', $turnos));

            $sheet->setCellValue("A{$row}", $registro->id);
            $sheet->setCellValue("B{$row}", $registro->user?->name ?? 'N/D');
            $sheet->setCellValue("C{$row}", $registro->subpunto?->nombre ?? 'N/D');
            $sheet->setCellValue("D{$row}", $registro->fecha ? Carbon::parse($registro->fecha)->format('d/m/Y') : '');
            $sheet->setCellValue("E{$row}", $turnosTexto);
            $sheet->setCellValue("F{$row}", $registro->tipo_pago === 'nomina' ? 'Nómina' : 'Efectivo');
            $sheet->setCellValue("G{$row}", $registro->arch_pago ? 'Sí' : 'No');
            $sheet->setCellValue("H{$row}", $registro->created_at ? Carbon::parse($registro->created_at)->format('d/m/Y H:i') : '');

            // Estilo de fila
            $sheet->getStyle("A{$row}:H{$row}")->applyFromArray([
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
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Ajustar ancho mínimo para algunas columnas
        $sheet->getColumnDimension('B')->setWidth(20); // Usuario
        $sheet->getColumnDimension('C')->setWidth(15); // Subpunto
        $sheet->getColumnDimension('E')->setWidth(15); // Turnos
        $sheet->getColumnDimension('H')->setWidth(18); // Fecha Registro

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }
}
