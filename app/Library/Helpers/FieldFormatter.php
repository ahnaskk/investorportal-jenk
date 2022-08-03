<?php

namespace App\Library\Helpers;

use Carbon\Carbon;

class FieldFormatter
{
    public function __construct()
    {
        $portfolio_arr[343333]['portfolio_difference'] = $this->dec31_0(5017.31);
        $this->portfolio_arr = $portfolio_arr;
    }

    public function dec31_0($amount)
    {
        $current_date = date('Y-m-d');
        $date1 = date_create($current_date);
        $date2 = date_create('2020-01-04');
        $diff = date_diff($date1, $date2);
        $days = $diff->format('%a');

        return $amount * $days / 17;
    }

    function fees_array($start=0.00, $end=5, $denomination=0.25)
    {
        $value = $start;
        while ($value <= $end) {
            $syndication_fee_values[number_format($value,2)] = number_format($value,2);
            $value = $value + $denomination;
        }

          return $syndication_fee_values;
    
    }

    function fees_array_without_decimal($start=0, $end=10, $denomination=1)
    {
        $value = $start;
        while ($value <= $end) {
            $upsell_commission_values[number_format($value)] = number_format($value);
            $value = $value + $denomination;
        }

          return $upsell_commission_values;
    
    }

    public function dollar($field)
    {
        $field = $field ? $field : 0;
        $field = str_replace(',', '', $field);
        $field = (float) $field;

        return '$'.number_format($field, 2);
    }

    public function mask_cc($number)
    {
        if (strlen($number) <= 4) {
            return substr_replace($number, str_repeat('X', strlen($number) - 2), 0, strlen($number) - 2);
        }

        return substr_replace($number, str_repeat('X', strlen($number) - 4), 0, strlen($number) - 4);
    }

    public function adjustment($rtr = '', $id = 0)
    {
        return $rtr;
    }

    public function percent($field)
    {
        return round((float) $field, 2).'%';
    }

    public function percent3($field)
    {
        return number_format($field, 2).'%';
    }

    public function percent1($field)
    {
        return round($field, 6).'%';
    }

    public function date($date)
    {
        try {
            $format = 'm-d-Y';
            if (auth()->user()) {
                $user = auth()->user();
                $date_format = json_decode($user->date_format);
                if (! $date_format) {
                    $format = 'm-d-Y';
                } else {
                    $format = $date_format->dbFormat;
                }
            }
            $parse_date = Carbon::parse($date)->format('Y-m-d');

            return Carbon::createFromFormat('Y-m-d', $date)->format($format);
        } catch (\Exception $e) {
            return '';
        }
    }

    public function dbdate($date)
    {
        try {
            return Carbon::createFromFormat($this->defaultDateFormat('db'), $date)->setTimezone($this->timezone())->format('Y-m-d');
        } catch (\Exception $e) {
            return;
        }
    }

    public function defaultDateFormat($type)
    {
        try {
            if (auth()->user()) {
                $user = auth()->user();
                $date_format = json_decode($user->date_format);
                if ($type == 'format') {
                    if (! $date_format) {
                        $date_format = 'MM-DD-YYYY';
                    } else {
                        $date_format = $date_format->format;
                    }

                    return $date_format;
                } elseif ($type == 'db') {
                    if (! $date_format) {
                        $date_format = 'm-d-Y';
                    } else {
                        $date_format = $date_format->dbFormat;
                    }

                    return $date_format;
                }
            }else{
                if ($type == 'format') {
                    $date_format = 'MM-DD-YYYY';
                    return $date_format;
                } elseif ($type == 'db') {                    
                    $date_format = 'm-d-Y';  
                    return $date_format;
                } 
            }
        } catch (\Exception $e) {
            return;
        }
    }

    public function timezone()
    {
        return (auth()->user() && auth()->user()->timezone) ? auth()->user()->timezone : 'America/New_york';
    }

    public function datetime($date)
    {
        try {
            if (is_int($date)) {
                $date = date('Y-m-d H:i:s', $date);
            }
            $date = new Carbon($date);
            $dates = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
            $date = $dates->setTimezone($this->timezone());

            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($this->defaultDateFormat('db').' h:i A');
        } catch (\Exception $e) {
            return;
        }
    }

    public function datetimeExcel($date)
    {
        try {
            if (is_int($date)) {
                $date = date('Y-m-d H:i:s', $date);
            }
            $date = new Carbon($date);
            $dates = Carbon::createFromFormat('Y-m-d H:i:s', $date, 'UTC');
            $date = $dates->setTimezone($this->timezone());

            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($this->defaultDateFormat('db').' h_i_s');
        } catch (\Exception $e) {
            return;
        }
    }

    public function time($date)
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('H:i:s');
        } catch (\Exception $e) {
            return;
        }
    }

    public function datetimetodate($date)
    {
        try {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format($this->defaultDateFormat('db'));
        } catch (\Exception $e) {
            return;
        }
    }

    public function dbdatetime($date)
    {
        return Carbon::createFromFormat($this->defaultDateFormat('db').' H:i:s', $date)->format('Y-m-d H:i:s');
    }

    public function sr($field)
    {
        return number_format($field, 2);
    }

    public function viewableDocExtensions()
    {
        return ['png', 'jpg', 'jpeg', 'gif', 'pdf'];
    }

    public function viewableDocExtensionsGoogle()
    {
        return ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'rtf'];
    }

    public function viewableDocExtensionsMicrosoft()
    {
        return ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'rtf', 'odp', 'ods', 'odt'];
    }

    public function viewableImageExtensions()
    {
        return ['png', 'jpg', 'jpeg', 'gif', 'webp'];
    }

    public function portfolio_difference($user_id)
    {
        $portfolio_arr = $this->portfolio_arr;
        $portfolio_diff = 0;
        foreach ($portfolio_arr as $key => $portfolio_dt) {
            if ($key == $user_id) {
                $portfolio_diff = $portfolio_dt['portfolio_difference'];
            }
        }

        return $portfolio_diff;
    }

    public function total_portfolio_difference($user_array)
    {
        $portfolio_arr = $this->portfolio_arr;
        $portfolio_diff = 0;
        foreach ($portfolio_arr as $key => $portfolio_dt) {
            if (in_array($key, $user_array)) {
                $portfolio_diff = $portfolio_diff + $portfolio_dt['portfolio_difference'];
            }
        }

        return $portfolio_diff;
    }
}
