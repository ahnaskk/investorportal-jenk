<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('template')->truncate();
        $data = [];
        //Merchant status change - (default/active advance for less)
        $data[] = [
            'subject'   => 'Merchant Status changed to [new_status].',
            'temp_code' => 'MCSS',
            'template'  =>  '<tr>'.
                            '<td class="logo" style="text-align:center; border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '<a href="[merchant_view_link]">[merchant_name]</a> Status changed to [new_status].<br>'.
                                '<a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant'.
                                '</a>'.
                            '</td></tr>',
            'title'     =>  'Merchant status change (Default/Default+)',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // Merchant status change - advance completed 100%
        $data[] = [
            'subject'   =>  'Advance completed 100%',
            'temp_code' =>  'MSAC',
            'title'     =>  'Merchant status change (Advance completed 100%)',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center; border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    Dear Velocity,
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center; border:0;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    Merchant <a href="[merchant_view_link]">[merchant_name]</a> Advance completed 100% <br>
                                    <a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant
                                    </a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // Merchant pending payment
        $data[] = [
            'subject'   =>  'Payment Pending',
            'temp_code' =>  'MSPP',
            'title'     =>  'Merchant Payment Pending',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center; border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    Dear Velocity,
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center; border:0;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    Merchant [merchant_name] has payments pending for [days] days <br>
                                    <a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant
                                    </a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Merchant collection status
        // $data[] = [
        //     'subject'   =>  'Collection status added to [merchant_name]',
        //     'temp_code' =>  'MSCS',
        //     'title'     =>  'Merchant status change (Collection)',
        //     'template'  =>  '<tr>
        //                         <td class="logo" style="text-align:center; border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
        //                             Dear Velocity,
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td class="content" style="text-align:center; border:0;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
        //                             A new payment added for [merchant_name]. Status changed to Collection. <br>
        //                             <a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant
        //                             </a>
        //                         </td>
        //                     </tr>',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        //marketplace new deal
        $data[] = [
            'subject'   =>  'New deal',
            'temp_code' =>  'MPLCE',
            'template'  =>  '<tr>'.
                            '<td class="logo" style="border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; " align="center">'.
                                'Dear Investor,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 400;">'.
                                'A new deal has been uploaded to the marketplace. It may be of interest to you.  Please login and have a look.'.
                               ' <br></div>'.
                                '<a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant</a>'.
                           '</td></tr>',
            'title'     =>  'Enable Marketplace - New Deal',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //-ve liquidity email
        $data[] = [
            'subject'   =>  'Liquidity -ve email alert for [investor_name]',
            'temp_code' =>  'LIQAL',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Hello admin,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0;font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '[investor_name] liquidity went negative, and it is [amount]. <br><a href="[action_link]">Click here to view the transaction log.</a>'.
                            '</td></tr>',
            'title'     =>  'Liquidity -ve Alert',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //merchant notes
        $data[] = [
            'subject'   => '[merchant_name] Notes',
            'temp_code' =>  'NOTES',
            'template'  =>  '<tr>'.
                            '<td class="logo" style="border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="border:0; font-size:22px;  line-height: 36px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'A note has been added to the merchant <br><a href="[merchant_view_link]" style="color: #00a762; ">[merchant_name]</a> by [author] on [date_time]'.
                                '<div style="margin: 20px 0 10px ">[note]</div>'.
                                '<a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; " target="_blank">View Merchant Notes</a>'.
                            '</td></tr>',
            'title'     =>  'Merchant Note',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //generate pdf for investor
        $data[] = [
            'subject'   =>  'Payment Report Statement',
            'temp_code' =>  'GPDF',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Hello [investor_name],'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Syndication Report has been generated,please see attachment.'.
                            '</td></tr>',
            'title'     =>  'Generate PDF for Investors',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //generate pdf for investor with recurrence
        $data[] = [
            'subject'   =>  'Syndication Report Statement',
            'temp_code' =>  'GRPDF',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Hello [investor_name],'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '[recurrence_type] syndication report has been generated,please see attachment.'.
                            '</td></tr>',
            'title'     =>  'Generate PDF for Investors with recurrence',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //funding request
        $data[] = [
            'subject'   =>  'Funding request | Velocitygroupusa',
            'temp_code' =>  'FUNDR',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Dear Admin,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '<a  href="[investor_view_link]" target="_blank">[investor_name]</a> Invested [amount] in the merchant <a href="[merchant_view_link]" target="_blank">[merchant_name]</a>'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">'.
                                '<a href="[merchant_view_link]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">View Merchant</a>'.
                            '</td></tr>',
            'title'     =>  'Fund request',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //fund request details
        $data[] = [
            'subject'   =>  'Funding Request Details',
            'temp_code' =>  'FREDT',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Dear [investor_name],'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border: 0; font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'You have successfully invested [amount] in <a href="[merchant_view_link]">[merchant_name]</a> . Thank you for your participation.'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">'.
                                '<a href="[document_url]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold;">View document</a>'.
                            '</td></tr>',
            'title'     =>  'Funding request details',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //marketplace 100% syndicates
        $data[] = [
            'subject'   =>  '100% syndicated',
            'temp_code' =>  'MPSYF',
            'template'  =>  '<tr><td class="logo" style="text-align:center; border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Dear Admin,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="text-align:center; border:0; font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                'Marketplace merchant  <a href="[merchant_view_link]">[merchant_name]</a> reaches 100% syndicated now'.
                            '</td></tr>',
            'title'     =>  'Marketplace 100% syndicated',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //pending payment
        $data[] = [
            'subject'   =>  'Pending Payment',
            'temp_code' =>  'PENDL',
            'template'  =>  '<tr><td class="logo" style="border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="border:0; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '[pending_payment_table]'.
                            '</td></tr>',
            'title'     =>  'Pending payment list',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Deals on Pause
        $data[] = [
            'subject'   =>  'Deals On Pause',
            'temp_code' =>  'DONP',
            'template'  =>  '<tr><td class="logo" style="border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="content" style="border:0; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '[content]'.
                            '</td></tr>',
            'title'     =>  'Deals on pause',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Payment completed percentage (<100)
        //$image_url = url('images/merchant-status.jpg');
        $image_url = 'data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QN/aHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0NSA3OS4xNjM0OTksIDIwMTgvMDgvMTMtMTY6NDA6MjIgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6YjE3NjZhZmEtMGU5Yi1hMDQ5LTg4MTgtZjJhNjgzNzQwYzM4IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkZCODZDQzgxM0MzQTExRUE4OUY3QjUyMDU5QUYyNENGIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkZCODZDQzgwM0MzQTExRUE4OUY3QjUyMDU5QUYyNENGIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOmIxNzY2YWZhLTBlOWItYTA0OS04ODE4LWYyYTY4Mzc0MGMzOCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpiMTc2NmFmYS0wZTliLWEwNDktODgxOC1mMmE2ODM3NDBjMzgiLz4gPC9yZGY6RGVzY3JpcHRpb24+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';
        $data[] = [
            'subject'   =>  '[percentage] % completed by [merchant_name]',
            'temp_code' =>  'PAYC',
            'template'  =>  '<tr><td class="logo" style="border:0;text-align:center;font-size:22px; color: #70748e; line-height:24px; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="logo" style="border:0;text-align:center;ine-height:0pt; padding: 0; background: #fff;">'.
                                    '<img src="'.$image_url.'" style="width: 50%; height: auto;">'.
                            '</td></tr>'.
                            '<tr><td class="content" style="border:0; text-align:center;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 10px 30px 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '<div style="background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777; font-weight: bold; line-height: 36px; padding: 20px 25px 40px;">'.
                                    'The merchant<a href="[merchant_view_link]"> [merchant_name] </a> is'.
                                    '<span style="font-weight: bold; display: block; font-size: 50px; color: #fa5440; margin: 7px 0 0;">'.
                                    '[percentage]%</span> Completed <br>'.
                                    '<a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">View Merchant</a>'.
                                '</div>'.
                            '</td></tr>',
            'title'     =>  'Payment Completed Percentage (<100)',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //payment completed percentage (>100)
       // $image_url = url('images/merchant-status-100.jpg');
       $image_url = 'data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QONaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0NSA3OS4xNjM0OTksIDIwMTgvMDgvMTMtMTY6NDA6MjIgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6YjE3NjZhZmEtMGU5Yi1hMDQ5LTg4MTgtZjJhNjgzNzQwYzM4IiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOkFDM0I2OThFM0Y0QTExRUFBRkMwQ0EzREY2MDMwRTQ0IiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOkFDM0I2OThEM0Y0QTExRUFBRkMwQ0EzREY2MDMwRTQ0IiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAyMDE5IChXaW5kb3dzKSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOjE1MTZhNjE3LThjZTgtNTc0NS04MDAxLTAwNzViZmQ1NDNhNiIgc3RSZWY6ZG9jdW1lbnRJRD0iYWRvYmU6ZG9jaWQ6cGhvdG9zaG9wOjc0ZGQxM2Q4LTFkMDItZjQ0MC1hN2Q0LTgyMzFmY2VjNTBiMCIvPiA8L3Jk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';
        $data[] = [
            'subject'   =>  '[percentage] % completed by [merchant_name]',
            'temp_code' =>  'PAYCO',
            'template'  =>  '<tr><td class="logo" style="border:0;text-align:center;font-size:22px; color: #70748e; line-height:24px; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">'.
                                'Dear Velocity,'.
                            '</td></tr>'.
                            '<tr><td class="logo" style="border:0;text-align:center;ine-height:0pt; padding: 0; background: #fff;">'.
                                    '<img src="'.$image_url.'" style="width: 50%; height: auto;">'.
                            '</td></tr>'.
                            '<tr><td class="content" style="border:0; text-align:center;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 10px 30px 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">'.
                                '<div style="background: #fdfdff; border-radius: 8px; border: 1px solid #eeedf5; color: #535777; font-weight: bold; line-height: 36px; padding: 20px 25px 40px;">'.
                                    'The merchant<a href="[merchant_view_link]"> [merchant_name] </a> is'.
                                    '<span style="font-weight: bold; display: block; font-size: 50px; color: #2db77e; margin: 7px 0 0;">'.
                                    '[percentage]%</span> Completed <br>'.
                                    '<a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold;">View Merchant</a>'.
                                '</div>'.
                            '</td></tr>',
            'title'     =>  'Payment Completed Percentage (>100)',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // Pending payment
        $data[] = [
            'subject'   => 'Pending Payment from [date]',
            'temp_code' =>  'PENDP',
            'template'  =>  '<tr><td class="logo" style="border:0; text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                Dear Velocity,
                            </td></tr>
                            <tr><td class="content" style="border:0;;text-align:center;font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                Merchant <a href="[merchant_view_link]">[merchant_name]</a> has payments pending for <span style="color: #d33724; font-size: 40px;">[days]</span> days.   
                            </td></tr>
                            <tr><td class="content" style="border:0; text-align:center; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">
                                <a href="[merchant_view_link]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">View Merchant</a>
                            </td></tr>',
            'title'     =>  'Pending payment',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //merchant details
        $data[] = [
            'subject'   =>  '[merchant_name] Details',
            'temp_code' =>  'MERD',
            'template'  =>  '<tr><td class="logo" style="border:0;text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear Admin,
                            </td></tr>
                            <tr><td class="content" style="border:0;text-align:center;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                A new merchant called <a href="[merchant_view_link]">[merchant_name]</a> created by [creator] .    
                            </td></tr>
                            <tr><td style="border:0;text-align:center;font-size:22px;">
                                [merchant_details]
                            </td><tr>',
            'title'     => 'Merchant details',
            'type'      => 'email',
            'enable'    =>  1,
            'created_at'=> date('Y-m-d H:i:s'),
        ];
        //Merchant login credentials
        $data[] = [
            'subject'   =>  'Login Credentials for [merchant_name]',
            'temp_code' =>  'MERC',
            'template'  =>  '<tr><td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                Dear Merchants,
                            </td></tr>
                            <tr><td class="content" style="text-align:center;font-size:32px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                Download our app and start tracking your payments today.
                            </td></tr>
                            <tr><td class="download-icons">
                                <table width="100%" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 10px 30px 0; text-align: right; background: #fff">
                                            <a href="[android_view]"><img src="data:image/png;base64,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" width="180" height="59" ></a>
                                        </td>
                                        <td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 0 30px 10px; text-align: left; background: #fff">
                                            <a href="[ios_view]"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALQAAAA7CAYAAADSK6A/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAAFA5JREFUeNrsXX1QlNXbvpbddVdYiAe/2LVindK1AmWl0CSNPqwMJizJ8C2mmZ10pmxogr4/lAlm6JeZ9quhGn1rKaWhosjEIQP5cKBNCERhQdlWkABndd0X2AWW3eV+/8g97bqLqW0K9lwzZ4bdc859PvY693Pu+9znQQBvTBeJRJlOpzMZwDwAEvDgMfFgB9AuFAp3u1yu/wI47a/QagD/B4D4xKdJlPrPchcAIPAg89cen3nwmEwgAI8CKBYAmCEUCg0ulyuMnxcekxUikWjA6XTOFQJ4hYju46eEx2TG2NiYBIBTAOAQgIX8lPC4CnBEAGCE92bwuEowKji7oebB46pAED8FPHhC8+DBE5oHD57QPHjwhObBE3rCIz4+Hh988AG++uorCIXCgMkVi8WQy+V/W05iYiI4jgMApKWlIS0t7bzlFQoFnnnmGVbHEyqVCllZWYiIiLiic65Wq7Fu3bqAzvflwIQPQMnLyyOLxUJEREajkUQiUcBk79q1i8xmMyUlJV2yDJVKRWfOnKFnnnmGAFBlZSVVVlaet05SUhINDQ1RfHy8T97atWvJZrNRTEzMZZ1nmUxGsbGx7PNLL71EZrOZOI6bNMFKE15D5+fn45VXXkF4eDgAoK2tDU6nMyCyFQoF4uLiMDg4iDVr1rDvOY6DWCz20uIymYz9rVKpvDSrwWDA+vXr8d133wEA7HY77HY7y5fJZFCpVEwGAIyNjcFut2N0dBRKpRIhISEsz+FwYGRkBETkJWPevHl/OaaoqCgolUqv79ztymQyREVF+a0nEomwefNmfPzxx2xsLpcLdrsdAoHAR+bF9InX0GdTWloaORwOcsPlclF6enrA5K9bt470ej1t3LiROjs7SSaTEQD64IMPqLy8nABQSEgI6XQ6eu6550gmk1F5eTnpdDpqb2+nzMxMAkAKhYIOHjxIqampBIDKysqorKyMANBtt91GBw8epF9++YXa2tooJSWFANDKlSvJYrFQWVkZ6fV6MhqNrP7q1avJbDYzDZ2WlkaHDx+mhoYGqqyspKioKJ+xiMViKiwspCNHjlB7ezsVFBSQWCwmAKTVaqm0tJR0Oh319fXR1q1bfeqnpKSQyWQiq9VKNTU1JJfLKSMjg0wmE5WWllJ3dzdVV1czbZ2enk7Nzc3U0NBAFRUVpFAoJgpvJi6hq6qqyBOlpaUBlV9SUkJarZY4jqNTp04xsq1evZqGh4cpJiaGHnzwQerv76fo6GgCQLGxsaRSqUir1VJPTw+JRCKKioois9lM69ev9yG0TCajmJgYio6OppqaGqqrqyMAtGLFCnK5XLRlyxZSKpVUVlZGBoOBhEIhI7RKpSKO46izs5MyMzOJ4zhqaGig7du3+92WmUwmio2NpcTERLJarbRp0yYCQHv27CGz2UwJCQn02muv0dDQEBuP53ajqKiImpubSaVSkUgkoueee47sdjtpNBpasWIF2Ww2ysjIYH1y/93U1EQfffTRhOCMaKJuNZRKJRYu/DNmqqqqCk899VTA5HMch8WLF8NkMuGzzz5DcHAwkpOT8f3332P37t3o6enBI488glmzZqG+vh4tLS2QyWR49tlnER0dDYlEAqlUihkzZmB0dBQul8vvVmj69OnIyclBREQE5HI524qIRCL09/dDq9Wis7MTO3bsgFarxaxZs7zk3HrrrZDL5VizZg1WrVqF6dOnQ6FQ+LRz1113obKyEocOHQIA1NfX484772T59fX1qK2thc1mwxtvvIHIyEi0tLSwfKvVisHBQYyMjODo0aOsj6dPn8bnn38Op9OJ33//HXK5HLfeeitmzZqFtLQ0pKamsrFNBExYQs+cORNWqxUnTpzAF198gXfffTeg8pOTkxEcHIydO3diaGgIAHDPPfdALBbD4XDg+++/x6pVqyAWi/H+++8DALKysvDwww8jLi4Od999NzZv3gy73Q6JZPzYrnfffRc33ngjlixZgtzcXNx33x+RugKBAFOmTEFYWBjbz9vtdoyMjPy5FyTC0NAQHA4HPvzwQ7S0tMDpdGJwcNCnneHhYURGRrLPM2bMYMQUCP68txESEuK1Nz93H+1ZlogQFBQEqVQKq9XKZA0NDcHlciE/Px+tra1wOp0YGBjgCa1UKrFmzRrExcVBJpOht7cXFRUVKC4uxsGDB7FgwQJYLBbExsYiLy8ParUaoaGhsFgsaGtrw9dff42DBw9eUtuPP/442trasHnzZgDA4cOHsX//fjz00EMoLi7Gzp07kZGRgd7eXnz55ZfMWJNIJHj00UeRlJQEiUQCiUQCgUAAiUTC3FsSiQQul4sZVtdccw2efvppJCcnM+0bFBSEqVOnIjc3F7W1tdBoNPjxxx9x5swZTJ06FVKpFFOmTIFOp0NjYyMyMzNRVFSE8PBw7NmzB11dXV7j+eSTT7B9+3Zs3boVoaGhmD17Np5//nlmyHoauVKp1Iu4bhw/fhxr1qxBXl4e3nzzTQQFBWHKlCmYMmUKkyOVSqHT6dDS0oKsrCx8+eWX4DgO3333nU+frgSEALKvRMM5OTn45JNPkJKSgltuuQVz587FokWLkJqaisceeww333wzYmJikJOTg40bNyIxMRE33ngjrrvuOsybNw9Lly7F2rVrccMNN2Dv3r0YGxu7qPbnz5+Pb7/9Fnq9HgDQ09MDqVSKEydO4OjRozh58iQEAgH27t0LnU4HAGhsbERYWBiio6OxZ88etLa24sCBAxgeHoZMJsP+/fvR29sLmUyG9vZ21NfXo66uDtdffz2uv/56FBUV4dixYzhw4ACCg4PR09ODxsZGLF++HBUVFXjhhRcwOjrKFsL+/fsxMDCAsrIyKBQKLFiwAAKBAHV1dTh58qTXeFpaWnD8+HEsXboUAoEAmzZtwo8//ggACAsLg16vR319PZxOJ8RiMSoqKmA2m71kHD58GNOmTUNkZCRKSkoQFBSEwcFB7N27F0SE8PBw6HQ6tLW1obS0FHK5HAsXLoRAIMCBAwdgMpn+nV6OwsJCCiT27dtHISEh/IVRPl1+P3ROTg7Wrl0b2H2TSIRrrrmGP/flcXk1tEqlIrPZHFDtvGfPnoCeHPKJ19AXjCeeeCKg8QknTpzA+vXrA3ZyyGPy47J6OZYtWxZQeYWFhejt7Q2IrPj4eIjFYgQFBaGxsRE2m23C/VgqlQpqtRoKhQIjIyPo7OzEzz//DIvFwjP5cm85xGIxGY3GgG017HY7JSYmBqRvsbGxZDabaXh4mIaGhuj111+fUI9RmUxGWq2WBWh5wmg0XvYgJn7LcfbEzDM45+9iZGQEp0+fDoislJQUREREQCqVYurUqXjggQcmjLYRi8X45ptv8OSTTyI8PBzHjh1DRUUFfv75Z5w6dQoAYDQa//TDCoVYvnw53n77bcTHx/Ma+p9KcrmcTCZTwDS0zWbzG3p5Kamuro6IiMrLy8nhcJDVavWJdbhSKT09nYiIxsbGaOvWrSzgCABFRUXRihUrfIKMRkZGiIhYbAqvof8BnD59OqD7UqlU6hXrcalQq9VYsGABXC4XCgoKYLFYEBISgpSUFL/lZTIZOI6DSCRiGlGtViM+Pn7ceAaO47yMYY7jEB8fD7VazeScb28PABaLBZs3b4bD4WB5XV1d+Omnn7z6Nm3aNHYULxKJwHHcuE/GqKgoJCQkIDY2dtwy7vG6T0HlcjmzN85FdHQ0EhMToVKp/h1uO51OF1CX3f79+/92n15//XUiIurq6iKO46impoaIiKqqqnzKCoVCKi8vp87OTtJoNLR27VpqaWkhh8NBLpeLuru7adu2bV5aNDo6mjo6Oqi9vZ1UKhXl5eVRT08PjY2Nkd1uJ71eT+vWrRu3f1u2bGE2gzu81F8SiURUU1Pj9RTs6+uj7u5uamhoYKGxACg5OZkqKyupv7+fiIgcDgcZDAZ65513vPouFAqpqqqKOjs7KT09nTIzM6mnp8fn6fjEE09QU1MTOZ1O9vSsqqqi5cuXX93howUFBQEltMvloqysrL/VJzeBv/rqKwJAmzZtIiKigYEBUqlUPuWbm5uJiKiuro6GhobIZrNRR0cHWa1W1q9du3ax8mq1mux2OzkcDqqsrCQiIpPJRMePH6exsTFGVvdtl3NTYmIiDQ0NMYJmZWV5kdPT6NbpdF6GY39/P505c4b0ej2LY9ZoNGSz2Vg/ysvLSa/XszplZWVepG5vbycioqKiIrLb7WwBJCQkEADasGED2+JUVVVRYWEhdXV1sf5eAYP18jW2bt06CjRsNhsLtL+Ugx63ltJoNASA4uPj2Q/+4osv+tRpaGhgbdfU1JBarfYK5Hfvd93aNDo6mrVBRPTRRx+RQqEgkUhEaWlpdObMGSIi6unpGTdIPjc31+uiQ0dHB+Xl5fmU5ziONBoNK5eenk4KhYLkcjkbb19fHxERNTU1kVKpZIthx44drJ47jtpzAbtcLjIajZSRkUErV64kmUzmdVCWl5fH6iiVSmpra/NSFFcloRUKRUANQ0+UlJR4aZYLSS+++CIjk6fWq62tZUbieNsmdwC+Z96yZcvYYvjmm28IAMXExDBC63Q6H3kbN25kY9iwYcO4fdVoNNTS0uLjslu9erVXudTUVJZ/7j3JvLw89kQ4N08mk5HBYCAiIr1eT0Kh0IvQNpvNx03qltfS0uLT3xdeeIGIiLq7uy/rncTLelLY29uL8vLyf0R2aGiol8F0Ibj//vsBAH19fVi1ahU0Gg00Gg1zhy1atGhcA8dgMLB4YzcOHDiAjo4OAMANN9zwh4HiEXtcW1vrI+eHH35g8djz588ft6+ffvop1Go10tPTUVFRASLCnDlz8NlnnyEhIcHLzcdOzc4xOG+77TYAQHd3N/bt2+eVZ7VaWSju7NmzcfPNN3vlt7e3o6qqyq+84OBgaLVaFBYWYteuXSgsLERSUhIAICIiwu99xKvipBAAPv74Yzz88MOQSqWBs2qJkJ+ff9GnbnFxcQCAuLg4fPHFF369E0lJST7EBcDinc+FOyTTX9C/m7ieOHnyJOx2O4KDgxEaGnrePjscDuzcuRM7d+7Eyy+/jLfeeguhoaHYsGEDWyzjBe8DYJcJbDab38XvDv8UiUSsrBv+AvinTZsGAJgzZw7mzJnj93e53GEJl53QNTU12L17t9ct67+LyspKFBcXX7R2Dg8Ph9PpxL59+zA8PAzgz9sd9957L8LCwvDggw/ivffeu2C57qg/f+T1F1Q/ffp0plXdt0IuBP/5z3/w2GOPQa1Wj3vz+txF577+JZVKIRQKffLdN+udTucFHVq55dXV1SE7O9vHlUdEcDgcOHz48NVLaADYuHEj7rjjDr934y4WFosFr7766kXXS05OZqds7sejJ0pKSpCSkgK1Wg2lUonOzk6v/MjISHZdy9MPO3fuXADAb7/95kPim266yaedu+66C8HBwQCA5ubmixqD2zfsb/F45rtx9OhR3HHHHbj22muxZMkSry2QSCTC7bffDuCPyw7u/p8PRqMRixcvxqxZs1BZWTlhgsSuyImORqPxstwvFS+//PJFt61UKpl1XlBQ4LfMhg0bWBsZGRl+fekffvghM54iIiKorKzMx8vhaRTabDZKS0tjsuLj41l8y4kTJ5g34tyTv+zsbC8DlOM42rJlC5u/nJwclrdy5UpyuVxERLRt2zYfF+Dw8DDz4XteinjnnXfYuHJzc328HP5enJOcnMz6UFhY6GP8KRSKizbUJ5WX49zkOYn+MDw8zHy1/lBYWHhJ7WZkZDBXlCfBPFNUVBRzqblfSeBJaPcP2dzcTKWlpV6BV5798nTbORwOcjqdVFVVRfv27WOLyuFwjOuHLioqIiIii8VCer2empqaqKenh7XV0NDgRSSFQkG9vb3Mm9Hc3OzVn61bt7K6er2eCgoKqLq6mn1XXV3tRfQjR46MS2gAXu6+7u5uKi0tpZKSEqqtrSWz2Tzu/F6VhAZAOTk5jDhuX2d+fj6lpqbSsmXLKCUlhbZt20YGg4Fpnv7+fsrPz7/k1V9cXEw2m40MBsN5XUolJSVks9moq6uL+WzdhNbpdKTVar2eMmaz2adfnhp6x44d7CDHjc7OznHJDICys7PJaDSysXueAmq1Wr9aff369V7u0YaGBh9XYXd3t5c8s9lM27dv95kPnU5HNpvNa1H7cz36i6Ts7u4O6IuBLiRNiH9JERMTgyVLluDUqVOorq72G98rk8mwfPlyREREoLW1FU1NTZfcnkqlglgsxuDg4HlvKkdEREChUEAgEKC9vR0OhwM6nQ6LFy9GXV0dEhISsHTpUsyfPx+jo6Oor6/38YhER0ejtrYWYWFhyM3NRXZ2Nu6//37Mnj0bJpMJNTU1fxnPzHEcFi5ciMjISIhEIpjNZhw6dAh9fX3j1pk3bx4WLVoEAGhtbcWRI0d8ZN55552YOXMmBgYG0NjYiGPHjvnIUSqVCA0NxeDgoI8dce7vc/vtt2PatGlwOp3o6+uDXq+/IrHafBztJcSj+Dsk8Zc8txxvv/02P4f/9pc1TjT4c71daPnz+Yh5BAY8oS8Sni9dudBDH/ch0mR7z/JkxBV70cxkhUQiwbFjx1BdXY1ffvnlL8s7HA4IhUL8+uuvKC8vh8Fg4Cfxn3yCTgSjkAcPfsvBgwdPaB48oXnw4AnNg8eVI7SdnwYeVwmsQQDa+XngcZXAGARgN+/w5zHZcZbDpQIAMwF0AAjjp4XHJMYAgLlBAEwANOAPWHhMXtBZDpvce402AK0AVgqFQgkfRMNjMkAkEmFsbGwQwOMAioE/Yjnc0APYTkQuANzZxG+ueUxE2AC0jo2N/S+A/wHwqzvj/wcAZ1z7eLtfKhgAAAAASUVORK5CYII=" width="180" height="59" ></a>
                                        </td>
                                    </tr>
                                </table>
                            </td></tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:18px; line-height: 30px; color: #63678a; letter-spacing: -0.04em; padding: 5px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    <p style="margin: 0; font-size: 20px; color: #1f244c; font-weight: bold; padding: 0">Login credentials for [merchant_name]  </p>
                                    Username : [username]<br>
                                    Password : [password]
                                    <p style="font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">Use above credentials to login to the mobile app and web portal. <a href="[login_view]" style="color: #e85a44; font-weight: bold;" target="_blank">Click here</a> to login to web portal. </p>
                                </td>
                            </tr>
                            <tr>
                                <td class="banner" style="font-size:0pt; line-height:0pt;">
                                    <img src="data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QNvaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0NSA3OS4xNjM0OTksIDIwMTgvMDgvMTMtMTY6NDA6MjIgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6Nzk2NTg3MUMzQzI4RTkxMUEwOUI5OTlFRjAxMUU5NTgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6ODczMTM2Q0IzQzMxMTFFQTlGQzJGOENDNzdGRjZENjAiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6ODczMTM2Q0EzQzMxMTFFQTlGQzJGOENDNzdGRjZENjAiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDowMDVGMkRERkE2MzJFQTExQjg4NEREQjQ4MTE4QTdBMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3OTY1ODcxQzNDMjhFOTExQTA5Qjk5OUVGMDExRTk1OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6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" width="600" height="362" style="max-width: 100%; width: 100%; height: auto; " >
                                </td>
                            </tr>',
            'title'     =>  'Merchant login credentials',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Company details while creation
        $data[] = [
            'subject'   =>  'Company Details',
            'temp_code' =>  'COMPC',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:24px; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear Velocity,
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:22px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    [company_name] Company has been created successfully in the portal!
                                </td>													
                            </tr>',
            'title'     =>  'Company Details',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Investor contact us (to admin)
        // $data[] =  [
        //     'subject'   =>  'Investor Contact us',
        //     'temp_code' =>  'INCOA',
        //     'template'  =>  '<tr>
        //                         <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
        //                             <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
        //                                 Funding portal
        //                             </span>
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             <p style="font-size: 18px; color: #666; font-weight: normal; letter-spacing: 0">
        //                                 Dear Admin,
        //                             </p>
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td class="full-width" style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             <table width="100%" border="1" cellspacing="0" cellpadding="0">
        //                                 <thead>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Name :</th>
        //                                         <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[username]</td>
        //                                     </tr>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Email :</th>
        //                                         <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[email]</td>
        //                                     </tr>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Phone :</th>
        //                                         <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[phone]</td>
        //                                     </tr>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Company :</th>
        //                                         <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[company_name]</td>
        //                                     </tr>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Message :</th>
        //                                         <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[content]</td>
        //                                     </tr>
        //                                 </thead>
        //                             </table>
        //                         </td>
        //                     </tr>',
        //     'title'     =>  'Investor Contact Us (sent to admin)',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // Investor contact us(to others)
        // $data[] = [
        //     'subject'   =>  'Investor Contact us',
        //     'temp_code' =>  'INCOO',
        //     'template'  =>  '<tr>
        //                         <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
        //                             <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
        //                                 Funding portal
        //                             </span>
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             <p style="font-size: 18px; color: #666; font-weight: normal; letter-spacing: 0">
        //                                 Dear [username],
        //                             </p>
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             <p style="font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">
        //                             Thank you for showing interest we will get back to you soon.
        //                             </p>
        //                         </td>
        //                     </tr>',
        //     'title'     =>  'Investor Contact us (sent to others)',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // Request more money
        $data[] = [
            'subject'   =>  'Request More Money',
            'title'     =>  'Request More Money',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
            'type'      =>  'email',
            'temp_code' =>  'REQMM',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                        Request More Money                                        
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    [merchant_name] Requested [amount]
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                    <a href="[merchant_view_link]" style="text-decoration:none">
                                        <b>View Merchant</b>
                                    </a>
                                </td>
                            </tr>',
        ];
        // Request payoff
        $data[] = [
            'subject'   =>  'Request PayOff',
            'title'     =>  'Request PayOff',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
            'temp_code' =>  'REPOF',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                        Request PayOff                                        
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    [merchant_name] Requested to Payoff
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                    <a href="[merchant_view_link]" style="text-decoration:none">
                                        <b>View Merchant</b>
                                    </a>
                                </td>
                            </tr>',
        ];
        // Merchant update (crm)
        $data[] = [
            'subject'   =>  'Merchant updated from CRM.',
            'title'     =>  'Merchant Updated',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=> date('Y-m-d H:i:s'),
            'temp_code' =>  'CRMMU',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Merchant Updated                                        
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                Merchant <b> <a href="[merchant_view_link]">[merchant_name]</a> </b> was updated in the portal.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                    <a href="[merchant_view_link]" style="text-decoration:none">
                                        <b>View Merchant</b>
                                    </a>
                                </td>
                            </tr>',
        ];
        // merchant creation (crm)
        $data[] = [
            'subject'   =>  'New merchant added from CRM.',
            'title'     =>  'Merchant Created',
            'temp_code' =>  'CRMMC',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Merchant Created                                       
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                A new merchant, <b> <a href="[merchant_view_link]">[merchant_name]</a> </b> has been created in the portal.
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:0 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                    <a href="[merchant_view_link]" style="text-decoration:none">
                                        <b>View Merchant</b>
                                    </a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // investor creation (crm)
        $data[] = [
            'subject'   =>  'investor created from CRM.',
            'title'     =>  'Investor created',
            'temp_code' =>  'CRMIC',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    investor created                                       
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                A new investor, <b> <a href="[investor_view_link]">[investor_name]</a> </b> has been created in the portal.
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Investor update from crm
        $data[] = [
            'subject'   =>  'investor updated from CRM.',
            'title'     =>  'Investor Updated',
            'temp_code' =>  'CRMIU',
            'template' =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    investor created                                       
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
                                    Hello Velocity,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                An investor,<b> <a href="[investor_view_link]">[investor_name]</a> </b> has been updated in the portal.
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Fundings sign up (admin)
        $data[] = [
            'subject'   =>  'Investor Created',
            'title'     =>  'Fundings sign up (sent to admin)',
            'temp_code' =>  'INSUA',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear Admin,
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:18px; line-height: 30px; color: #63678a; letter-spacing: -0.04em; padding: 5px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    <p style="margin-top:16px;margin-bottom:16px;font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">
                                    A new investor, [investor_name] has taken interest in our Business Crowdfunding to participate in the funding process. The profile details, as created on [date_time], are as given below:
                                    </p>
                                    Email : [email]<br>
                                    Phone : [phone]<br>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Funding sign up (others)
        $data[] = [
            'subject'   =>  'Investor Created',
            'title'     =>  'Fundings sign up (sent to admin)',
            'temp_code' =>  'INSUO',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear [investor_name],
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:18px; line-height: 30px; color: #63678a; letter-spacing: -0.04em; padding: 5px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    <p style="margin-top:16px;margin-bottom:16px;font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">
                                    Thank you for showing interest and creating a profile in our Business Crowdfunding. Please note the following details of the profile  created on [date_time]
                                    </p>
                                    Email : [email]<br>
                                    Phone : [phone]<br>
                                    <p>Please add your bank details for the same.</p>
                                    <p>Note, if you have already registered with your bank details, please ignore this email</p>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // investor details
        $data[] = [
            'subject'   =>  '[investor_name] Details',
            'title'     =>  'Investor Details',
            'temp_code' =>  'INVTR',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear [investor_name],
                                </td>													
                            </tr>
                            <tr>
                                <td class="content"  style="text-align:center;font-size:32px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    Download our app and start tracking your investments today.
                                </td>													
                            </tr>
                            <tr>
                                <td class="download-icons">
                                    <table width="100%" cellpadding="0" cellspacing="0" style="">
                                        <tr>
                                            <td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 10px 30px 0; text-align: right; background: #fff">
                                                <a href="[android_view]"><img src="data:image/png;base64,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" width="180" height="59" >
                                                </a>
                                            </td>
                                            <td class="btn-wrap" style="font-size:0pt; line-height:0pt; padding: 12px 0 30px 10px; text-align: left; background: #fff">
                                                <a href="[ios_view]"><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAALQAAAA7CAYAAADSK6A/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAKT2lDQ1BQaG90b3Nob3AgSUNDIHByb2ZpbGUAAHjanVNnVFPpFj333vRCS4iAlEtvUhUIIFJCi4AUkSYqIQkQSoghodkVUcERRUUEG8igiAOOjoCMFVEsDIoK2AfkIaKOg6OIisr74Xuja9a89+bN/rXXPues852zzwfACAyWSDNRNYAMqUIeEeCDx8TG4eQuQIEKJHAAEAizZCFz/SMBAPh+PDwrIsAHvgABeNMLCADATZvAMByH/w/qQplcAYCEAcB0kThLCIAUAEB6jkKmAEBGAYCdmCZTAKAEAGDLY2LjAFAtAGAnf+bTAICd+Jl7AQBblCEVAaCRACATZYhEAGg7AKzPVopFAFgwABRmS8Q5ANgtADBJV2ZIALC3AMDOEAuyAAgMADBRiIUpAAR7AGDIIyN4AISZABRG8lc88SuuEOcqAAB4mbI8uSQ5RYFbCC1xB1dXLh4ozkkXKxQ2YQJhmkAuwnmZGTKBNA/g88wAAKCRFRHgg/P9eM4Ors7ONo62Dl8t6r8G/yJiYuP+5c+rcEAAAOF0ftH+LC+zGoA7BoBt/qIl7gRoXgugdfeLZrIPQLUAoOnaV/Nw+H48PEWhkLnZ2eXk5NhKxEJbYcpXff5nwl/AV/1s+X48/Pf14L7iJIEyXYFHBPjgwsz0TKUcz5IJhGLc5o9H/LcL//wd0yLESWK5WCoU41EScY5EmozzMqUiiUKSKcUl0v9k4t8s+wM+3zUAsGo+AXuRLahdYwP2SycQWHTA4vcAAPK7b8HUKAgDgGiD4c93/+8//UegJQCAZkmScQAAXkQkLlTKsz/HCAAARKCBKrBBG/TBGCzABhzBBdzBC/xgNoRCJMTCQhBCCmSAHHJgKayCQiiGzbAdKmAv1EAdNMBRaIaTcA4uwlW4Dj1wD/phCJ7BKLyBCQRByAgTYSHaiAFiilgjjggXmYX4IcFIBBKLJCDJiBRRIkuRNUgxUopUIFVIHfI9cgI5h1xGupE7yAAygvyGvEcxlIGyUT3UDLVDuag3GoRGogvQZHQxmo8WoJvQcrQaPYw2oefQq2gP2o8+Q8cwwOgYBzPEbDAuxsNCsTgsCZNjy7EirAyrxhqwVqwDu4n1Y8+xdwQSgUXACTYEd0IgYR5BSFhMWE7YSKggHCQ0EdoJNwkDhFHCJyKTqEu0JroR+cQYYjIxh1hILCPWEo8TLxB7iEPENyQSiUMyJ7mQAkmxpFTSEtJG0m5SI+ksqZs0SBojk8naZGuyBzmULCAryIXkneTD5DPkG+Qh8lsKnWJAcaT4U+IoUspqShnlEOU05QZlmDJBVaOaUt2ooVQRNY9aQq2htlKvUYeoEzR1mjnNgxZJS6WtopXTGmgXaPdpr+h0uhHdlR5Ol9BX0svpR+iX6AP0dwwNhhWDx4hnKBmbGAcYZxl3GK+YTKYZ04sZx1QwNzHrmOeZD5lvVVgqtip8FZHKCpVKlSaVGyovVKmqpqreqgtV81XLVI+pXlN9rkZVM1PjqQnUlqtVqp1Q61MbU2epO6iHqmeob1Q/pH5Z/YkGWcNMw09DpFGgsV/jvMYgC2MZs3gsIWsNq4Z1gTXEJrHN2Xx2KruY/R27iz2qqaE5QzNKM1ezUvOUZj8H45hx+Jx0TgnnKKeX836K3hTvKeIpG6Y0TLkxZVxrqpaXllirSKtRq0frvTau7aedpr1Fu1n7gQ5Bx0onXCdHZ4/OBZ3nU9lT3acKpxZNPTr1ri6qa6UbobtEd79up+6Ynr5egJ5Mb6feeb3n+hx9L/1U/W36p/VHDFgGswwkBtsMzhg8xTVxbzwdL8fb8VFDXcNAQ6VhlWGX4YSRudE8o9VGjUYPjGnGXOMk423GbcajJgYmISZLTepN7ppSTbmmKaY7TDtMx83MzaLN1pk1mz0x1zLnm+eb15vft2BaeFostqi2uGVJsuRaplnutrxuhVo5WaVYVVpds0atna0l1rutu6cRp7lOk06rntZnw7Dxtsm2qbcZsOXYBtuutm22fWFnYhdnt8Wuw+6TvZN9un2N/T0HDYfZDqsdWh1+c7RyFDpWOt6azpzuP33F9JbpL2dYzxDP2DPjthPLKcRpnVOb00dnF2e5c4PziIuJS4LLLpc+Lpsbxt3IveRKdPVxXeF60vWdm7Obwu2o26/uNu5p7ofcn8w0nymeWTNz0MPIQ+BR5dE/C5+VMGvfrH5PQ0+BZ7XnIy9jL5FXrdewt6V3qvdh7xc+9j5yn+M+4zw33jLeWV/MN8C3yLfLT8Nvnl+F30N/I/9k/3r/0QCngCUBZwOJgUGBWwL7+Hp8Ib+OPzrbZfay2e1BjKC5QRVBj4KtguXBrSFoyOyQrSH355jOkc5pDoVQfujW0Adh5mGLw34MJ4WHhVeGP45wiFga0TGXNXfR3ENz30T6RJZE3ptnMU85ry1KNSo+qi5qPNo3ujS6P8YuZlnM1VidWElsSxw5LiquNm5svt/87fOH4p3iC+N7F5gvyF1weaHOwvSFpxapLhIsOpZATIhOOJTwQRAqqBaMJfITdyWOCnnCHcJnIi/RNtGI2ENcKh5O8kgqTXqS7JG8NXkkxTOlLOW5hCepkLxMDUzdmzqeFpp2IG0yPTq9MYOSkZBxQqohTZO2Z+pn5mZ2y6xlhbL+xW6Lty8elQfJa7OQrAVZLQq2QqboVFoo1yoHsmdlV2a/zYnKOZarnivN7cyzytuQN5zvn//tEsIS4ZK2pYZLVy0dWOa9rGo5sjxxedsK4xUFK4ZWBqw8uIq2Km3VT6vtV5eufr0mek1rgV7ByoLBtQFr6wtVCuWFfevc1+1dT1gvWd+1YfqGnRs+FYmKrhTbF5cVf9go3HjlG4dvyr+Z3JS0qavEuWTPZtJm6ebeLZ5bDpaql+aXDm4N2dq0Dd9WtO319kXbL5fNKNu7g7ZDuaO/PLi8ZafJzs07P1SkVPRU+lQ27tLdtWHX+G7R7ht7vPY07NXbW7z3/T7JvttVAVVN1WbVZftJ+7P3P66Jqun4lvttXa1ObXHtxwPSA/0HIw6217nU1R3SPVRSj9Yr60cOxx++/p3vdy0NNg1VjZzG4iNwRHnk6fcJ3/ceDTradox7rOEH0x92HWcdL2pCmvKaRptTmvtbYlu6T8w+0dbq3nr8R9sfD5w0PFl5SvNUyWna6YLTk2fyz4ydlZ19fi753GDborZ752PO32oPb++6EHTh0kX/i+c7vDvOXPK4dPKy2+UTV7hXmq86X23qdOo8/pPTT8e7nLuarrlca7nuer21e2b36RueN87d9L158Rb/1tWeOT3dvfN6b/fF9/XfFt1+cif9zsu72Xcn7q28T7xf9EDtQdlD3YfVP1v+3Njv3H9qwHeg89HcR/cGhYPP/pH1jw9DBY+Zj8uGDYbrnjg+OTniP3L96fynQ89kzyaeF/6i/suuFxYvfvjV69fO0ZjRoZfyl5O/bXyl/erA6xmv28bCxh6+yXgzMV70VvvtwXfcdx3vo98PT+R8IH8o/2j5sfVT0Kf7kxmTk/8EA5jz/GMzLdsAAAAgY0hSTQAAeiUAAICDAAD5/wAAgOkAAHUwAADqYAAAOpgAABdvkl/FRgAAFA5JREFUeNrsXX1QlNXbvpbddVdYiAe/2LVindK1AmWl0CSNPqwMJizJ8C2mmZ10pmxogr4/lAlm6JeZ9quhGn1rKaWhosjEIQP5cKBNCERhQdlWkABndd0X2AWW3eV+/8g97bqLqW0K9lwzZ4bdc859PvY693Pu+9znQQBvTBeJRJlOpzMZwDwAEvDgMfFgB9AuFAp3u1yu/wI47a/QagD/B4D4xKdJlPrPchcAIPAg89cen3nwmEwgAI8CKBYAmCEUCg0ulyuMnxcekxUikWjA6XTOFQJ4hYju46eEx2TG2NiYBIBTAOAQgIX8lPC4CnBEAGCE92bwuEowKji7oebB46pAED8FPHhC8+DBE5oHD57QPHjwhObBE3rCIz4+Hh988AG++uorCIXCgMkVi8WQy+V/W05iYiI4jgMApKWlIS0t7bzlFQoFnnnmGVbHEyqVCllZWYiIiLiic65Wq7Fu3bqAzvflwIQPQMnLyyOLxUJEREajkUQiUcBk79q1i8xmMyUlJV2yDJVKRWfOnKFnnnmGAFBlZSVVVlaet05SUhINDQ1RfHy8T97atWvJZrNRTEzMZZ1nmUxGsbGx7PNLL71EZrOZOI6bNMFKE15D5+fn45VXXkF4eDgAoK2tDU6nMyCyFQoF4uLiMDg4iDVr1rDvOY6DWCz20uIymYz9rVKpvDSrwWDA+vXr8d133wEA7HY77HY7y5fJZFCpVEwGAIyNjcFut2N0dBRKpRIhISEsz+FwYGRkBETkJWPevHl/OaaoqCgolUqv79ztymQyREVF+a0nEomwefNmfPzxx2xsLpcLdrsdAoHAR+bF9InX0GdTWloaORwOcsPlclF6enrA5K9bt470ej1t3LiROjs7SSaTEQD64IMPqLy8nABQSEgI6XQ6eu6550gmk1F5eTnpdDpqb2+nzMxMAkAKhYIOHjxIqampBIDKysqorKyMANBtt91GBw8epF9++YXa2tooJSWFANDKlSvJYrFQWVkZ6fV6MhqNrP7q1avJbDYzDZ2WlkaHDx+mhoYGqqyspKioKJ+xiMViKiwspCNHjlB7ezsVFBSQWCwmAKTVaqm0tJR0Oh319fXR1q1bfeqnpKSQyWQiq9VKNTU1JJfLKSMjg0wmE5WWllJ3dzdVV1czbZ2enk7Nzc3U0NBAFRUVpFAoJgpvJi6hq6qqyBOlpaUBlV9SUkJarZY4jqNTp04xsq1evZqGh4cpJiaGHnzwQerv76fo6GgCQLGxsaRSqUir1VJPTw+JRCKKioois9lM69ev9yG0TCajmJgYio6OppqaGqqrqyMAtGLFCnK5XLRlyxZSKpVUVlZGBoOBhEIhI7RKpSKO46izs5MyMzOJ4zhqaGig7du3+92WmUwmio2NpcTERLJarbRp0yYCQHv27CGz2UwJCQn02muv0dDQEBuP53ajqKiImpubSaVSkUgkoueee47sdjtpNBpasWIF2Ww2ysjIYH1y/93U1EQfffTRhOCMaKJuNZRKJRYu/DNmqqqqCk899VTA5HMch8WLF8NkMuGzzz5DcHAwkpOT8f3332P37t3o6enBI488glmzZqG+vh4tLS2QyWR49tlnER0dDYlEAqlUihkzZmB0dBQul8vvVmj69OnIyclBREQE5HI524qIRCL09/dDq9Wis7MTO3bsgFarxaxZs7zk3HrrrZDL5VizZg1WrVqF6dOnQ6FQ+LRz1113obKyEocOHQIA1NfX484772T59fX1qK2thc1mwxtvvIHIyEi0tLSwfKvVisHBQYyMjODo0aOsj6dPn8bnn38Op9OJ33//HXK5HLfeeitmzZqFtLQ0pKamsrFNBExYQs+cORNWqxUnTpzAF198gXfffTeg8pOTkxEcHIydO3diaGgIAHDPPfdALBbD4XDg+++/x6pVqyAWi/H+++8DALKysvDwww8jLi4Od999NzZv3gy73Q6JZPzYrnfffRc33ngjlixZgtzcXNx33x+RugKBAFOmTEFYWBjbz9vtdoyMjPy5FyTC0NAQHA4HPvzwQ7S0tMDpdGJwcNCnneHhYURGRrLPM2bMYMQUCP68txESEuK1Nz93H+1ZlogQFBQEqVQKq9XKZA0NDcHlciE/Px+tra1wOp0YGBjgCa1UKrFmzRrExcVBJpOht7cXFRUVKC4uxsGDB7FgwQJYLBbExsYiLy8ParUaoaGhsFgsaGtrw9dff42DBw9eUtuPP/442trasHnzZgDA4cOHsX//fjz00EMoLi7Gzp07kZGRgd7eXnz55ZfMWJNIJHj00UeRlJQEiUQCiUQCgUAAiUTC3FsSiQQul4sZVtdccw2efvppJCcnM+0bFBSEqVOnIjc3F7W1tdBoNPjxxx9x5swZTJ06FVKpFFOmTIFOp0NjYyMyMzNRVFSE8PBw7NmzB11dXV7j+eSTT7B9+3Zs3boVoaGhmD17Np5//nlmyHoauVKp1Iu4bhw/fhxr1qxBXl4e3nzzTQQFBWHKlCmYMmUKkyOVSqHT6dDS0oKsrCx8+eWX4DgO3333nU+frgSEALKvRMM5OTn45JNPkJKSgltuuQVz587FokWLkJqaisceeww333wzYmJikJOTg40bNyIxMRE33ngjrrvuOsybNw9Lly7F2rVrccMNN2Dv3r0YGxu7qPbnz5+Pb7/9Fnq9HgDQ09MDqVSKEydO4OjRozh58iQEAgH27t0LnU4HAGhsbERYWBiio6OxZ88etLa24sCBAxgeHoZMJsP+/fvR29sLmUyG9vZ21NfXo66uDtdffz2uv/56FBUV4dixYzhw4ACCg4PR09ODxsZGLF++HBUVFXjhhRcwOjrKFsL+/fsxMDCAsrIyKBQKLFiwAAKBAHV1dTh58qTXeFpaWnD8+HEsXboUAoEAmzZtwo8//ggACAsLg16vR319PZxOJ8RiMSoqKmA2m71kHD58GNOmTUNkZCRKSkoQFBSEwcFB7N27F0SE8PBw6HQ6tLW1obS0FHK5HAsXLoRAIMCBAwdgMpn+nV6OwsJCCiT27dtHISEh/IVRPl1+P3ROTg7Wrl0b2H2TSIRrrrmGP/flcXk1tEqlIrPZHFDtvGfPnoCeHPKJ19AXjCeeeCKg8QknTpzA+vXrA3ZyyGPy47J6OZYtWxZQeYWFhejt7Q2IrPj4eIjFYgQFBaGxsRE2m23C/VgqlQpqtRoKhQIjIyPo7OzEzz//DIvFwjP5cm85xGIxGY3GgG017HY7JSYmBqRvsbGxZDabaXh4mIaGhuj111+fUI9RmUxGWq2WBWh5wmg0XvYgJn7LcfbEzDM45+9iZGQEp0+fDoislJQUREREQCqVYurUqXjggQcmjLYRi8X45ptv8OSTTyI8PBzHjh1DRUUFfv75Z5w6dQoAYDQa//TDCoVYvnw53n77bcTHx/Ma+p9KcrmcTCZTwDS0zWbzG3p5Kamuro6IiMrLy8nhcJDVavWJdbhSKT09nYiIxsbGaOvWrSzgCABFRUXRihUrfIKMRkZGiIhYbAqvof8BnD59OqD7UqlU6hXrcalQq9VYsGABXC4XCgoKYLFYEBISgpSUFL/lZTIZOI6DSCRiGlGtViM+Pn7ceAaO47yMYY7jEB8fD7VazeScb28PABaLBZs3b4bD4WB5XV1d+Omnn7z6Nm3aNHYULxKJwHHcuE/GqKgoJCQkIDY2dtwy7vG6T0HlcjmzN85FdHQ0EhMToVKp/h1uO51OF1CX3f79+/92n15//XUiIurq6iKO46impoaIiKqqqnzKCoVCKi8vp87OTtJoNLR27VpqaWkhh8NBLpeLuru7adu2bV5aNDo6mjo6Oqi9vZ1UKhXl5eVRT08PjY2Nkd1uJ71eT+vWrRu3f1u2bGE2gzu81F8SiURUU1Pj9RTs6+uj7u5uamhoYKGxACg5OZkqKyupv7+fiIgcDgcZDAZ65513vPouFAqpqqqKOjs7KT09nTIzM6mnp8fn6fjEE09QU1MTOZ1O9vSsqqqi5cuXX93howUFBQEltMvloqysrL/VJzeBv/rqKwJAmzZtIiKigYEBUqlUPuWbm5uJiKiuro6GhobIZrNRR0cHWa1W1q9du3ax8mq1mux2OzkcDqqsrCQiIpPJRMePH6exsTFGVvdtl3NTYmIiDQ0NMYJmZWV5kdPT6NbpdF6GY39/P505c4b0ej2LY9ZoNGSz2Vg/ysvLSa/XszplZWVepG5vbycioqKiIrLb7WwBJCQkEADasGED2+JUVVVRYWEhdXV1sf5eAYP18jW2bt06CjRsNhsLtL+Ugx63ltJoNASA4uPj2Q/+4osv+tRpaGhgbdfU1JBarfYK5Hfvd93aNDo6mrVBRPTRRx+RQqEgkUhEaWlpdObMGSIi6unpGTdIPjc31+uiQ0dHB+Xl5fmU5ziONBoNK5eenk4KhYLkcjkbb19fHxERNTU1kVKpZIthx44drJ47jtpzAbtcLjIajZSRkUErV64kmUzmdVCWl5fH6iiVSmpra/NSFFcloRUKRUANQ0+UlJR4aZYLSS+++CIjk6fWq62tZUbieNsmdwC+Z96yZcvYYvjmm28IAMXExDBC63Q6H3kbN25kY9iwYcO4fdVoNNTS0uLjslu9erVXudTUVJZ/7j3JvLw89kQ4N08mk5HBYCAiIr1eT0Kh0IvQNpvNx03qltfS0uLT3xdeeIGIiLq7uy/rncTLelLY29uL8vLyf0R2aGiol8F0Ibj//vsBAH19fVi1ahU0Gg00Gg1zhy1atGhcA8dgMLB4YzcOHDiAjo4OAMANN9zwh4HiEXtcW1vrI+eHH35g8djz588ft6+ffvop1Go10tPTUVFRASLCnDlz8NlnnyEhIcHLzcdOzc4xOG+77TYAQHd3N/bt2+eVZ7VaWSju7NmzcfPNN3vlt7e3o6qqyq+84OBgaLVaFBYWYteuXSgsLERSUhIAICIiwu99xKvipBAAPv74Yzz88MOQSqWBs2qJkJ+ff9GnbnFxcQCAuLg4fPHFF369E0lJST7EBcDinc+FOyTTX9C/m7ieOHnyJOx2O4KDgxEaGnrePjscDuzcuRM7d+7Eyy+/jLfeeguhoaHYsGEDWyzjBe8DYJcJbDab38XvDv8UiUSsrBv+AvinTZsGAJgzZw7mzJnj93e53GEJl53QNTU12L17t9ct67+LyspKFBcXX7R2Dg8Ph9PpxL59+zA8PAzgz9sd9957L8LCwvDggw/ivffeu2C57qg/f+T1F1Q/ffp0plXdt0IuBP/5z3/w2GOPQa1Wj3vz+txF577+JZVKIRQKffLdN+udTucFHVq55dXV1SE7O9vHlUdEcDgcOHz48NVLaADYuHEj7rjjDr934y4WFosFr7766kXXS05OZqds7sejJ0pKSpCSkgK1Wg2lUonOzk6v/MjISHZdy9MPO3fuXADAb7/95kPim266yaedu+66C8HBwQCA5ubmixqD2zfsb/F45rtx9OhR3HHHHbj22muxZMkSry2QSCTC7bffDuCPyw7u/p8PRqMRixcvxqxZs1BZWTlhgsSuyImORqPxstwvFS+//PJFt61UKpl1XlBQ4LfMhg0bWBsZGRl+fekffvghM54iIiKorKzMx8vhaRTabDZKS0tjsuLj41l8y4kTJ5g34tyTv+zsbC8DlOM42rJlC5u/nJwclrdy5UpyuVxERLRt2zYfF+Dw8DDz4XteinjnnXfYuHJzc328HP5enJOcnMz6UFhY6GP8KRSKizbUJ5WX49zkOYn+MDw8zHy1/lBYWHhJ7WZkZDBXlCfBPFNUVBRzqblfSeBJaPcP2dzcTKWlpV6BV5798nTbORwOcjqdVFVVRfv27WOLyuFwjOuHLioqIiIii8VCer2empqaqKenh7XV0NDgRSSFQkG9vb3Mm9Hc3OzVn61bt7K6er2eCgoKqLq6mn1XXV3tRfQjR46MS2gAXu6+7u5uKi0tpZKSEqqtrSWz2Tzu/F6VhAZAOTk5jDhuX2d+fj6lpqbSsmXLKCUlhbZt20YGg4Fpnv7+fsrPz7/k1V9cXEw2m40MBsN5XUolJSVks9moq6uL+WzdhNbpdKTVar2eMmaz2adfnhp6x44d7CDHjc7OznHJDICys7PJaDSysXueAmq1Wr9aff369V7u0YaGBh9XYXd3t5c8s9lM27dv95kPnU5HNpvNa1H7cz36i6Ts7u4O6IuBLiRNiH9JERMTgyVLluDUqVOorq72G98rk8mwfPlyREREoLW1FU1NTZfcnkqlglgsxuDg4HlvKkdEREChUEAgEKC9vR0OhwM6nQ6LFy9GXV0dEhISsHTpUsyfPx+jo6Oor6/38YhER0ejtrYWYWFhyM3NRXZ2Nu6//37Mnj0bJpMJNTU1fxnPzHEcFi5ciMjISIhEIpjNZhw6dAh9fX3j1pk3bx4WLVoEAGhtbcWRI0d8ZN55552YOXMmBgYG0NjYiGPHjvnIUSqVCA0NxeDgoI8dce7vc/vtt2PatGlwOp3o6+uDXq+/IrHafBztJcSj+Dsk8Zc8txxvv/02P4f/9pc1TjT4c71daPnz+Yh5BAY8oS8Sni9dudBDH/ch0mR7z/JkxBV70cxkhUQiwbFjx1BdXY1ffvnlL8s7HA4IhUL8+uuvKC8vh8Fg4Cfxn3yCTgSjkAcPfsvBgwdPaB48oXnw4AnNg8eVI7SdnwYeVwmsQQDa+XngcZXAGARgN+/w5zHZcZbDpQIAMwF0AAjjp4XHJMYAgLlBAEwANOAPWHhMXtBZDpvce402AK0AVgqFQgkfRMNjMkAkEmFsbGwQwOMAioE/Yjnc0APYTkQuANzZxG+ueUxE2AC0jo2N/S+A/wHwqzvj/wcAZ1z7eLtfKhgAAAAASUVORK5CYII=" width="180" height="59" ></a>
                                            </td>													
                                        </tr>
                                    </table>
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:18px; line-height: 30px; color: #63678a; letter-spacing: -0.04em; padding: 5px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    <p style="margin: 0; font-size: 20px; color: #1f244c; font-weight: bold; padding: 0">Login credentials</p>
                                    Username : [username]<br>
                                    Password : [password]
                                    <p style="font-size: 16px; color: #666; font-weight: normal; letter-spacing: 0">Use above credentials to login to the mobile app and web portal. <a href="[login_view]" style="color: #e85a44; font-weight: bold;" target="_blank">Click here</a> to login to web portal. </p>
                                </td>													
                            </tr>
                            <tr>
                                <td class="banner" style="text-align:center;font-size:0pt; line-height:0pt;">
                                    <img src="data:image/jpeg;base64,/9j/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP/sABFEdWNreQABAAQAAAA8AAD/4QNvaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjYtYzE0NSA3OS4xNjM0OTksIDIwMTgvMDgvMTMtMTY6NDA6MjIgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6Nzk2NTg3MUMzQzI4RTkxMUEwOUI5OTlFRjAxMUU5NTgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6Njc2MzM3MUMzQzMxMTFFQUIwNzRCRTA0QUREOUVERjQiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6Njc2MzM3MUIzQzMxMTFFQUIwNzRCRTA0QUREOUVERjQiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpENzYzMUY0RkFBMzJFQTExQjg4NEREQjQ4MTE4QTdBMyIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo3OTY1ODcxQzNDMjhFOTExQTA5Qjk5OUVGMDExRTk1OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6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" width="600" height="362" style="max-width: 100%; width: 100%; height: auto; " >
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // funding request approval
        $data[] = [
            'subject'   =>  'Funding request approved | Velocitygroupusa',
            'title'     =>  'Funding request approved',
            'temp_code' =>  'FREQA',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear [investor_name]
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                Your fund request is approved!
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">
                                    <a href="[merchant_view_link]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">View Merchant</a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //funding request reject
        $data[] = [
            'subject'   =>  'Funding request rejected | Velocitygroupusa',
            'title'     =>  'Funding request rejected',
            'temp_code' =>  'FREQR',
            'template'  =>  '<tr>
                                <td class="logo" style="text-align:center;font-size:22px; color: #70748e; line-height:0pt; padding: 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Dear [investor_name]
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;font-size:26px; line-height: 44px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    The Merchant <a href="[merchant_view_link]">[merchant_name]</a> did not fund and you did not participate in this deal. Please contact your admin for details if you have any questions.
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="text-align:center;padding: 25px 30px 15px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;">
                                    <a href="[merchant_view_link]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #d33724; font-size: 16px; font-weight: bold; ">View Merchant</a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //funding push notifications
        // $data[] = [
        //     'subject'   =>  'Funding request | Velocitygroupusa',
        //     'title'     =>  'Funding request push notification (sent to admin)',
        //     'temp_code' =>  'FAPN',
        //     'template'  =>  '<tr>
        //                         <td style=" padding: 10px 50px 10px; background: #fff; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:left; font-size: 22px; font-weight: bold; color: #3d3d3d;">
        //                         Hello Velocity,
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             [content]
        //                         </td>
        //                     </tr>',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // Merchant DB Auto Test
        // $data[] =   [
        //     'subject'   =>  'Merchant DB Auto Test',
        //     'title'     =>  'Merchant DB Auto Test',
        //     'temp_code' =>  'MDBAT',
        //     'template'  =>  '<tr><td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
        //                         <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
        //                         Merchant DB Auto Test
        //                         </span>
        //                     </td></tr>
        //                     <tr><td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                         <table width="100%" border="1" cellspacing="0" cellpadding="0">
        //                             <thead>
        //                                 <tr>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Name</th>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">IDs</th>
        //                                 </tr>
        //                             </thead>
        //                             <tbody>
        //                                 <tr>
        //                                     <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">Last Payment Date is null</td>
        //                                     <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[last_payment]</td>
        //                                 </tr>
        //                                 <tr>
        //                                     <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">Last Status Updated Date is null</td>
        //                                     <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[last_status]</td>
        //                                 </tr>
        //                             </tbody>
        //                         </table>
        //                     </td></tr>',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // delete old logs
        // $data[] = [
        //     'subject'   =>  '[title]',
        //     'title'     =>  'Delete old logs',
        //     'temp_code' =>  'DOLDL',
        //     'template'  =>  '<tr><td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
        //                         <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
        //                         [title]
        //                         </span>
        //                     </td>
        //                     </tr>
        //                     <tr><td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                         <table width="100%" border="1" cellspacing="0" cellpadding="0">
        //                             <thead>
        //                                 <tr>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[title] before [date]</th>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[total_count]</th>
        //                                 </tr>
        //                             </thead>
        //                             <tbody>
        //                                 <tr>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[title] deleted</th>
        //                                     <th style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[deleted_count]</th>
        //                                 </tr>
        //                             </tbody>
        //                         </table>
        //                     </td></tr>',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // reconcillation request
        $data[] = [
            'subject'   =>  'Reconciliation request',
            'title'     =>  'Reconciliation Notification',
            'temp_code' =>  'RECR',
            'template'  =>  '<tr>
                                    <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                        <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                        Reconciliation Notification
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size: 22px;color: #45485d;line-height: 30px;padding: 30px 0 0;background: #fff;font-family: Arial, sans-serif, Helvetica, Verdana;
                                    font-weight: 700;
                                    text-align: center">
                                        Dear [merchant_name],                            
                                    </td>
                                </tr>
                                <tr>
                                    <td style="font-size:16px;line-height:32px;color:#000;text-align:justify;letter-spacing:0;padding:20px 25px;background:#fff;font-family:Arial,sans-serif,Helvetica,Verdana;">
                                    As you are aware, pursuant to paragraph 10 of the Future Receivables Sale and Purchase Agreement, you have the right of reconciliation if you have experienced either a decrease or increase in your daily receipts over the past month. If you request a reconciliation, you will be required to provide a copy of your bank statements, credit card processing statements, and pertinent aging report(s) for the reconciliation month at issue, which will be reviewed by Velocity Group USA, Inc. Would you like to request a reconciliation?
                                    </td>
                                </tr>
                                <tr>
                                    <td class="content" style="padding: 25px 0 60px 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;" align="center">    
                                        <a href="[yes_link]" id="recon_yes" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px; ">Yes</a>
                                        <a href="[no_link]" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #f05559; font-size: 16px; font-weight: bold; display: inline-block; margin: 0 5px; ">No</a>
                                    </td>
                                </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //reconcile request to admin
        $data[] = [
            'subject'   =>  'Reconciliation Request by merchant',
            'title'     =>  'Reconciliation Request (sent to admin)',
            'temp_code' =>  'RERQA',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Reconciliation Request
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 22px;
                                            color: #45485d;
                                            line-height: 30px;
                                            padding: 30px 0 0 50px;
                                            background: #fff;
                                            font-family: Arial, sans-serif, Helvetica, Verdana;
                                            font-weight: 700;">
                                    Hi,                            
                                </td>
                            </tr>
                            <tr>
                                <td style="font-size: 16px;
                                            line-height: 32px;
                                            color: #000;
                                            text-align: justify;
                                            letter-spacing: 0em;
                                            padding:20px 50px;
                                            background: #fff;
                                            font-family: Arial, sans-serif, Helvetica, Verdana;">
                                            Merchant  <a href="[merchant_view_link]">[merchant_name]</a> has requested a reconciliation.
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="font-size: 16px;
                                                            line-height: 32px;
                                                            color: #000;
                                                            text-align: justify;
                                                            letter-spacing: 0em;
                                                            padding:20px 50px !important;
                                                            background: #fff;
                                                            font-family: Arial, sans-serif, Helvetica, Verdana;">    
                                    <a href="[merchant_view_link]" style="padding: 2px 45px; margin:15px 0 0; display: inline-block; width: auto; text-decoration: none; border-radius: 35px; color: #fff; background: #2db77e; font-size: 16px; font-weight: bold; ">View Merchant
                                    </a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach check status
        $data[] = [
            'subject'   =>  '[title]',
            'title'     =>  'Merchant ACH Status (Check/Re-Check)',
            'temp_code' =>  'MACHC',
            'template'  =>  '<tbody>
                            <tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    [title]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">        
                                <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                    <thead>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Transactions:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[count_total]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Payment Transactions:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_payment]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Fee Transactions:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_fee]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[checked_time]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Settled :</th>
                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[total_settled]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Settled Payment:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_settled_payment]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Settled Fees:</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_settled_fee]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Rcode :</th>
                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[total_rcode]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Rcode Payment :</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_rcode_amount]</td>
                                        </tr>
                                        <tr>
                                            <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Rcode Fees :</th>
                                            <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_rcode_fee]</td>
                                        </tr>
                                    </thead>
                                </table>        
                                </td>
                            </tr>
                            </tbody>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // rcode mail
        $data[] = [
            'subject'   =>  '[title]',
            'title'     =>  'Merchant ACH (Check/Re-check) Rcode report',
            'temp_code' =>  'RCOML',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    [title]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    <div class="content">
                                    [rcode_report_table]
                                    </div>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach sent report
        $data[] = [
            'subject'   =>  'Merchant ACH Sent report for [payment_date]',
            'title'     =>  'Merchant ACH Sent report',
            'temp_code' =>  'MACHR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Merchant ACH Sent report for [payment_date]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">                            
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Transactions:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[count_total]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Payment Transactions:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_payment]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Fee Transactions:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_fee]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Payment Date:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[payment_date]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[checked_time]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Processed Count:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[count_total_processing]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Payment Processed Count:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_payment_processing]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Fee Processed Count:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[count_fee_processing]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Processed:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[total_processed]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Processed Payment Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_processed_payment]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: normal; color: #100947;">Processed Fee:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: normal; color: #3d3d3d;">[total_processed_fee]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach syndication sent report
        $data[] = [
            'subject'   =>  'ACH Syndicate Sent report for [payment_date]',
            'temp_code' =>  'ACHSR',
            'title'     =>  'ACH Syndication Sent Report',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style=" padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    ACH Syndicate Sent report for [payment_date]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">        
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Transactions:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[count_total]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Payment Date:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[payment_date]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[checked_time]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Processed Count:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[count_total_processing]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total Processed:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[total_processed]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //payment pause
        $data[] = [
            'subject'   =>  'Payment Paused',
            'temp_code' =>  'PYPS',
            'title'     =>  'Payment Paused',
            'template'  =>  "<tr>
                                <td class='logo' style='padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;'>
                                    <span style='padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;'>
                                    Payment Paused
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;'>
                                    <a href='[merchant_view_link]'> [merchant_name]'s </a> ACH payment has been paused [paused_type] [paused_by]  on [paused_at].
                                </td>
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // payment resume
        $data[] = [
            'subject'   =>  'Payment Resumed',
            'title'     =>  'Payment Resumed',
            'temp_code' =>  'PYRS',
            'template'  =>  "<tr>
                                <td class='logo' style='padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;'>
                                    <span style='padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;'>
                                    Payment Resumed
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;'>
                                    <a href='[merchant_view_link]'>[merchant_name] 's </a> ACH payment resumed manually by [resumed_by] on [resumed_at].
                                </td>
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Missed payment
        $data[] = [
            'subject'   =>  'We noticed you missed a payment...',
            'title'     =>  'Missed Payment',
            'temp_code' =>  'MRTD',
            'template'  =>  "<tr>
                                <td class='logo' style='padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;'>
                                    <span style='padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;'>
                                    We noticed you missed a payment...
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style='padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;'>
                                Hope all is well. This is the Asset Recovery Department at Velocity Group USA. We noticed that there was an interruption to the delivery of receivables due to insufficient funds in your designated account today. Is everything OK with the business, [merchant_name]  ?<br>
                                If this was a one-time issue, you can make a one time credit card payment here. <a href='[payment_link]' target='_blank'>Click Here</a>.<br>
                                If not, please make sure that there is necessary funds available in your designated account today to deliver the receivables generated yesterday in addition to your next ACH debit.<br>
                                In the event you have experienced a problem with your designated bank account or if there's an issue with the business, please contact me immediately so that we can discuss the necessary steps and work with you to resolve the same.<br>
                                <br>
                                Respectfully,<br>
                                Lauren Esposito | Director of Collections<br>
                                lesposito@curepayment.com<br>
                                (631) 953-2625 Ext. 502<br>
                                (800) 519-2234<br>
                                Fax: (631) 953-2610<br>
                                lesposito@curepayment.com <br>
                                www.curepayment.com
                                </td>
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // sending payment to other
        $data[] = [
            'subject'   =>  'Payment successful',
            'title'     =>  'Payment successful (sent to others)',
            'temp_code' =>  'PYMNT',
            'template'  =>  '<tbody>
                            <tr>
                                <td class="td" align="center" style="width:650px; min-width:650px; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal; padding: 0;">
                                <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0" style="border-radius: 5px;">
                                    <tbody>
                                    <tr>
                                    <td>
                                        <!-- Header -->
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td style=" padding: 0 50px 10px; background: #fff; ">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FCFDFE; border: 1px solid #f3f6f7;">
                                                <tr>
                                                <td style=" padding: 20px 50px 0; background: #FCFDFE; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 15px; font-weight: bold; color: #888896;">
                                                    Hello [merchant_name],<br>
                                                </td>
                                                </tr>
                                                <tr>
                                                <td style="padding:10px 50px 20px; background: #FCFDFE; font-size:16px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:center; color: #605f73;">
                                                    [content]
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:15px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:center; color: #7a7a7a;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Amount Paid
                                                </td>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Date Paid
                                                </td>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Payment Method
                                                </td>
                                                </tr>
                                                <tr>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    [amount]
                                                </td>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    [date]
                                                </td>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    Visa - [card_number]
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:30px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                <td style="padding: 15px 30px; background: #F7F9FC;">
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; border-bottom: 1px solid #E6EBF1;">
                                                        Payment to Velocity
                                                        </td>
                                                        <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; text-align: right; border-bottom: 1px solid #E6EBF1;">
                                                        [amount]
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; color: #21293e; font-weight: bold; background: #F7F9FC;">
                                                        Amount Paid
                                                        </td>
                                                        <td style="padding: 10px 0;color: #21293e;  font-weight: bold; background: #F7F9FC; text-align: right;">
                                                        [amount]
                                                        </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:30px 50px 0; background: #fff; font-size:15px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:left; color: #605f73; ">
                                            <p style="border-top: 1px solid #f3f6f7;
                                                                        border-bottom: 1px solid #f3f6f7;
                                                                        padding: 25px 0;
                                                                    ">
                                                If you have any questions, contact us at <a href="mailto:info@vgusa.com" style="color: #1d64c5;">info@vgusa.com</a> or call at  <br><span style="color: #1d64c5;"> 631-201-0703</span>
                                            </p>
                                            </td>
                                        </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //payment send to admin
        $data[] = [
            'subject'   =>  'Payment successful',
            'title'     =>  'Payment successful (sent to admin)',
            'temp_code' =>  'PYMNA',
            'template'  =>  '<tbody>
                            <tr>
                                <td class="td" align="center" style="width:650px; min-width:650px; font-size:0pt; line-height:0pt; padding:0; margin:0; font-weight:normal; padding: 0;">
                                <table width="100%" border="0" align="center" cellspacing="0" cellpadding="0" style="border-radius: 5px;">
                                    <tbody>
                                    <tr>
                                    <td>
                                        <!-- Header -->
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                        <tbody>
                                        <tr>
                                            <td style=" padding: 0 50px 10px; background: #fff; ">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #FCFDFE; border: 1px solid #f3f6f7;">
                                                <tr>
                                                <td style=" padding: 20px 50px 0; background: #FCFDFE; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 15px; font-weight: bold; color: #888896;">
                                                    Hello Velocity,<br>
                                                </td>
                                                </tr>
                                                <tr>
                                                <td style="padding:10px 50px 20px; background: #FCFDFE; font-size:16px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:center; color: #605f73;">
                                                We have just received a Credit Card payment (Card Number ** ** [card_number]) from <a href="[merchant_view_link]" target="_blank">[merchant_name]</a>. The amount paid was [amount] on [date].
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:15px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:center; color: #7a7a7a;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Amount Paid
                                                </td>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Date Paid
                                                </td>
                                                <td style=" text-align:center;  padding: 5px 0; font-size: 14px; color: #999; text-transform: uppercase;">
                                                    Payment Method
                                                </td>
                                                </tr>
                                                <tr>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    [amount]
                                                </td>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    [date]
                                                </td>
                                                <td style="padding: 5px 0; font-size: 16px; color: #444; text-align: center;">
                                                    Visa - [card_number]
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:30px 50px 0; background: #fff; font-size:18px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #7a7a7a;">
                                            <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                <tr>
                                                <td style="padding: 15px 30px; background: #F7F9FC;">
                                                    <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                                    <tr>
                                                        <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; border-bottom: 1px solid #E6EBF1;">
                                                        Payment to Velocity
                                                        </td>
                                                        <td style="padding: 10px 0; color: #88898c; background: #F7F9FC; text-align: right; border-bottom: 1px solid #E6EBF1;">
                                                        [amount]
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <td style="padding: 10px 0; color: #21293e; font-weight: bold; background: #F7F9FC;">
                                                        Amount Paid
                                                        </td>
                                                        <td style="padding: 10px 0;color: #21293e;  font-weight: bold; background: #F7F9FC; text-align: right;">
                                                        [amount]
                                                        </td>
                                                    </tr>
                                                    </table>
                                                </td>
                                                </tr>
                                            </table>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td style="padding:30px 50px 0; background: #fff; font-size:15px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 24px; text-align:left; color: #605f73; ">
                                            <p style="border-top: 1px solid #f3f6f7;
                                                                        border-bottom: 1px solid #f3f6f7;
                                                                        padding: 25px 0;
                                                                    ">
                                                If you have any questions, contact us at <a href="mailto:info@vgusa.com" style="color: #1d64c5;">info@vgusa.com</a> or call at  <br><span style="color: #1d64c5;"> 631-201-0703</span>
                                            </p>
                                            </td>
                                        </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // investor ach processing report
        $data[] = [
            'subject'   =>  'Investor ACH Processing Report For [date]',
            'title'     =>  'Investor ACH Processing Report',
            'temp_code' =>  'IAPR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Investor ACH Processing Report For [date]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[totalCount]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Settled Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
                                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[debitAcceptedAmount]</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[creditAcceptedAmount]</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Processing Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
                                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[debitProcessingAmount]</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[creditProcessingAmount]</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Returned Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
                                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[debitReturnedAmount]</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[creditReturnedAmount]</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[date]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[checked_time]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // investor ach recheck report
        $data[] = [
            'subject'   =>  'Investor ACH Recheck Report For [date]',
            'title'     =>  'Investor ACH Recheck Report',
            'temp_code' =>  'IARR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Investor ACH Recheck Report For [date]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Total:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[totalCount]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Settled Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
                                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[debitAcceptedAmount]</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[creditAcceptedAmount]</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Returned Amount:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">
                                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Debit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[debitReturnedAmount]</td>
                                                        </tr>
                                                        <tr>
                                                            <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Credit:</th>
                                                            <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[creditReturnedAmount]</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Date:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[date]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d;">[checked_time]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // investor debit/credit request
        $data[] = [
            'subject'   =>  'Ach [type] Requested',
            'title'     =>  'ACH Credit/Debit Request',
            'temp_code' =>  'ACDR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Ach [type] Requested
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    Hello [Creator]! <br>
                                    [creator_name] has initiated a request to [text_type] as a part of ACH payment.
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount requested:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[amount]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Date of request:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[date]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach request settlement
        $data[] = [
            'subject'   =>  'Ach [type] Request Settled',
            'title'     =>  'ACH (Debit/Credit) Settlement Request',
            'temp_code' =>  'ACSR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Ach [type] Request Settled
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                Hello <a target="_blank" href="[investor_view_link]">[investor_name]</a>! <br>
                                The ACH [type] request initiated was processed successfully.
                                <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                    <thead>
                                    <tr>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount requested:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[amount]</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Date of request:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[date]</td>
                                    </tr>
                                    <tr hidden>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Present Liquidity:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[liquidity]</td>
                                    </tr>
                                    </thead>
                                </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach request returned
        $data[] = [
            'subject'   =>  'Ach [type] Request Returned',
            'title'     =>  'ACH (Debit/Credit) Returned',
            'temp_code' =>  'ACRR',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Ach [type] Request Returned
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                Hello <a target="_blank" href="[investor_view_link]">[investor_name]</a>! <br>
                                The ACH [type] request was returned.
                                <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                    <thead>
                                    <tr>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[amount]</td>
                                    </tr>
                                    <tr>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Date of request:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[date]</td>
                                    </tr>
                                    <tr hidden>
                                        <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Present Liquidity:</th>
                                        <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[liquidity]</td>
                                    </tr>
                                    </thead>
                                </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ACH delete pending
        $data[] = [
            'subject'   =>  'Investor ACH Processing For More Than 9 Days as of [date]',
            'title'     =>  'Investor ACH Delete Pending',
            'temp_code' =>  'ACDP',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Investor ACH Processing For More Than 9 Days as of [date]
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="    
                                    font-size: 16px;
                                    line-height: 32px;
                                    color: #000;
                                    text-align: justify;
                                    letter-spacing: 0em;
                                    padding:20px 25px;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;"
                                >
                                    There are [totalCount] Investor ACH processing transactions for more than 9 days as of [date]. Click the following link to delete it.    
                                </td>													
                            </tr>
                            <tr>
                                <td class="content" style="padding: 25px 0 60px 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;" align="center">
                                    <a href="[confirm_url]" id="recon_yes" style="padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px; ">Delete</a>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        //Merchant ach balance difference
        $data[] = [
            'subject'   =>  'Merchants with difference between ACH balance and actual balance',
            'title'     =>  'Merchants with difference between ACH balance and actual balance',
            'temp_code' =>  'MACHD',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                    <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Merchants with difference between ACH balance and actual balance
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    <table width="100%" border="1" cellspacing="0" cellpadding="0">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time</th>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[date_time]</th>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Merchants with difference between ACH balance and actual balance</th>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[count]</th>
                                            </tr>
                                        </thead>
                                        </tbody>
                                    </table>                            
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // // Merchant unit test
        // $data[] = [
        //     'subject'   =>  'Merchant Unit Test',
        //     'title'     =>  'Merchant Unit Test',
        //     'temp_code' =>  'MUNIT',
        //     'template'  =>  '<tr>
        //                         <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
        //                             <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
        //                             Merchant Unit Test
        //                             </span>
        //                         </td>
        //                     </tr>
        //                     <tr>
        //                         <td class="full-width" style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
        //                             <table width="100%" border="1" cellspacing="0" cellpadding="0">
        //                                 <thead>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">Checked Time</th>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[date_time]</th>
        //                                     </tr>
        //                                     <tr>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[type]</th>
        //                                         <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947;">[count]</th>
        //                                     </tr>
        //                                 </thead>
        //                                 </tbody>
        //                             </table>
        //                         </td>
        //                     </tr>',
        //     'type'      =>  'email',
        //     'enable'    =>  1,
        //     'created_at'=>  date('Y-m-d H:i:s')
        // ];
        // Two factor confirmation
        $data[] = [
            'subject'   =>  "You've enabled two-step verification",
            'title'     =>  'Two step verification enable',
            'temp_code' =>  'TWFEN',
            'template'  =>  "<tr>
                                <td class='logo' style='    
                                    font-size: 22px;
                                    color: #45485d;
                                    line-height: 30px;
                                    padding: 30px 0 0;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;
                                    font-weight: 700;
                                    text-align: center;'>
                                    Hi ,
                                </td>													
                            </tr>
                            <tr>
                                <td class='content' style='    
                                    font-size: 16px;
                                    line-height: 32px;
                                    color: #000;
                                    text-align: justify;
                                    letter-spacing: 0em;
                                    padding:20px 25px;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;'>

                                    We'd like to confirm that you enabled two-step verification on the account [email].
                                    The next time you log in with your email address and password, you'll need to enter a 6-digit code to access your account.
                                    If you can't use your phone, you can enter the emergency recovery key you saved during setup.                                
                                </td>													
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // two factor disable
        $data[] = [
            'subject'   =>  "You've disabled two-step verification",
            'title'     =>  'Two step verification disable',
            'temp_code' =>  'TWFD',
            'template'  =>  "<tr>
                                <td class='logo' style='    
                                    font-size: 22px;
                                    color: #45485d;
                                    line-height: 30px;
                                    padding: 30px 0 0;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;
                                    font-weight: 700;
                                    text-align: center;'>
                                Hi ,
                                </td>													
                            </tr>
                            <tr>
                                <td class='content' style='
                                    font-size: 16px;
                                    line-height: 32px;
                                    color: #000;
                                    text-align: justify;
                                    letter-spacing: 0em;
                                    padding:20px 25px;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;'>
                                We'd like to confirm that you disabled two-step verification on the account [email].
                                You will no longer have the protection of a second login step.
                                If you want to enable two-step verification again, go back to the <a href='[action_link]'>two-step verification page</a>.
                                </td>													
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // ach deficit
        $data[] = [
            'subject'   =>  '[future_payments_count] Scheduled ACH Payments left',
            'title'     =>  'ACH Deficit',
            'temp_cide' =>  'ACHDF',
            'template'  =>  "<tr>
                                <td class='logo' style='padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;'>
                                    <span style='padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;'>
                                    [future_payments_count] Scheduled ACH Payments left
                                    </span>
                                </td>
                            </tr>
                            <tr>
                                <td class='content' style=' 
                                    font-size: 16px;
                                    line-height: 32px;
                                    color: #000;
                                    text-align: justify;
                                    letter-spacing: 0em;
                                    padding:20px 25px;
                                    background: #fff;
                                    font-family: Arial, sans-serif, Helvetica, Verdana;'
                                >
                                    [future_payments_count] scheduled ACH payments left for [merchant_name]. There are [makeup_payments] ACH Payments of Payment Amount [default_payment_amount] that need to be added to complete 100% RTR. 
                                    Please login and update.
                                </td>													
                            </tr>  
                            <tr>
                                <td class='content' style='padding: 25px 0 60px 0; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana;' align='center'>
                                    <a href='[url]' id='recon_yes' style='padding: 12px 45px; text-decoration: none; border-radius: 35px; color: #fff; background: #56d46f; font-size: 16px; font-weight: bold;  display: inline-block; margin: 0 5px;'>Merchant's ACH Terms</a>
                                </td>
                            </tr>",
            'type'      =>  'email',
            'enable'    =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // merchant ach credit request
        $data[] = [
            'subject'   =>  'Merchant ACH Credit Requested',
            'title'     =>  'Merchant ACH Credit Requested',
            'temp_code' =>  'MACC',
            'template'  =>  '<tr>
                                <td class="logo" style="padding: 15px 50px; font-size:0pt; line-height:0pt; text-align:center; background: #fff; border-radius: 0;">
                                <span style="padding: 10px 50px 10px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 26px; text-align:center; font-size: 22px; font-weight: 700; color: #70748e;">
                                    Merchant ACH Credit Requested
                                </span>
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:25px 50px 5px; background: #fff; font-size:22px; font-family: Arial, sans-serif, Helvetica, Verdana; line-height: 36px; text-align:left; color: #3c3c3c;">
                                    Hello ! <br>
                                    Admin [creator_name] has initiated a request for <a href="[merchant_view_link]">[merchant_name]</a> as a part of ACH Credit payment.
                                    <table width="100%" cellspacing="0" cellpadding="0" border="1">
                                        <thead>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Amount requested:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[payment_amount]</td>
                                            </tr>
                                            <tr>
                                                <th style="padding: 10px 50px 10px;font-weight: bold; color: #100947; white-space: nowrap;">Time of request:</th>
                                                <td style="padding: 10px 50px 10px;font-weight: bold; color: #3d3d3d; white-space: nowrap;">[checked_time]</td>
                                            </tr>
                                        </thead>
                                    </table>
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enabled'   =>  1,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        // marketing offers
        $data[] = [
            'subject'   =>  '[title]',
            'title'     =>  'Marketing offers',
            'template'  =>  '<tr>
                                <td class="logo" style="border:0; font-size:22px; color: #70748e; line-height:0pt; padding: 0 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700; ">
                                    Hi [name],
                                </td>
                            </tr>
                            <tr>
                                <td class="content" style="border:0; font-size:22px;  line-height: 36px; color: #1f244c; letter-spacing: -0.04em; padding: 45px 30px 10px 30px; background: #fff;  font-family: Arial, sans-serif, Helvetica, Verdana; font-weight: 700;">
                                    [offer]
                                </td>
                            </tr>',
            'type'      =>  'email',
            'enabled'   =>  1,
            'temp_code' =>  null,
            'created_at'=>  date('Y-m-d H:i:s'),
        ];
        DB::table('template')->insert($data);
    }
}
