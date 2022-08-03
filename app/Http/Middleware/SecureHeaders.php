<?php
namespace App\Http\Middleware;
use Closure;
class SecureHeaders
{
    // Enumerate headers which you do not want in your application's responses.
    // Great starting point would be to go check out @Scott_Helme's:
    // https://securityheaders.com/
    private $unwantedHeaderList = [
        'X-Powered-By',
        'Server',
    ];
    public function handle($request, Closure $next) {
        $this->removeUnwantedHeaders($this->unwantedHeaderList);
        $response = $next($request);
        $response->headers->set('Referrer-Policy', 'no-referrer-when-downgrade');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('X-Frame-max-age', '24');
         $response->headers->set('Content-Security-Policy', "default-src 'self' 'unsafe-inline' 'unsafe-eval'  *.pusher.com https://fonts.gstatic.com  https://fonts.googleapis.com https://api.stripe.com https://connect.stripe.com https://www.routingnumbers.info https://files.stripe.com data:");

        return $response;
    }
    private function removeUnwantedHeaders($headerList) {
        foreach ($headerList as $header)
        header_remove($header);
    }
}
?>