<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

class SendMessage extends Command
{
    protected $signature = 'send:message';
    protected $description = 'Send Message To the Given Mobile No';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $Message = Message::where('status', '!=', Message::COMPLETED);
        $Message = $Message->get();
        echo "\n -------Time : ".date('Y-m-d H:i:s')."-------. \n";
        $count = count($Message->toArray());
        echo "\n -------Total Pending Count : ".$count."-------. \n";
        foreach ($Message as $key => $value) {
            try {
                echo "\n ____".$value->Model->name.':';
                $return_function = $value->sendMessage();
                if ($return_function['result'] != 'success') {
                    throw new \Exception($return_function['result'], 1);
                }
                $value->status = Message::COMPLETED;
                $return['result'] = 'success';
                $return['result'] = $return_function['result'];
            } catch (\Exception $e) {
                $return['result'] = $e->getMessage();
            }
            $value->remark = $return['result'];
            echo ':'.$return['result']."____ \n";
            $value->save();
        }
    }
}
