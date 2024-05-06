<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class MoneyBoxExport implements WithEvents
{
    /**
     * @return \Illuminate\Support\Collection
     */
    protected $data;
    protected $encargado;
    protected $director;
    protected $nombreDirector;
    protected $titulo;

    public function __construct($data, $encargado, $nombreDirector, $director, $titulo)
    {
        $this->data = $data;
        $this->encargado = $encargado;
        $this->director = $director;
        $this->nombreDirector = $nombreDirector;
        $this->titulo = $titulo;
    }
    // public function collection()
    // {
    //     return new Collection($this->data);
    // }

    public function registerEvents(): array
    {
        $data = $this->data;
        $encargado = $this->encargado;
        $director = $this->director;
        $nombreDirector = $this->nombreDirector;
        $titulo = $this->titulo;

        return [
            AfterSheet::class => function (AfterSheet $event) use ($data, $encargado, $director, $nombreDirector, $titulo) {

                $event->sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
                $event->sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);

                // Establecer el ancho de la columna D a 100 caracteres
                $event->sheet->getDelegate()->getColumnDimension('A')->setWidth(5);
                $event->sheet->getDelegate()->getColumnDimension('B')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('C')->setWidth(12);
                $event->sheet->getDelegate()->getColumnDimension('D')->setWidth(70);
                $event->sheet->getDelegate()->getColumnDimension('E')->setWidth(20);
                $event->sheet->getDelegate()->getColumnDimension('F')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('G')->setWidth(13);
                $event->sheet->getDelegate()->getColumnDimension('H')->setWidth(13);

                // Agregar imagen a la izquierda
                $drawingLeft = new Drawing();
                $drawingLeft->setName('Logo');
                $drawingLeft->setDescription('Logo');
                $drawingLeft->setPath(public_path('images/Logo UMSA.png'));
                $drawingLeft->setCoordinates('A2');
                $drawingLeft->setHeight(100);
                $drawingLeft->setWorksheet($event->sheet->getDelegate());

                // Agregar imagen a la derecha
                $drawingRight = new Drawing();
                $drawingRight->setName('Logo');
                $drawingRight->setDescription('Logo');
                $drawingRight->setPath(public_path('images/logoTecMed.png'));
                $drawingRight->setCoordinates('H2');
                $drawingRight->setHeight(100);
                $drawingRight->setWorksheet($event->sheet->getDelegate());

                // Dividir la cadena por '\n' para obtener cada parte del texto
                $parts = explode("\n", $titulo);

                // Crear un objeto RichText para manejar el contenido de la celda
                $richText = new RichText();

                // Agregar cada parte del texto al objeto RichText con un salto de línea entre ellas
                foreach ($parts as $index => $part) {
                    if ($index > 0) {
                        $richText->createText("\n");
                    }
                    $richText->createText($part);
                }

                // Agregar título al medio
                $event->sheet->mergeCells('B2:G6');
                $event->sheet->setCellValue('B2', $richText);
                $event->sheet->getStyle('B2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE,
                        ],
                    ],
                ]);

                $event->sheet->getStyle('8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $row = 8; // Fila donde empieza la data
                foreach ($data as $rowData) {
                    $column = 'A';
                    foreach ($rowData as $cellData) {
                        $event->sheet->setCellValue($column . $row, $cellData);
                        $event->sheet->getStyle($column . $row)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                        ]);
                        $column++;
                    }
                    $row++;
                    $event->sheet->getStyle('A8:' . $column . ($row - 1))->getAlignment()->setWrapText(true);
                    $event->sheet->getStyle('A8:' . $column . ($row - 1))->getAlignment()->setVertical('top');
                    $event->sheet->getStyle('A8:' . $column . ($row - 1))->getAlignment()->setHorizontal('left');
                    $event->sheet->getStyle('A8:' . $column . ($row - 1))->getAlignment()->setShrinkToFit(true);
                }

                // Agregar líneas de puntos para la firma a la izquierda
                $leftSignatureRow1 = count($data) + 11; // Primera fila para la firma a la izquierda
                $leftSignatureRow2 = count($data) + 12; // Segunda fila para la firma a la izquierda

                // Agregar líneas de puntos para la firma a la derecha
                $rightSignatureRow1 = count($data) + 11; // Primera fila para la firma a la derecha
                $rightSignatureRow2 = count($data) + 12; // Segunda fila para la firma a la derecha

                // Establecer el texto de las líneas de puntos para la firma a la izquierda
                $responsibleNameLeft = $encargado; // Nombre del responsable para la firma a la izquierda
                $responsiblePositionLeft = 'RESPONSABLE CAJA CHICA'; // Cargo del responsable para la firma a la izquierda

                // Establecer el texto de las líneas de puntos para la firma a la derecha
                $responsibleNameRight = $nombreDirector; // Nombre del responsable para la firma a la derecha
                $responsiblePositionRight = $director; // Cargo del responsable para la firma a la derecha

                // Fusionar celdas para las líneas de puntos para la firma a la izquierda
                $event->sheet->mergeCells('C' . $leftSignatureRow1 . ':D' . $leftSignatureRow1);
                $event->sheet->mergeCells('C' . $leftSignatureRow2 . ':D' . $leftSignatureRow2);

                // Fusionar celdas para las líneas de puntos para la firma a la derecha
                $event->sheet->mergeCells('E' . $rightSignatureRow1 . ':H' . $rightSignatureRow1);
                $event->sheet->mergeCells('E' . $rightSignatureRow2 . ':H' . $rightSignatureRow2);

                // Establecer el valor de las celdas como el texto de las líneas de puntos para la firma a la izquierda
                $event->sheet->setCellValue('C' . $leftSignatureRow1, '............................................................');
                $event->sheet->setCellValue('C' . $leftSignatureRow2, $responsibleNameLeft);
                $event->sheet->setCellValue('C' . ($leftSignatureRow2 + 1), $responsiblePositionLeft);

                // Establecer el valor de las celdas como el texto de las líneas de puntos para la firma a la derecha
                $event->sheet->setCellValue('E' . $rightSignatureRow1, '...........................................................');
                $event->sheet->setCellValue('E' . $rightSignatureRow2, $responsibleNameRight);
                $event->sheet->setCellValue('E' . ($rightSignatureRow2 + 1), $responsiblePositionRight);

                // Aplicar bordes a las celdas de las líneas de puntos para la firma a la izquierda
                $event->sheet->getStyle('C' . $leftSignatureRow1 . ':D' . $leftSignatureRow2)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE, // No hay bordes
                        ],
                    ],
                ]);

                // Aplicar bordes a las celdas de las líneas de puntos para la firma a la derecha
                $event->sheet->getStyle('E' . $rightSignatureRow1 . ':H' . $rightSignatureRow2)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_NONE, // No hay bordes
                        ],
                    ],
                ]);

                // Aplicar negrita al cargo del responsable para la firma a la izquierda
                $event->sheet->getStyle('C' . ($leftSignatureRow2 + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                // Aplicar negrita al cargo del responsable para la firma a la derecha
                $event->sheet->getStyle('E' . ($rightSignatureRow2 + 1))->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);
            },
        ];
    }
}
