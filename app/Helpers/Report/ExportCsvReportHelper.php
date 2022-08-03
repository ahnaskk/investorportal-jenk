<?php

namespace App\Helpers\Report;

use App\Merchant;
use App\MerchantUser;
use App\PaymentInvestors;
use App\User;
use FFM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ExportCsvReportHelper
{
    public static function getDownload($columns, $report, $fileName = '')
    {
        $headings = collect($columns)->map(function ($column) {
            if (! empty(optional($column)['title'] ?? '')) {
                return ['name' => optional($column)['name'] ?? '', 'title' => optional($column)['title'] ?? ''];
            }
        })->filter(function ($column) {
            return $column;
        })->pluck('title', 'name')->toArray();
        $fileName = empty($fileName) ? time().'-export' : $fileName;
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename='.Str::slug($fileName).'.csv');
        $fp = fopen('php://output', 'w');
        fputcsv($fp, $headings);
        $total_records = 1;
        $records = optional($report)->original['data'] ?? [];
        foreach ($records as $index => $record) {
            $record = (array) $record;
            $data = [];
            foreach ($headings as $fieldName => $fieldTitle) {
                if ($fieldTitle == 'No') {
                    $data[] = $total_records;
                } elseif (isset($record[$fieldName])) {
                    $data[] = $record[$fieldName];
                } else {
                    $data[] = '';
                }
            }
            $total_records++;
            fputcsv($fp, $data);
        }
        exit;
    }
}
