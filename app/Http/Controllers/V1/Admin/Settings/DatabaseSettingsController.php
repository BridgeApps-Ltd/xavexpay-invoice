<?php

namespace Crater\Http\Controllers\V1\Admin\Settings;

use Crater\Http\Controllers\Controller;
use Crater\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class DatabaseSettingsController extends Controller
{
    /**
     * Run database migrations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function runMigrations()
    {
        try {
            $company = Company::find(request()->header('company'));
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            $credentials = $company->getDatabaseConnection();
            
            if (!$credentials) {
                return response()->json([
                    'success' => false,
                    'message' => 'Database settings not found'
                ], 404);
            }

            // Configure the connection
            config([
                'database.connections.company' => [
                    'driver' => 'mysql',
                    'host' => $credentials['database_host'],
                    'port' => $credentials['database_port'],
                    'database' => $credentials['database_name'],
                    'username' => $credentials['database_username'],
                    'password' => $credentials['database_password'],
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => false,
                    'engine' => null,
                ]
            ]);

            // Set the connection
            DB::setDefaultConnection('company');

            // Run migrations
            Artisan::call('migrate', [
                '--database' => 'company',
                '--path' => 'database/migrations/company',
                '--force' => true
            ]);

            // Run seeders
            Artisan::call('db:seed', [
                '--database' => 'company',
                '--class' => 'Database\Seeders\CompanySeeder',
                '--force' => true
            ]);

            // Setup company defaults
            $company->setupDefaultData();
            $company->setupDefaultUnits();
            $company->setupDefaultPaymentMethods();

            return response()->json([
                'success' => true,
                'message' => 'Migrations completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to run migrations: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to run migrations: ' . $e->getMessage()
            ], 500);
        }
    }
} 