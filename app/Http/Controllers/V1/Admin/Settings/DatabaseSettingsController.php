<?php

namespace Crater\Http\Controllers\V1\Admin\Settings;

use Crater\Http\Controllers\Controller;
use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class DatabaseSettingsController extends Controller
{
    /**
     * Check if database settings are configured
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function check()
    {
        try {
            $company = Company::find(request()->header('company'));
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            $hasSettings = CompanySetting::where('company_id', $company->id)
                ->whereIn('option', [
                    'database_connection_host',
                    'database_connection_port',
                    'database_connection_name',
                    'database_connection_username',
                    'database_connection_password'
                ])
                ->count() === 5;

            return response()->json([
                'success' => true,
                'hasSettings' => $hasSettings
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to check database settings: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to check database settings: ' . $e->getMessage()
            ], 500);
        }
    }

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

            // Run custom fields seeder
            Artisan::call('db:seed', [
                '--database' => 'company',
                '--class' => 'Database\Seeders\CustomFieldsSeeder',
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