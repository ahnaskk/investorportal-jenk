<?php

namespace App\Console;

use App\Settings;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        /*ACH commands section
        */

        //Getting automation time from settings
        $ach_automation = (Settings::where('keys', 'ach_merchant')->value('values'));
        $ach_automation = json_decode($ach_automation, true);
        $admin_email = Settings::where('keys', 'admin_email_address')->first()->values??'emailnotification22@gmail.com';
        

        $ach_status_time = $ach_automation['status_time'] ?? '07:45';
        $ach_double_check_time = $ach_automation['double_check_time'] ?? '08:00';
        $ach_difference_time = $ach_automation['difference_time'] ?? '08:45';
        $ach_request_time = $ach_automation['request_time'] ?? '14:30';
        $ach_notification_time = $ach_automation['notification_time'] ?? '16:00';

        $schedule->command('ach:doublecheck')->weekdays()->at(ET_To_UTC_TimeOnly($ach_double_check_time))->emailOutputTo($admin_email);

        $schedule->command('ach:status')->weekdays()->at(ET_To_UTC_TimeOnly($ach_status_time))->emailOutputTo($admin_email);

        $schedule->command('ach:difference')->sundays()->at(ET_To_UTC_TimeOnly($ach_difference_time))->emailOutputTo($admin_email);

        $schedule->command('ach:send')->weekdays()->at(ET_To_UTC_TimeOnly($ach_request_time))->emailOutputTo($admin_email);

        // $schedule->command('ach:requeststatus')->weekdays()->at(ET_To_UTC_TimeOnly($ach_notification_time))->emailOutputTo($admin_email);

        //ACH Syndicate Payment
        $ach_investor = (Settings::where('keys', 'ach_investor')->value('values'));
        $ach_investor = json_decode($ach_investor, true);

        $ach_syndicate_payment_time = $ach_investor['ach_syndicate_payment_time'] ?? '16:50';
        $ach_investor_status_time = $ach_investor['ach_investor_status_time'] ?? '07:50';
        $ach_investor_double_check_time = $ach_investor['ach_investor_double_check_time'] ?? '08:05';

        $schedule->command('ach:investorcheck')->weekdays()->at(ET_To_UTC_TimeOnly($ach_investor_status_time))->emailOutputTo($admin_email);

        // $schedule->command('ach:investorrecheck')->weekdays()->at(ET_To_UTC_TimeOnly($ach_investor_double_check_time))->emailOutputTo($admin_email);

        $schedule->command('ach:syndicate')->weekdays()->at(ET_To_UTC_TimeOnly($ach_syndicate_payment_time))->emailOutputTo($admin_email);
        /*ACH commands section ends
        */

        //New commands

        // $schedule->exec('apt-get autoremove && apt-get autoclean')->sundays()->at(ET_To_UTC_TimeOnly('00:00'))->emailOutputTo($admin_email);

        $schedule->command('PendingPaymentEmailNotification:pendingpaymentemailnotification')->dailyAt(ET_To_UTC_TimeOnly('00:00'))->emailOutputTo($admin_email);

        // $schedule->command('RollINSPayments:rollinspayemnts')->weekdays()->at(ET_To_UTC_TimeOnly('00:05'))->emailOutputTo($admin_email);

        $schedule->command('merchant:dbautotest')->dailyAt(ET_To_UTC_TimeOnly('00:02'))->emailOutputTo($admin_email);

        $schedule->command('api:notifyApiErrorResponse')->dailyAt(ET_To_UTC_TimeOnly('05:40'))->emailOutputTo($admin_email);

        $schedule->command('PendingPaymentSettledMail:PendingPaymentSettledMail')->dailyAt(ET_To_UTC_TimeOnly('06:00'))->emailOutputTo($admin_email);

        $schedule->command('merchant:unittest')->dailyAt(ET_To_UTC_TimeOnly('07:10'))->emailOutputTo($admin_email);

        $schedule->command('PendingPaymentMail:PendingPaymentMail')->dailyAt(ET_To_UTC_TimeOnly('07:30'))->emailOutputTo($admin_email);

        $schedule->command('MerchantActiveMail:merchantactivemail')->dailyAt(ET_To_UTC_TimeOnly('08:30'))->emailOutputTo($admin_email);

        $schedule->command('Notifications:sendpaymentnotifications')->dailyAt(ET_To_UTC_TimeOnly('17:00'))->emailOutputTo($admin_email);

        $schedule->command('backup:run --only-db')->dailyAt(ET_To_UTC_TimeOnly('22:00'))->emailOutputTo($admin_email);
        $schedule->command('WeeklyPDFGeneration:weeklypdfgeneration')->dailyAt(ET_To_UTC_TimeOnly('18:00'))->emailOutputTo($admin_email);

        // $schedule->command('db:staging')->dailyAt(ET_To_UTC_TimeOnly('22:09'))->emailOutputTo($admin_email);

        // $schedule->command('DealsOnPause:DealsOnPause')->dailyAt(ET_To_UTC_TimeOnly('22:10'))->emailOutputTo($admin_email);

        //$schedule->command('db:export')->dailyAt(ET_To_UTC_TimeOnly('23:33'))->emailOutputTo($admin_email);

        $sms_sending_time = $ach_automation['sms_sending_time'] ?? '12:00';

        // $schedule->command('send:message')->daily()->at(ET_To_UTC_TimeOnly($sms_sending_time))->emailOutputTo($admin_email);

        //Old cron commands

        // $schedule->command('php artisan backup:run')->daily();

        // $schedule->command('EmailNotificationCron:emailnotificationcron')
        //     ->daily();

        // $schedule->command('WeeklyReportCron:weeklyreportcron')
        //     ->weekly(); // not urgent.
        // $schedule->command('WeeklyNotificationCron:weeklynotificationcron')
        //     ->weekly();

        // $schedule->command('MerchantEmailNotifications:merchantemailnotifications')
        //     ->dailyAt(ET_To_UTC_TimeOnly('01:00'));

        // $schedule->command('Notifications:sendMerchantAppPaymentNotifications')
        //     ->daily();

        // $schedule->command('PendingPayment:pendingpayment')
        //     ->daily();
        // $schedule->command('InterestForInvestor:interestforinvestor')
        //     ->daily();

        // $schedule->command('backup:restore')
        //     ->weekly();

        // $schedule->command('Notifications:sendpaymentnotifications')
        //     ->daily();

        // $schedule->command('PDFGeneration:pdfgeneration')
        //     ->weekly();

        // $schedule->command('merchant:dbautotest')
        //     ->daily();
        // $schedule->command('MerchantActiveMail:merchantactivemail')
        //     ->daily();
        // $schedule->command('logs:delete')
        //     ->weekly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
