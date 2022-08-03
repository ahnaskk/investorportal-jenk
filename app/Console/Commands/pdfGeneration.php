<?php

namespace App\Console\Commands;

use App\Library\Repository\Interfaces\IRoleRepository;
use App\Library\Repository\Interfaces\IUserRepository;
use App\Settings;
use Illuminate\Console\Command;

class pdfGeneration extends Command
{
    protected $signature = 'PDFGeneration:pdfgeneration';
    protected $description = 'PDF Generation';
    protected $role;
    protected $user;

    public function __construct(IRoleRepository $role, IUserRepository $user)
    {
        parent::__construct();
        $this->role = $role;
        $this->user = $user;
    }

    public function handle()
    {
        $hide = (Settings::value('hide') == 1) ? 1 : 0;
        $investors = [15];
        $investors = $this->role->allInvestors()->where('auto_generation', 1); // investors
        $filters = [
            'date_start'    => '',
            'date_end'      => '',
            'from'          => 'pdfGeneration',
            'send_mail'     => true,
            'merchants'     => '',
            // 'whole_portfolio'=> '',
            'recurrence'    => '',
            'hide'          => $hide,
            'generationtype'=> 1,
        ];
        if (! empty($investors)) {
            $result = $this->user->generatePDFCSV($investors, $filters);
            dd($result);
        }
    }
}
