<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;
use Maatwebsite\Excel\Writer;

class Data_arrExportDefMerchant implements FromArray, WithEvents, WithTitle, WithCalculatedFormulas
{
    protected $data_arr;

    public function __construct(array $data_arr, $footer)
    {
        $this->data_arr = $data_arr;
        $this->footer = $footer;
    }

    public static function beforeExport(BeforeExport $event, Writer $writer)
    {
        $writer
            ->getDelegate()
            ->getProperties()
            ->setPreCalculateFormulas(true);
    }

    public function array(): array
    {
        return $this->data_arr;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function (AfterSheet $event) {
                $cellRange = 'A1:AMJ1048576'; //
              $cellHeader = 'A1:E1'; // Header
                $cellDollar = 'D2:E30000'; // dollar

               $event->sheet->getDelegate()->getStyle($cellDollar)->getNumberFormat()->setFormatCode('[$$-C09]#,##0.00;[RED]-[$$-C09]#,##0.00');
                $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
                $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF');
                $event->sheet->getDelegate()->getStyle($cellDollar)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF');
                $event->sheet->getDelegate()->getStyle($cellHeader)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('171c3e');
                $event->sheet->getDelegate()->getStyle($cellHeader)
                ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
                $event->sheet->getDelegate()->getStyle($cellRange)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $event->sheet->getDelegate()->getStyle($cellDollar)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
                $event->sheet->getColumnDimension('D')->setWidth(26);
                $event->sheet->getColumnDimension('E')->setWidth(22);
                $event->sheet->getColumnDimension('C')->setWidth(15);
                $event->sheet->getColumnDimension('B')->setWidth(65);
                $event->sheet->getDelegate()->getStyle('A'.$this->footer.':E'.$this->footer.'')
                ->getBorders()
                ->getTop()
                ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                $sumtodefrtr = 'E'.($this->footer - 1);
                $sumtodefinv = 'D'.($this->footer - 1);
                // $event->sheet->getDelegate()
                // ->setCellValue(
                //     'E201',
                //     '=SUM(D2:E2)',
                //     \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_FORMULA
                // );
                $event->sheet->getDelegate()->setCellValue('E'.($this->footer), '=SUM(E2:'.$sumtodefrtr.')');
                $total_def_rtr = $event->sheet->getDelegate()->getCell('E'.($this->footer))->getCalculatedValue();
                $event->sheet->getDelegate()->setCellValue('E'.($this->footer), $total_def_rtr);

                $event->sheet->getDelegate()->setCellValue('D'.($this->footer), '=SUM(D2:'.$sumtodefinv.')');
                $total_def_rtr = $event->sheet->getDelegate()->getCell('D'.($this->footer))->getCalculatedValue();
                $event->sheet->getDelegate()->setCellValue('D'.($this->footer), $total_def_rtr);
            },
        ];
    }

    public function title(): string
    {
        return 'Default Rate Merchant Report';
    }
}
