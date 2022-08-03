<?php

//namespace App\Helpers;

function unqID()
{
    return md5(uniqid(rand(), true));
}

function session_set($key, $val)
{
    if ($key) {
        Session::put($key, $val);
    }
}

function percentage($percentage, $total)
{
    if (is_numeric($total)) {
        return ($percentage / 100) * $total;
    }

    return $total;
}
function calculatePercentage($oldFigure, $newFigure)
{
    if (! $oldFigure || ! $newFigure) {
        return 0;
    }
    $percentChange = (($oldFigure - $newFigure) / $oldFigure) * 100;

    return 100 - round(abs($percentChange));
}

function dynamic_report_array($data1,$data2){
    $data = array();
    $arrayAB = array_merge(json_decode(json_encode($data1,true),true),json_decode(json_encode($data2,true),true));
    foreach ($arrayAB as $value) {
        $id = $value['id'];
        if (!isset($data[$id])) {
            $data[$id] = array();
        }
        $data[$id] = array_merge($data[$id],$value);
    }
    return $data;
}

function datetime($datetime)
{
    return date('m-d-Y h:i:s a', strtotime($datetime));
}

function dateUS($datetime)
{
    return date('m-d-Y', strtotime($datetime));
}

function mask_string(string $string = null)
{
    if (! $string) {
        return null;
    }
    $length = strlen($string);
    $visibleCount = (int) round($length / 4);
    $hiddenCount = $length - ($visibleCount * 2);

    return substr($string, 0, $visibleCount).str_repeat('*', $hiddenCount).substr($string, ($visibleCount * -1), $visibleCount);
}

function carry_fow_amount($id)
{
    $carry_forwards = session('carry_forwards');
    if ($carry_forwards) {
        return ($carry_forwards[$id]) ? $carry_forwards[$id] : 0;
    } else {
        return 0;
    }
}

function ET_To_UTC_Time($time, $format = 'date')
{
    if(in_array($time,['','00:00','00:00:00'])){
        return '';
    }
    $time = date('M d, Y H:i:s', strtotime($time));
    $date = \Carbon\Carbon::createFromFormat('M d, Y H:i:s', $time, \FFM::timezone());
    $date->setTimezone('UTC');
    if ($format == 'time') {
        return $date->format('H:i');
    } elseif ($format == 'datetime') {
        return $date->format('Y-m-d H:i:s');
    }

    return $date->format('Y-m-d');
}
function ET_To_UTC_TimeOnly($time)
{
    $date = \Carbon\Carbon::createFromFormat('H:i', $time, 'America/New_york');
    $date->setTimezone('UTC');

    return $date->format('H:i');
}
function get_user_name_with_session($id)
{
    $all_users = session('all_users');

    return isset($all_users[$id]['name']) ? $all_users[$id]['name'] : '--';
}

function get_name_with_session($session_name,$id)
{
    $all_users = session($session_name);

    return isset($all_users[$id]['name']) ? $all_users[$id]['name'] : '--';
}

function changeCase($string, $capitalizeFirstCharacter = false)
{
    $str = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

    if (!$capitalizeFirstCharacter) {
        $str[0] = strtolower($str[0]);
    }

    return ucwords(implode(' ',preg_split('/(?=[A-Z])/', $str)));

}
function report_array($data1,$data2){
	$data = array();
	$arrayAB = array_merge(json_decode(json_encode($data1,true),true),json_decode(json_encode($data2,true),true));
	foreach ($arrayAB as $value) {
		$id = $value['id'];
		if (!isset($data[$id])) {
			$data[$id] = array();
		}
		$data[$id] = array_merge($data[$id],$value);
	}
	return $data;
}
