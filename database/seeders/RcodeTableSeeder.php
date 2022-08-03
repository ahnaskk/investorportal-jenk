<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RcodeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id' => '1', 'code' => 'R01', 'description' => 'Insufficient Funds'],
            ['id' => '2', 'code' => 'R02', 'description' => 'Account Closed'],
            ['id' => '3', 'code' => 'R03', 'description' => 'No Account/Unable to Locate Account'],
            ['id' => '4', 'code' => 'R04', 'description' => 'Invalid Account Number'],
            ['id' => '5', 'code' => 'R05', 'description' => 'Unauthorized Debit to Consumer Account Using Corporate SEC Code'],
            ['id' => '6', 'code' => 'R06', 'description' => 'Returned per ODFI Request'],
            ['id' => '7', 'code' => 'R07', 'description' => 'Authorization Revoked by Customer'],
            ['id' => '8', 'code' => 'R08', 'description' => 'Payment Stopped'],
            ['id' => '9', 'code' => 'R09', 'description' => 'Uncollected Funds'],
            ['id' => '10', 'code' => 'R10', 'description' => "Customer Advises Originator is Not Known to Receiver and/or Originator is Not Authorized by Receiver to Debit Receiver's Account"],
            ['id' => '11', 'code' => 'R11', 'description' => 'Customer Advises Entry Not in Accordance with the Terms of the Authorization'],
            ['id' => '12', 'code' => 'R12', 'description' => 'Branch Sold to Another DFI'],
            ['id' => '13', 'code' => 'R13', 'description' => 'RDFI Not Qualified to Participate'],
            ['id' => '14', 'code' => 'R14', 'description' => 'Account Holder Deceased'],
            ['id' => '15', 'code' => 'R15', 'description' => 'Beneficiary Deceased'],
            ['id' => '16', 'code' => 'R16', 'description' => 'Account Frozen'],
            ['id' => '17', 'code' => 'R17', 'description' => 'RDFI Edit Criteria'],
            ['id' => '18', 'code' => 'R20', 'description' => 'Non-Transaction Account'],
            ['id' => '19', 'code' => 'R21', 'description' => 'Invalid Company Identification'],
            ['id' => '20', 'code' => 'R22', 'description' => 'Invalid Individual ID Number'],
            ['id' => '21', 'code' => 'R23', 'description' => 'Credit Refused by Receiver'],
            ['id' => '22', 'code' => 'R24', 'description' => 'Duplicate Entry'],
            ['id' => '23', 'code' => 'R26', 'description' => 'Mandatory Field Error'],
            ['id' => '24', 'code' => 'R27', 'description' => 'Trace Number Error'],
            ['id' => '25', 'code' => 'R28', 'description' => 'Routing Check Digit Error'],
            ['id' => '26', 'code' => 'R29', 'description' => 'Corporate Customer Advises Not Authorized'],
            ['id' => '27', 'code' => 'R30', 'description' => 'RDFI Not Participate in Check Truncation Program'],
            ['id' => '28', 'code' => 'R31', 'description' => 'Permissible Return Entry'],
            ['id' => '29', 'code' => 'R32', 'description' => 'RDFI Non-Settlement'],
            ['id' => '30', 'code' => 'R34', 'description' => 'Limited Participation DFI'],
            ['id' => '31', 'code' => 'R35', 'description' => 'Return of Improper Debit Entry'],
            ['id' => '32', 'code' => 'R36', 'description' => 'Return of Improper Credit Entry'],
            ['id' => '33', 'code' => 'R37', 'description' => 'Source Document Presented for Payment'],
            ['id' => '34', 'code' => 'R38', 'description' => 'Stop Payment on Source Document'],
            ['id' => '35', 'code' => 'R39', 'description' => 'Improper Source Document/Source Document Presented for Payment'],
            ['id' => '40', 'code' => 'R50', 'description' => 'State Law Affecting RCK Acceptance'],
            ['id' => '41', 'code' => 'R51', 'description' => 'Item is Ineligible, Notice not Provided, Signature not Genuine, or Item Altered'],
            ['id' => '42', 'code' => 'R52', 'description' => 'Stop Payment on Item'],
            ['id' => '43', 'code' => 'R53', 'description' => 'Item and A.C.H. Entry Presented for Payment'],
            ['id' => '44', 'code' => 'R54', 'description' => 'Not Currently Used'],
            ['id' => '45', 'code' => 'R61', 'description' => 'Misrouted Return'],
            ['id' => '46', 'code' => 'R18', 'description' => 'Improper Effective Date'],
            ['id' => '47', 'code' => 'R19', 'description' => 'Amount Field Error'],
            ['id' => '48', 'code' => 'R25', 'description' => 'Addenda Error'],
            ['id' => '49', 'code' => 'R33', 'description' => 'Return of XCK Entry'],
            ['id' => '50', 'code' => 'R40', 'description' => 'Non-Participant in ENR Program'],
            ['id' => '51', 'code' => 'R41', 'description' => 'Invalid Transaction Code (ENR)'],
            ['id' => '52', 'code' => 'R42', 'description' => 'Routing Number/Check Digit (ENR)'],
            ['id' => '53', 'code' => 'R43', 'description' => 'Invalid DFI Account (ENR)'],
            ['id' => '54', 'code' => 'R44', 'description' => 'Invalid Individual ID (ENR)'],
            ['id' => '55', 'code' => 'R45', 'description' => 'Invalid Individual Name (ENR)'],
            ['id' => '56', 'code' => 'R46', 'description' => 'Invalid Representative Payee Indicator'],
            ['id' => '57', 'code' => 'R47', 'description' => 'Duplicate Enrollment (ENR)'],
            ['id' => '58', 'code' => 'R67', 'description' => 'Duplicate Return'],
            ['id' => '59', 'code' => 'R68', 'description' => 'Untimely Return'],
            ['id' => '60', 'code' => 'R69', 'description' => 'Field Errors'],
            ['id' => '61', 'code' => 'R70', 'description' => 'Permissible Return Entry Not Accepted'],
            ['id' => '62', 'code' => 'R71', 'description' => 'Misrouted Dishonor Return'],
            ['id' => '63', 'code' => 'R72', 'description' => 'Untimely Dishonored Return'],
            ['id' => '64', 'code' => 'R73', 'description' => 'Timely Original Return'],
            ['id' => '65', 'code' => 'R74', 'description' => 'Corrected Return'],
            ['id' => '66', 'code' => 'R75', 'description' => 'Original Return not a Duplicate'],
            ['id' => '67', 'code' => 'R76', 'description' => 'No Errors Found'],
            ['id' => '68', 'code' => 'R80', 'description' => 'Cross-Border Payment Coding Error'],
            ['id' => '69', 'code' => 'R81', 'description' => 'Non-Participant in Cross-Border Program'],
            ['id' => '70', 'code' => 'R82', 'description' => 'Invalid Foreign Receiving D.F.I. Identification'],
            ['id' => '71', 'code' => 'R83', 'description' => 'Foreign Receiving D.F.I. Unable to Settle'],
            ['id' => '72', 'code' => 'R84', 'description' => 'Entry Not Processed by O.G.O.'],
            ['id' => '73', 'code' => 'R98', 'description' => 'Non Participating Bank'],
            ['id' => '74', 'code' => 'C01', 'description' => 'Incorrect DFI Account Number'],
            ['id' => '75', 'code' => 'C02', 'description' => 'Incorrect Routing Number'],
            ['id' => '76', 'code' => 'C03', 'description' => 'Incorrect Routing Number and Incorrect DFI Account Number'],
            ['id' => '77', 'code' => 'C04', 'description' => 'Incorrect Individual Name (Account Holder Name)'],
            ['id' => '78', 'code' => 'C05', 'description' => 'Incorrect Transaction Code'],
            ['id' => '79', 'code' => 'C06', 'description' => 'Incorrect DFI Account Number and Incorrect Transaction Code'],
            ['id' => '80', 'code' => 'C07', 'description' => 'Incorrect Routing Number, DFI Account Number & Transaction Code'],
            ['id' => '81', 'code' => 'C09', 'description' => 'Incorrect Individual ID Number'],
            ['id' => '82', 'code' => 'C99', 'description' => 'Miscellaneous'],
            ['id' => '83', 'code' => 'IC1', 'description' => 'Rcode Not Found'],
          ];
        DB::table('rcode')->truncate();
        foreach ($data as $single) {
            $single['created_at'] = date('Y-m-d H:i:s');
            $single['updated_at'] = date('Y-m-d H:i:s');
            DB::table('rcode')->insert($single);
        }
    }
}
