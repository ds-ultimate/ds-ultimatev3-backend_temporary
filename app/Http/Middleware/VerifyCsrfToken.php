<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    
    /*
     * This will be completeley deactivated in the future
     * - Authentification will be handled via tokens (maybe selenium?)
     * - deactivate Laravel session cookie completly
     */
    
    protected $except = [
        "/error",
        "/basicSearch",
        "/extendedSearch",
    ];
}
