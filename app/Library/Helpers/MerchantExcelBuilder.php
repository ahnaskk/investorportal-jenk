<?php

namespace App\Library\Helpers;

use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class MerchantExcelBuilder
{
    protected $excel;
    protected $sheet;

    public function __construct($name, $sheetName = 'Data')
    {
        $this->excel = Excel::create($name);
        $this->excel->sheet($sheetName);
        $this->sheet = $this->excel->getSheet();
    }

    public function amortization($data)
    {
        if (count($data) == 5) {
            $this->sheet->fromArray($data[0], null, 'C6', false, false);
            $this->sheet->data = [];
            $this->sheet->fromArray($data[2], null, 'C17', false);
            $this->sheet->data = [];
            $this->sheet->fromArray($data[1], null, 'K6', false, false);
            $this->sheet->data = [];
            $this->sheet->fromArray($data[3], null, 'L19', false, false);
            $this->sheet->cell('L5', function ($cell) {
                $cell->setValue('Per Sched');
                $cell->setFontWeight();
            });
            $this->sheet->cell('M5', function ($cell) {
                $cell->setValue('Per Client');
                $cell->setFontWeight();
            });
            $this->sheet->cell('N5', function ($cell) {
                $cell->setValue('Diffrence');
                $cell->setFontWeight();
            });
            $this->sheet->setFreeze('A18');
            $this->sheet->cell('J3', function ($cell) {
                $cell->setValue('Monthly Interest Totals');
                $cell->setFontWeight();
            });
            $this->sheet->cell('A1', function ($cell) {
                $cell->setValue('Velocity Group');
                $cell->setFontWeight();
            });
            $this->sheet->cell('A2', function ($cell) use ($data) {
                $cell->setValue($data['merchant_name']);
                $cell->setFontWeight();
            });
            $this->sheet->cell('A3', function ($cell) {
                $cell->setValue('Amortization Schedule');
                $cell->setFontWeight();
            });
            $this->sheet->cell('K18', function ($cell) {
                $cell->setValue('Calculated on Revenue Schedule as:');
                $cell->setFontWeight();
            });
            $this->sheet->setWidth('C', 20);
            $this->sheet->setWidth('D', 15);
            $this->sheet->getStyle('C6:D14')->getAlignment()->applyFromArray(['horizontal' => 'right']);
            $this->sheet->setWidth('L', 15);
            $this->sheet->setWidth('M', 15);
            $this->sheet->setWidth('N', 15);
            $this->sheet->getStyle('L6:N14')->getAlignment()->applyFromArray(['horizontal' => 'right']);
            $this->sheet->getStyle('L19:M28')->getAlignment()->applyFromArray(['horizontal' => 'right']);
        }

        return $this->excel;
    }

    public function revenueRecognition($data, $date)
    {
        $this->sheet->cell('A1', function ($cell) {
            $cell->setValue('Velocity Group');
            $cell->setFontWeight();
        });
        $this->sheet->cell('A2', function ($cell) {
            $cell->setValue('Revenue Recognition');
            $cell->setFontWeight();
        });
        $this->sheet->cell('A3', function ($cell) use ($date) {
            $cell->setValue(Carbon::createFromFormat('m/d/Y', $date)->toFormattedDateString());
            $cell->setFontWeight();
        });
        $this->sheet->cell('K6', function ($cell) use ($date) {
            $cell->setValue($date);
        });
        $this->sheet->cell('T6', function ($cell) use ($date) {
            $cell->setValue('Management Fee');
        });
        $this->sheet->mergeCells('T6:V6');
        $this->sheet->cell('T6', function ($cell) use ($date) {
            $cell->setValue('Management Fee');
        });
        $this->sheet->mergeCells('T6:V6');
        $this->sheet->cell('X6', function ($cell) use ($date) {
            $cell->setValue('Syndication Fee');
        });
        $this->sheet->mergeCells('X6:Z6');
        $this->sheet->cell('AB6', function ($cell) use ($date) {
            $cell->setValue('Commission');
        });
        $this->sheet->mergeCells('AB6:AD6');
        $this->sheet->setFreeze('A8');
        if (! empty($data[1])) {
            $this->sheet->fromArray($data[1], null, 'B7', false, true);
        } else {
            $this->sheet->fromArray($data[0], null, 'B7', false, false);
        }
        $this->sheet->getStyle('T6:AD6')->getAlignment()->applyFromArray(['horizontal' => 'center']);
        if (! empty($data[1])) {
            for ($j = 8; $j < count($data[1]) + 8; $j++) {
                $this->sheet->setCellValue("H{$j}", "=F{$j}-G{$j}");
                $this->sheet->setCellValue("K{$j}", '=+$K$6-E'.$j.'+1');
                $this->sheet->setCellValue("M{$j}", "=I{$j}-J{$j}");
                $this->sheet->setCellValue("N{$j}", "=IF(K{$j},ROUNDUP(J{$j}/(M{$j}/K{$j})+K{$j},0),0)");
                $this->sheet->setCellValue("O{$j}", "=N{$j}-K{$j}");
                $this->sheet->setCellValue("P{$j}", "=RATE(N{$j},(-I{$j}/N{$j}),H{$j})");
                $this->sheet->setCellValue("Q{$j}", "=(-(H{$j}+PV(P{$j},O{$j},J{$j}/O{$j})-M{$j}))");
                $this->sheet->setCellValue("R{$j}", "=I{$j}-H{$j}-Q{$j}");
                $this->sheet->setCellValue("U{$j}", "=Q{$j}/(I{$j}-H{$j})*T{$j}");
                $this->sheet->setCellValue("V{$j}", "=T{$j}-U{$j}");
                $this->sheet->setCellValue("Y{$j}", "=Q{$j}/(I{$j}-H{$j})*X{$j}");
                $this->sheet->setCellValue("Z{$j}", "=X{$j}-Y{$j}");
                $this->sheet->setCellValue("AC{$j}", "=Q{$j}/(I{$j}-H{$j})*AB{$j}");
                $this->sheet->setCellValue("AD{$j}", "=AB{$j}-AC{$j}");
            }
        }

        return $this->excel;
    }
}
