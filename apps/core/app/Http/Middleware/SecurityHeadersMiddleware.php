<?php

namespace App\Http\Middleware;

use Closure;

/**
 * Security Headers Middleware (AC-M8-01, §30.5, DEC-036).
 *
 * Implements defense-in-depth HTTP response hardening, setting strict
 * browser security policies and stripping server fingerprinting headers.
 */
class SecurityHeadersMiddleware
{
    /**
     * Handle an incoming request and apply security headers to response.
     *
     * @param mixed $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        // Security policies
        $headers = [
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains; preload',
            'X-Content-Type-Options'    => 'nosniff',
            'X-Frame-Options'           => 'DENY',
            'Referrer-Policy'           => 'strict-origin-when-cross-origin',
            'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Content-Security-Policy'   => "default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self'; connect-src 'self'; frame-ancestors 'none'; base-uri 'self'; form-action 'self';",
        ];

        foreach ($headers as $key => $val) {
            if (method_exists($response, 'header')) {
                $response->header($key, $val);
            } elseif (isset($response->headers) && method_exists($response->headers, 'set')) {
                $response->headers->set($key, $val);
            }
        }

        // Prevent technology fingerprinting
        if (function_exists('header_remove')) {
            @header_remove('X-Powered-By');
            @header_remove('Server');
        }

        return $response;
    }
}
