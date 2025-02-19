<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class DownloadVaccination implements FromView, WithStyles
{
    protected $vaccinations;
    public function __construct($records_vaccinations)
    {
        $this->vaccinations = $records_vaccinations;
    }
    /**
    * @return \Illuminate\Support\Collection
    */
    public function view(): View
    {
        return view("vaccinations.download_excel",[
            "vaccinations" => $this->vaccinations,
        ]);
    }

    public function styles($sheet)
    {
        // Estilo para los encabezados (centrado y negrita)
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:H1')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A1:H1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('ccff99');

        // Estilo para el contenido de las celdas (centrado)
        $sheet->getStyle('A2:H' . ($sheet->getHighestRow()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A2:H' . ($sheet->getHighestRow()))->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Aplica bordes a todas las celdas con datos (A1:H hasta la última fila)
        $sheet->getStyle('A1:H' . ($sheet->getHighestRow()))->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Ajuste automático de las columnas
        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true); 
        }
    }
}
