<?php

namespace App\Http\Middleware;

use App\ApiLog;
use Closure;

class ApiDataLogger
{
    private $startTime;

    public function handle($request, Closure $next)
    {
        $this->startTime = microtime(true);

        return $next($request);
    }

    public function terminate($request, $response)
    {
        if (config('settings.api_datalogger')) {
            $data = [
                'api_name'   => $request->getPathInfo(),
                'date'       => date('Y-m-d H:i:s'),
                'ip_address' => $request->ip(),
                'method'     => $request->method(),
                'request'    => json_encode($request->all()),
                'response'   => $response->getContent(),
                'mail_status'=>0,
            ];
            $ApiLog = ApiLog::create($data);
        }
    }
}
