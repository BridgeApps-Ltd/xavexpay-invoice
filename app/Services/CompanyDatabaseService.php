<?php

namespace Crater\Services;

use Crater\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use PDO;
use PDOException;

class CompanyDatabaseService
{
    /**
     * Create a new database for the company
     *
     * @param Company $company
     * @param array $credentials
     * @return bool
     */
    public function createDatabase(Company $company, array $credentials): bool
    {
        try {
            // 1. Create new database
            $this->createDatabaseIfNotExists($credentials);

            // 2. Configure connection
            $this->configureConnection($credentials);

            // 3. Run migrations
            $this->runMigrations();

            // 4. Seed initial data
            $this->seedInitialData($company);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create database for company ' . $company->id . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Create database if it doesn't exist
     *
     * @param array $credentials
     * @return void
     */
    private function createDatabaseIfNotExists(array $credentials): void
    {
        try {
            // Create a connection without database name
            $pdo = new PDO(
                "mysql:host={$credentials['database_host']};port={$credentials['database_port']}",
                $credentials['database_username'],
                $credentials['database_password']
            );

            // Create database
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$credentials['database_name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            Log::info('Database created successfully', [
                'database' => $credentials['database_name']
            ]);
        } catch (PDOException $e) {
            Log::error('Failed to create database: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Configure database connection
     *
     * @param array $credentials
     * @return void
     */
    private function configureConnection(array $credentials): void
    {
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

        // Test the connection
        DB::connection('company')->getPdo();
        
        Log::info('Database connection configured successfully', [
            'database' => $credentials['database_name']
        ]);
    }

    /**
     * Run migrations for the company database
     *
     * @return void
     */
    private function runMigrations(): void
    {
        try {
            // Set the connection
            DB::setDefaultConnection('company');

            // Run migrations
            Artisan::call('migrate', [
                '--database' => 'company',
                '--path' => 'database/migrations/company',
                '--force' => true
            ]);

            Log::info('Migrations run successfully');
        } catch (\Exception $e) {
            Log::error('Failed to run migrations: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Seed initial data for the company
     *
     * @param Company $company
     * @return void
     */
    private function seedInitialData(Company $company): void
    {
        try {
            // Set the connection
            DB::setDefaultConnection('company');

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

            Log::info('Initial data seeded successfully', [
                'company_id' => $company->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to seed initial data: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Drop company database
     *
     * @param Company $company
     * @return bool
     */
    public function dropDatabase(Company $company): bool
    {
        try {
            $settings = $company->getDatabaseConnection();
            
            if (!$settings) {
                Log::warning('No database settings found for company', [
                    'company_id' => $company->id
                ]);
                return false;
            }

            // Create a connection without database name
            $pdo = new PDO(
                "mysql:host={$settings['database_host']};port={$settings['database_port']}",
                $settings['database_username'],
                $settings['database_password']
            );

            // Drop database
            $pdo->exec("DROP DATABASE IF EXISTS `{$settings['database_name']}`");

            Log::info('Database dropped successfully', [
                'company_id' => $company->id,
                'database' => $settings['database_name']
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to drop database: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Test database connection
     *
     * @param array $credentials
     * @return bool
     */
    public function testConnection(array $credentials): bool
    {
        try {
            // Create a connection without database name
            $pdo = new PDO(
                "mysql:host={$credentials['database_host']};port={$credentials['database_port']}",
                $credentials['database_username'],
                $credentials['database_password']
            );

            // Test connection
            $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);

            return true;
        } catch (PDOException $e) {
            Log::error('Database connection test failed: ' . $e->getMessage());
            return false;
        }
    }
} 