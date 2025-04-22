<?php

namespace Crater\Http\Middleware;

use Closure;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompanyDatabaseMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = auth()->user();
        
        if ($user) {
            try {
                $company = $user->companies()->first();
                
                if ($company) {
                    $settings = CompanySetting::where('company_id', $company->id)
                        ->where('option', 'database_connection')
                        ->first();

                    if ($settings && $settings->database_host) {
                        // Configure the company-specific database connection
                        config([
                            'database.connections.company' => [
                                'driver' => 'mysql',
                                'host' => $settings->database_host,
                                'port' => $settings->database_port,
                                'database' => $settings->database_name,
                                'username' => $settings->database_username,
                                'password' => $settings->database_password,
                                'charset' => 'utf8',
                                'collation' => 'utf8_unicode_ci',
                                'prefix' => '',
                                'strict' => false,
                                'engine' => null,
                            ]
                        ]);

                        // Set as default connection for this request
                        DB::setDefaultConnection('company');
                        
                        // Test the connection
                        DB::connection('company')->getPdo();
                    }
                }
            } catch (\Exception $e) {
                Log::error('Database connection error for company ' . ($company ? $company->id : 'unknown') . ': ' . $e->getMessage());
                // Fall back to default connection if company database connection fails
                DB::setDefaultConnection(config('database.default'));
            }
        }

        return $next($request);
    }
} 