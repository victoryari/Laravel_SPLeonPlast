<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class KardexExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
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
            $mov->cantidad_entrada ? number_format($mov->cantidad_entrada, 2) : '',
            $mov->costo_entrada ? number_format($mov->costo_entrada, 4) : '',
            $mov->total_entrada ? number_format($mov->total_entrada, 2) : '',
            $mov->cantidad_salida ? number_format($mov->cantidad_salida, 2) : '',
            $mov->costo_salida ? number_format($mov->costo_salida, 4) : '',
            $mov->total_salida ? number_format($mov->total_salida, 2) : '',
            $mov->cantidad_saldo ? number_format($mov->cantidad_saldo, 2) : '',
            $mov->costo_promedio ? number_format($mov->costo_promedio, 4) : '',
            $mov->total_saldo ? number_format($mov->total_saldo, 2) : '',
            $mov->observaciones,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'color' => ['argb' => 'FF4F46E5']]],
        ];
    }
}
