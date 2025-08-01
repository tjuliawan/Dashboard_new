<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class IncreaseExecutionTime
{
    public function handle(Request $request, Closure $next)
    {
        // Set waktu eksekusi dan memori maksimum
        ini_set('max_execution_time', 120);
        set_time_limit(120);
        ini_set('memory_limit', '512M'); // Opsional, jika perlu

        return $next($request);
    }
}

