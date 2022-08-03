<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;

class Merchant_Graph implements FromArray, WithEvents, WithTitle
{
    protected $data_arr;

    public function __construct(array $data_arr, $footer, $attribute)
    {
        $this->data_arr = $data_arr;
        $this->footer = $footer + 4;
        $this->attribute = $attribute;
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
               $cellHeader = 'A4:B4'; // Header
               $cellDollar = 'B5:C3000'; // dollar

              if ($this->attribute == 8 || $this->attribute == 6 || $this->attribute == 5) {
                  // checking
              } else {
                  $event->sheet->getDelegate()->removeColumnByIndex(1);
              }
          $event->sheet->getDelegate()->getStyle($cellDollar)->getNumberFormat()->setFormatCode('[$$-C09]#,##0.00;[RED]-[$$-C09]#,##0.00');
          $event->sheet->getDelegate()->getStyle('B1:B2')->getNumberFormat()->setFormatCode('[$$-C09]#,##0.00;[RED]-[$$-C09]#,##0.00');
          $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
          $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
               ->getStartColor()->setARGB('FFFFFF');
          $event->sheet->getDelegate()->getStyle($cellHeader)->getFill()
               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
               ->getStartColor()->setARGB('D3D3D3');
          $event->sheet->getDelegate()->getStyle($cellHeader)
               ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
          $event->sheet->getDelegate()->getStyle($cellRange)
               ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
          $event->sheet->getDelegate()->getStyle('A'.$this->footer.':B'.$this->footer.'')
               ->getBorders()
               ->getTop()
               ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
          $event->sheet->getStyle($cellRange)->getAlignment()->setHorizontal('left');
          $event->sheet->getStyle($cellHeader)->getAlignment()->setHorizontal('left');
          $event->sheet->getColumnDimension('A')->setWidth(46);
          $event->sheet->getColumnDimension('B')->setWidth(26);
          $event->sheet->getDelegate()->getStyle($cellDollar)->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF');
          $event->sheet->getDelegate()->getStyle('B1:B2')->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()->setARGB('FFFFFF');
          $event->sheet->getDelegate()->getStyle($cellDollar)
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
          $event->sheet->getDelegate()->getStyle('B1:B2')
                ->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_LEFT);
          $sumcellto = 'B'.($this->footer - 1);
          $event->sheet->getDelegate()->setCellValue('B'.($this->footer), '=SUM(B5:'.$sumcellto.')');
          $total_val = $event->sheet->getDelegate()->getCell('B'.($this->footer))->getCalculatedValue();
          $event->sheet->getDelegate()->setCellValue('B'.($this->footer), $total_val);
          $event->sheet->getDelegate()->setCellValue('B1', $total_val);
          if ($total_val) {
              $avg = $total_val / ($this->footer - 5);
          } else {
              $avg = 0;
          }
          $event->sheet->getDelegate()->setCellValue('B2', $avg);
      },
           ];
    }

    public function title(): string
    {
        return 'Merchant Graph';
    }
}
