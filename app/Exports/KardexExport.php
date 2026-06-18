<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithColumnFormatting
{
    protected $filtros;

    public function __construct($filtros)
    {
        $this->filtros = $filtros;
    }

    public function collection()
    {
        $query = DB::table('kardex')
            ->join('producto', 'kardex.codigo_producto', '=', 'producto.codigo')
            ->join('almacen', 'kardex.codigo_almacen', '=', 'almacen.codigo_almacen')
            ->where('kardex.codigo_almacen', '!=', 'ALM04')
            ->select('kardex.*', 'producto.descripcion as producto', 'almacen.descripcion as almacen');

        if (!empty($this->filtros['documento'])) {
            $query->where('kardex.documento', $this->filtros['documento']);
        }

        if (!empty($this->filtros['codigo_producto'])) {
            $query->where(function ($q) {
                $q->where('kardex.codigo_producto', 'LIKE', "%{$this->filtros['codigo_producto']}%")
                  ->orWhere('producto.descripcion', 'LIKE', "%{$this->filtros['codigo_producto']}%");
            });
        }

        if (!empty($this->filtros['codigo_almacen'])) {
            $query->where('kardex.codigo_almacen', $this->filtros['codigo_almacen']);
        }

        if (!empty($this->filtros['fecha_desde'])) {
            $query->where('kardex.fecha_movimiento', '>=', $this->filtros['fecha_desde'] . ' 00:00:00');
        }

        if (!empty($this->filtros['fecha_hasta'])) {
            $query->where('kardex.fecha_movimiento', '<=', $this->filtros['fecha_hasta'] . ' 23:59:59');
        }

        return $query->orderBy('kardex.fecha_movimiento', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Producto',
            'Almacén',
            'Doc. Ref.',
            'N° Documento',
            'Tipo',
            'Cant. Entrada',
            'Costo Unit. Ent.',
            'Total Entrada',
            'Cant. Salida',
            'Costo Unit. Sal.',
            'Total Salida',
            'Cant. Saldo',
            'Costo Promedio',
            'Total Saldo',
            'Observaciones'
        ];
    }

    public function map($mov): array
    {
        return [
            \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y H:i'),
            $mov->producto,
            $mov->almacen,
            $mov->documento,
            $mov->numero_documento,
            $mov->tipo_movimiento,
            $mov->cantidad_entrada ? (float) $mov->cantidad_entrada : null,
            $mov->costo_entrada ? (float) $mov->costo_entrada : null,
            $mov->total_entrada ? (float) $mov->total_entrada : null,
            $mov->cantidad_salida ? (float) $mov->cantidad_salida : null,
            $mov->costo_salida ? (float) $mov->costo_salida : null,
            $mov->total_salida ? (float) $mov->total_salida : null,
            $mov->cantidad_saldo ? (float) $mov->cantidad_saldo : null,
            $mov->costo_promedio ? (float) $mov->costo_promedio : null,
            $mov->total_saldo ? (float) $mov->total_saldo : null,
            $mov->observaciones,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_00,
            'H' => '#,##0.0000', // 4 decimales
            'I' => NumberFormat::FORMAT_NUMBER_00,
            'J' => NumberFormat::FORMAT_NUMBER_00,
            'K' => '#,##0.0000', // 4 decimales
            'L' => NumberFormat::FORMAT_NUMBER_00,
            'M' => NumberFormat::FORMAT_NUMBER_00,
            'N' => '#,##0.000000000', // 9 decimales
            'O' => '#,##0.000000000', // 9 decimales
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FF4F46E5']]],
        ];
    }
}
