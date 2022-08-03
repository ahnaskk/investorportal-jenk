<?php

namespace App\Helpers;

function modelQuerySqlWithBinding($sql, $bindings)
{
    $sql_chunks = explode('?', $sql);
    $result = '';
    if (count($bindings) > 0 and count($sql_chunks) > 0) {
        foreach ($sql_chunks as $key => $sql_chunk) {
            if (isset($bindings[$key])) {
                $result .= $sql_chunk.'"'.$bindings[$key].'"';
            } else {
                $result .= $sql_chunk;
            }
        }
    } else {
        $result = $sql;
    }

    return $result;
}

function modelQuerySql($query)
{
    $sql = $query->toSql();
    $bindings = $query->getBindings();

    return modelQuerySqlWithBinding($sql, $bindings);
}

function getFileExtension($path)
{
    $extract = explode('.', $path);
    $lastExtension = $extract[count($extract) - 1];

    return strtolower($lastExtension);
}

function api_report_url($uri)
{
    return url('api/admin/report/'.$uri);
}

function api_download_url($uri)
{
    return api_report_url($uri).'?token='.\Auth::user()->getDownloadToken();
}
