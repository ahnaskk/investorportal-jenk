<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ActumDeclineCode extends Seeder
{
    public function run()
    {
        DB::table('actum_decline_codes')->truncate();
        $data = [];
        $data[] = ['code'=>'DAR104', 'definition'=>'Account number length > 17'];
        $data[] = ['code'=>'DAR105', 'definition'=>'Account number contains 123456'];
        $data[] = ['code'=>'DAR108', 'definition'=>'Invalid ABA Number'];
        $data[] = ['code'=>'DAR109', 'definition'=>'Invalid Fractional'];
        $data[] = ['code'=>'DCR103', 'definition'=>'Name scrub'];
        $data[] = ['code'=>'DCR105', 'definition'=>'Email blocking'];
        $data[] = ['code'=>'DCR106', 'definition'=>'Previous scrubbed account (Negative BD)'];
        $data[] = ['code'=>'DCR107', 'definition'=>'Recurring Velocity Check Exceeded'];
        $data[] = ['code'=>'DDR101', 'definition'=>'Duplicate Check indicates that this transaction was previously declined'];
        $data[] = ['code'=>'DMR001', 'definition'=>'Invalid merchant'];
        $data[] = ['code'=>'DMR002', 'definition'=>'Invalid billing profile'];
        $data[] = ['code'=>'DMR003', 'definition'=>'Invalid cross sale ID'];
        $data[] = ['code'=>'DMR004', 'definition'=>'Invalid Consumer Unique'];
        $data[] = ['code'=>'DMR005', 'definition'=>'Missing field: processtype, parent_id, mersubid, accttype, consumername, accountname,host_ip, or client_ip'];
        $data[] = ['code'=>'DMR006', 'definition'=>'Payment Type Not Supported'];
        $data[] = ['code'=>'DMR007', 'definition'=>'Invalid Origination Code'];
        $data[] = ['code'=>'DMR104', 'definition'=>'Merchant not authorized for credit'];
        $data[] = ['code'=>'DMR105', 'definition'=>'Invalid or non-matching original order for repeat-order-only subid'];
        $data[] = ['code'=>'DMR106', 'definition'=>'Invalid Amount Passed In'];
        $data[] = ['code'=>'DMR107', 'definition'=>'Invalid Merchant TransID Passed In'];
        $data[] = ['code'=>'DMR109', 'definition'=>'Invalid SysPass or Subid'];
        $data[] = ['code'=>'DMR110', 'definition'=>'Future Initial Billing not authorized for this merchant'];
        $data[] = ['code'=>'DMR201', 'definition'=>'Amount over the per-trans limit'];
        $data[] = ['code'=>'DMR202', 'definition'=>'Amount over daily amount limit'];
        $data[] = ['code'=>'DMR203', 'definition'=>'Count over daily count limit'];
        $data[] = ['code'=>'DMR204', 'definition'=>'Amount over monthly amount limit'];
        $data[] = ['code'=>'DMR205', 'definition'=>'Count over monthly count limit'];
        $data[] = ['code'=>'DOR002', 'definition'=>'A recur has been found for Order'];
        $data[] = ['code'=>'DOR003', 'definition'=>'A return has been found for Order'];
        $data[] = ['code'=>'DOR004', 'definition'=>'Order was not found'];
        $data[] = ['code'=>'DOR005', 'definition'=>'Order is not active.'];
        $data[] = ['code'=>'DOR006', 'definition'=>'The merchant does not match the order'];
        $data[] = ['code'=>'DOR008', 'definition'=>'Could not find original transaction for orderkeyid'];
        $data[] = ['code'=>'DOR009', 'definition'=>'Recur Record not found for keyid'];
        $data[] = ['code'=>'DOR010', 'definition'=>'Multiple transactions found with that TransID'];
        $data[] = ['code'=>'DTA001', 'definition'=>'Consumer identity could not be verified DTE200 Account information could not be verified'];
        DB::table('actum_decline_codes')->insert($data);
    }
}
