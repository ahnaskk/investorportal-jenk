<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class Data_arrExportPdfCsv implements FromArray, WithEvents, WithDrawings, WithTitle
{
    protected $data_arr;

    public function __construct(array $data_arr, $investor_name, $footer,$common_name=null)
    {
        $this->data_arr = $data_arr;
        $this->investor_nname = $investor_name;
        $this->footer = $footer;
        $this->common_name = $common_name;
    }

    public function array(): array
    {
        return $this->data_arr;
    }

    public function drawings()
    {
        $drawing = new Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('Velosity');
        $drawing->setPath(public_path('/images/velocity_vector_blue_1.png'));
        $drawing->setHeight(150);
        $drawing->setCoordinates('A1');

        return $drawing;
    }

    public function registerEvents(): array
    {
        return [
      AfterSheet::class    => function (AfterSheet $event) {
          $cellRange = 'A1:AMJ1048576'; //
               $cellHeader = 'A9:O9'; // Header
               $event->sheet->getDelegate()->getStyle($cellRange)->getFont()->setSize(11);
          //  $event->sheet->getDelegate()->getStyle($cellRange)->getAlignment()->setWrapText(true);
          $event->sheet->getDelegate()->getStyle($cellRange)->getFill()
               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
               ->getStartColor()->setARGB('FFFFFF');
          $event->sheet->getDelegate()->getStyle($cellHeader)->getFill()
               ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
               ->getStartColor()->setARGB('C0C0C0');

          //->setARGB('171c3e');
          $event->sheet->getDelegate()->getStyle($cellHeader)
               ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
          $event->sheet->getDelegate()->getStyle($cellRange)
               ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_TOP);
          $event->sheet->getDelegate()->getStyle('A'.$this->footer.':N'.$this->footer.'')
               ->getBorders()
               ->getTop();
              // ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
          $event->sheet->getStyle($cellRange)->getAlignment()->setHorizontal('left');
          $event->sheet->getColumnDimension('A')->setWidth(6);
          $event->sheet->getColumnDimension('B')->setWidth(26);
          $event->sheet->getColumnDimension('C')->setWidth(8);
          $event->sheet->getColumnDimension('D')->setWidth(12);
          $event->sheet->getColumnDimension('E')->setWidth(15);
          $event->sheet->getColumnDimension('F')->setWidth(18);
          $event->sheet->getColumnDimension('G')->setWidth(14);
          $event->sheet->getColumnDimension('H')->setWidth(12);
          $event->sheet->getColumnDimension('I')->setWidth(10);
          $event->sheet->getColumnDimension('J')->setWidth(11);
          $event->sheet->getColumnDimension('K')->setWidth(22);
          $event->sheet->getColumnDimension('L')->setWidth(20);
          $event->sheet->getColumnDimension('M')->setWidth(14);
          $event->sheet->getColumnDimension('N')->setWidth(22);
      },
           ];
    }

    public function title(): string
    {
        $arr = explode(' ', trim($this->investor_nname));
        if($this->common_name!=null){
            return ''.$arr[0].' - '.$this->common_name;
        }else{
            return ''.$arr[0].' - Payment Report';
        }
        
    }
}
