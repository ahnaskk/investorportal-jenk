<?php

namespace App\Models;

use App\Merchant;
use App\Settings;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class Message extends Model
{
    const UNKNOWN = 0;
    const PENDING = 1;
    const COMPLETED = 2;
    protected $fillable = [
        'date',
        'model_name',
        'model_id',
        'message',
        'mobile',
        'remark',
        'status',
        'type',
        'creator_id',
    ];

    public static function rules($id = 0, $merge = [])
    {
        return array_merge([
            'model_name'=> 'required',
            'model_id'  => 'required',
            'message'   => 'required',
        ],
        $merge);
    }

    public function Model()
    {
        $explode = explode('\\', $this->model_name);
        if ($explode[1] == 'Merchant') {
            return $this->belongsTo(Merchant::class, 'model_id');
        }
    }

    public static function selfCreate($data)
    {
        try {
            $data['date'] = date('Y-m-d');
            $data['creator_id'] = (Auth::check()) ? Auth::user()->id : null;
            $validator = \Validator::make($data, self::rules());
            if ($validator->fails()) {
                foreach ($validator->errors()->getMessages() as $key => $value) {
                    throw new \Exception($value[0]);
                }
            }
            $Self = self::create($data);
            $return['result'] = 'success';
        } catch (\Exception $e) {
            $return['result'] = $e->getMessage();
        }

        return $return;
    }

    public static function statusOptions()
    {
        return $statuses = [
            // Self::UNKNOWN  =>'UNKNOWN',
            self::PENDING  =>'PENDING',
            self::COMPLETED=>'COMPLETED',
        ];
    }

    public function getStatusNameAttribute()
    {
        $statuses = $this->statusOptions();

        return $statuses[$this->status];
    }

    public function sendMessage()
    {
        try {
            $api_key = config('settings.communication_portal_api_key');
            if (! $api_key) {
                throw new \Exception('Empty Api Key', 1);
            }
            $sendor_id = config('settings.communication_portal_sendor_id');
            if (! $sendor_id) {
                throw new \Exception('Empty Sendor Id', 1);
            }
            if ($this->Model) {
                if ($this->Model->cell_phone) {
                    $this->mobile = $this->Model->cell_phone;
                }
            }
            //for testing
            // $this->mobile='15866367106';
            if (empty($this->mobile)) {
                throw new \Exception('Empty Mobile', 1);
            }
            if (! $this->message) {
                throw new \Exception('Empty Message', 1);
            }
            $this->mobile = $this->TrimMobileNo($this->mobile);
            if (strlen($this->mobile) != '11') {
                $this->mobile = $this->InternationalizeNo($this->mobile);
            }
            if (strlen($this->mobile) != '11') {
                throw new \Exception('No Should Be 11 digit', 1);
            }
            $mobile = $this->mobile;
            $sms = htmlentities($this->message);
            $url = config('settings.communication_portal_website');
            $url .= '/sms/api?action=send-sms';
            $url .= "&api_key=$api_key";
            $url .= "&to=$mobile";
            $url .= "&from=$sendor_id";
            $url .= '&email='.$this->Model->notification_email ?? '';
            $url .= '&first_name='.$this->Model->name ?? '';
            $url .= '&last_name=';
            $url .= '&company=';
            $url .= "&sms=$sms";
            $request = Http::asForm()->get($url);
            $response = $request->body();
            $response = json_decode($response, true);
            if ($response['code'] != 'ok') {
                throw new \Exception($response['message'], 1);
            }
            $response['result'] = 'success';
        } catch (\Exception $e) {
            $response['result'] = $e->getMessage();
        }
        $response['response'] = json_encode($response);

        return $response;
    }

    public static function TrimMobileNo($mobile)
    {
        $mobile = str_replace(' ', '', $mobile);
        $mobile = str_replace('(', '', $mobile);
        $mobile = str_replace(')', '', $mobile);
        $mobile = str_replace('-', '', $mobile);
        preg_match_all('!\d+!', $mobile, $matches);
        $mobile = trim($mobile);
        // $mobile = intval($mobile);

        return $mobile;
    }

    public static function InternationalizeNo($number)
    {
        if (! $number) {
            return '';
        }
        $country_codes = [
            'Qatar' => '974',
            'In'    => '91',
            'UK'    => '44',
            'US'    => '1',
        ];
        $code = 'US';
        $default_country_code = '1';
        //Remove any parentheses and the numbers they contain:
        $number = preg_replace('/\\([0-9]+?\\)/', '', $number);
        //Strip spaces and non-numeric characters:
        $number = preg_replace('/[^0-9]/', '', $number);
        //Strip out leading zeros:
        // $number = ltrim($number, '0');
        //Look up the country dialling code for this number:
        if (array_key_exists($code, $country_codes)) {
            $pfx = $country_codes[$code];
        } else {
            $pfx = $default_country_code;
        }
        //Check if the number doesn't already start with the correct dialling code:
        if (! preg_match('/^'.$pfx.'/', $number)) {
            $number = $pfx.$number;
        } else {
            if (strlen($number) != 11) {
                $number = $pfx.$number;
            }
        }

        return $number;
    }
}
