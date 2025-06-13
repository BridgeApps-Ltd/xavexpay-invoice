<?php

namespace Crater\Http\Controllers\V1\Settings;

use Crater\Http\Controllers\Controller;
use Crater\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Crater\Models\CompanySetting;

class DatabaseSettingsController extends Controller
{
    /**
     * Get database connection settings
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSettings(Request $request)
    {
        try {
            $companyId = $request->query('company_id');
            if (!$companyId) {
                Log::error('No company_id provided in request');
                return response()->json(['error' => 'Company ID is required'], 400);
            }

            $company = Company::find($companyId);
            if (!$company) {
                Log::error('Company not found', ['company_id' => $companyId]);
                return response()->json(['error' => 'Company not found'], 404);
            }

            $settings = $company->getDatabaseConnection();
            Log::info('Retrieved database settings', [
                'company_id' => $company->id,
                'has_settings' => !is_null($settings),
                'settings' => $settings ? array_merge($settings, ['database_password' => '***']) : null
            ]);

            if (!$settings) {
                // Return empty settings instead of null
                return response()->json([
                    'settings' => [
                        'database_host' => '',
                        'database_port' => '',
                        'database_name' => '',
                        'database_username' => '',
                        'database_password' => ''
                    ]
                ]);
            }

            return response()->json(['settings' => $settings]);
        } catch (\Exception $e) {
            Log::error('Error fetching database settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to fetch database settings'], 500);
        }
    }

    /**
     * Save database connection settings
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveSettings(Request $request)
    {
        try {
            Log::info('Starting to save database settings', [
                'request_data' => array_merge($request->all(), ['database_password' => '***'])
            ]);

            $validator = Validator::make($request->all(), [
                'company_id' => 'required|integer',
                'database_host' => 'required|string',
                'database_port' => 'required|integer',
                'database_name' => 'required|string',
                'database_username' => 'required|string',
                'database_password' => 'required|string'
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first()
                ], 422);
            }

            $company = Company::find($request->company_id);
            if (!$company) {
                Log::error('Company not found', ['company_id' => $request->company_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            Log::info('Found company', ['company_id' => $company->id]);

            // Test the connection first
            if (!$company->testDatabaseConnection($request->all())) {
                Log::error('Database connection test failed', [
                    'company_id' => $company->id,
                    'host' => $request->database_host
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to connect to database'
                ], 422);
            }

            Log::info('Database connection test successful');

            // Save the settings with the correct field names
            $credentials = [
                'database_host' => $request->database_host,
                'database_port' => $request->database_port,
                'database_name' => $request->database_name,
                'database_username' => $request->database_username,
                'database_password' => $request->database_password
            ];

            Log::info('Attempting to save credentials', [
                'company_id' => $company->id,
                'credentials' => array_merge($credentials, ['database_password' => '***'])
            ]);

            if ($company->saveDatabaseConnection($credentials)) {
                Log::info('Database settings saved successfully', ['company_id' => $company->id]);
                
                // Verify the settings were saved
                $savedSettings = \Crater\Models\CompanySetting::where('company_id', $company->id)
                    ->whereIn('option', [
                        'database_connection_host',
                        'database_connection_port',
                        'database_connection_name',
                        'database_connection_username',
                        'database_connection_password'
                    ])
                    ->get();
                
                Log::info('Verified saved settings', [
                    'company_id' => $company->id,
                    'settings_count' => $savedSettings->count(),
                    'settings' => $savedSettings->toArray()
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Database settings saved successfully'
                ]);
            }

            Log::error('Failed to save database settings', ['company_id' => $company->id]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save database settings'
            ], 500);
        } catch (\Exception $e) {
            Log::error('Error saving database settings: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to save database settings: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Test database connection
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testConnection(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|integer',
                'database_host' => 'required|string',
                'database_port' => 'required|integer',
                'database_name' => 'required|string',
                'database_username' => 'required|string',
                'database_password' => 'required|string'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'errors' => $validator->errors()
                ], 422);
            }

            $company = Company::find($request->company_id);
            if (!$company) {
                return response()->json(['error' => 'Company not found'], 404);
            }

            if (!$company->testDatabaseConnection($request->all())) {
                return response()->json([
                    'error' => 'Failed to connect to the database. Please check your credentials.'
                ], 422);
            }

            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            Log::error('Error testing database connection', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to test database connection'], 500);
        }
    }

    /**
     * Run database migrations
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function runMigrations(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID is required'
                ], 422);
            }

            $company = Company::find($request->company_id);
            if (!$company) {
                Log::error('Company not found', ['company_id' => $request->company_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            Log::info('Starting migrations process', [
                'company_id' => $company->id,
                'company_name' => $company->name
            ]);

            // Get database settings from company settings table
            $settings = \Crater\Models\CompanySetting::where('company_id', $company->id)
                ->whereIn('option', [
                    'database_connection_host',
                    'database_connection_port',
                    'database_connection_name',
                    'database_connection_username',
                    'database_connection_password'
                ])
                ->get();

            Log::info('Retrieved settings from database', [
                'company_id' => $company->id,
                'settings_count' => $settings->count(),
                'settings_found' => $settings->pluck('option')->toArray()
            ]);

            if ($settings->isEmpty()) {
                Log::error('No database settings found', [
                    'company_id' => $company->id,
                    'expected_options' => [
                        'database_connection_host',
                        'database_connection_port',
                        'database_connection_name',
                        'database_connection_username',
                        'database_connection_password'
                    ]
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'No database settings found for this company'
                ], 404);
            }

            $settings = $settings->pluck('value', 'option')->toArray();

            // Verify all required settings are present
            $requiredSettings = [
                'database_connection_host',
                'database_connection_port',
                'database_connection_name',
                'database_connection_username',
                'database_connection_password'
            ];
            
            $missingSettings = array_diff($requiredSettings, array_keys($settings));
            if (!empty($missingSettings)) {
                Log::error('Missing required database settings', [
                    'company_id' => $company->id,
                    'missing_settings' => $missingSettings
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Missing required database settings: ' . implode(', ', $missingSettings)
                ], 400);
            }

            Log::info('Processing database settings', [
                'company_id' => $company->id,
                'settings' => array_merge($settings, ['database_connection_password' => '***'])
            ]);

            $credentials = [
                'database_host' => $settings['database_connection_host'],
                'database_port' => $settings['database_connection_port'],
                'database_name' => $settings['database_connection_name'],
                'database_username' => $settings['database_connection_username'],
                'database_password' => $settings['database_connection_password']
            ];

            // First, create the database if it doesn't exist
            try {
                Log::info('Attempting to create database', [
                    'company_id' => $company->id,
                    'database_name' => $credentials['database_name']
                ]);

                $pdo = new \PDO(
                    "mysql:host={$credentials['database_host']};port={$credentials['database_port']}",
                    $credentials['database_username'],
                    $credentials['database_password']
                );
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$credentials['database_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                Log::info('Database created or already exists', [
                    'company_id' => $company->id,
                    'database_name' => $credentials['database_name']
                ]);
            } catch (\PDOException $e) {
                Log::error('Failed to create database', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                    'code' => $e->getCode()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create database: ' . $e->getMessage()
                ], 500);
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

            Log::info('Database connection configured', [
                'company_id' => $company->id,
                'connection' => 'company'
            ]);

            // Set the connection
            DB::setDefaultConnection('company');

            // Run migrations
            try {
                Log::info('Starting migrations', [
                    'company_id' => $company->id,
                    'path' => 'database/migrations'
                ]);

                // First run the migrations
                Artisan::call('migrate', [
                    '--database' => 'company',
                    '--path' => 'database/migrations',
                    '--force' => true
                ]);

                Log::info('Migrations completed successfully', ['company_id' => $company->id]);

                // Verify migrations were successful by checking if the companies table exists
                $tables = DB::select('SHOW TABLES');
                $tableNames = array_map(function($table) {
                    return reset($table);
                }, $tables);

                Log::info('Tables in database', [
                    'company_id' => $company->id,
                    'tables' => $tableNames
                ]);

                if (!in_array('companies', $tableNames)) {
                    throw new \Exception('Companies table was not created during migration');
                }

                // Run seeders
                Log::info('Starting seeders', ['company_id' => $company->id]);

                // First seed the companies table
                DB::table('companies')->insert([
                    'id' => $company->id,
                    'name' => $company->name,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Then run the rest of the seeders
                Artisan::call('db:seed', [
                    '--database' => 'company',
                    '--class' => 'Database\Seeders\CompanySeeder',
                    '--force' => true
                ]);

                Log::info('Seeders completed successfully', ['company_id' => $company->id]);
            } catch (\Exception $e) {
                Log::error('Failed during migrations or seeding', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed during migrations or seeding: ' . $e->getMessage()
                ], 500);
            }

            // Setup company defaults
            try {
                Log::info('Setting up company defaults', ['company_id' => $company->id]);

                $company->setupDefaultData();
                $company->setupDefaultUnits();
                $company->setupDefaultPaymentMethods();

                Log::info('Company defaults setup completed', ['company_id' => $company->id]);
            } catch (\Exception $e) {
                Log::error('Failed to setup company defaults', [
                    'company_id' => $company->id,
                    'error' => $e->getMessage()
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to setup company defaults: ' . $e->getMessage()
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Migrations completed successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Unexpected error during migrations', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to run migrations: ' . $e->getMessage()
            ], 500);
        }
    }

    public function checkMigrations(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'company_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company ID is required'
                ], 422);
            }

            $company = Company::find($request->company_id);
            if (!$company) {
                Log::error('Company not found', ['company_id' => $request->company_id]);
                return response()->json([
                    'success' => false,
                    'message' => 'Company not found'
                ], 404);
            }

            // Get database settings
            $settings = CompanySetting::where('company_id', $company->id)
                ->whereIn('option', [
                    'database_connection_host',
                    'database_connection_port',
                    'database_connection_name',
                    'database_connection_username',
                    'database_connection_password'
                ])
                ->get();

            if ($settings->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'migrations_completed' => false
                ]);
            }

            $credentials = [];
            foreach ($settings as $setting) {
                $key = str_replace('database_connection_', 'database_', $setting->option);
                $credentials[$key] = $setting->value;
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

            // Check if the companies table exists and has records
            $migrationsCompleted = Schema::hasTable('companies') && DB::table('companies')->exists();

            return response()->json([
                'success' => true,
                'migrations_completed' => $migrationsCompleted
            ]);
        } catch (\Exception $e) {
            Log::error('Error checking migrations status', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error checking migrations status: ' . $e->getMessage()
            ], 500);
        }
    }
} 