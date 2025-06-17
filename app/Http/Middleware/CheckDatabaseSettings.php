<?php

namespace Crater\Http\Middleware;

use Closure;
use Crater\Models\CompanySetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class CheckDatabaseSettings
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Skip check for installation routes and database settings pages
        if ($request->is('installation*') || 
            $request->is('settings/database*') || 
            $request->is('api/v1/settings/database*') || 
            $request->is('api/v1/company/database-settings*') ||
            $request->is('api/v1/bootstrap') ||
            $request->is('api/v1/auth*') ||
            $request->is('api/v1/companies*') ||
            $request->is('api/v1/dashboard')) {
            return $next($request);
        }

        // Check if we're in installation process
        $installed = File::exists(storage_path('installed'));
        if (!$installed) {
            return $next($request);
        }

        $companyId = $request->header('company');
        
        if (!$companyId) {
            Log::error('Company ID not found in request header');
            return $next($request);
        }

        // Check if database settings exist
        $hasSettings = CompanySetting::where('company_id', $companyId)
            ->whereIn('option', [
                'database_connection_host',
                'database_connection_port',
                'database_connection_name',
                'database_connection_username',
                'database_connection_password'
            ])
            ->count() === 5;

        if (!$hasSettings) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database is not configured for this company'
                ], 422);
            }

            return redirect()->route('settings.database');
        }

        return $next($request);
    }
} 