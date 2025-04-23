<?php

namespace Crater\Console\Commands;

use Crater\Models\Company;
use Crater\Services\CompanyDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ManageCompanyDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:database 
                            {action : The action to perform (create|drop|test|migrate)}
                            {company_id? : The ID of the company}
                            {--host= : Database host}
                            {--port= : Database port}
                            {--name= : Database name}
                            {--username= : Database username}
                            {--password= : Database password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Manage company databases';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $action = $this->argument('action');
        $companyId = $this->argument('company_id');
        $databaseService = new CompanyDatabaseService();

        switch ($action) {
            case 'create':
                return $this->handleCreate($companyId, $databaseService);
            case 'drop':
                return $this->handleDrop($companyId, $databaseService);
            case 'test':
                return $this->handleTest($databaseService);
            case 'migrate':
                return $this->handleMigrate($companyId, $databaseService);
            default:
                $this->error('Invalid action. Available actions: create, drop, test, migrate');
                return 1;
        }
    }

    /**
     * Handle database creation
     *
     * @param int $companyId
     * @param CompanyDatabaseService $databaseService
     * @return int
     */
    private function handleCreate($companyId, $databaseService)
    {
        if (!$companyId) {
            $this->error('Company ID is required for create action');
            return 1;
        }

        $company = Company::find($companyId);
        if (!$company) {
            $this->error('Company not found');
            return 1;
        }

        $credentials = [
            'database_host' => $this->option('host') ?? config('database.connections.mysql.host'),
            'database_port' => $this->option('port') ?? config('database.connections.mysql.port'),
            'database_name' => $this->option('name') ?? 'company_' . $company->id,
            'database_username' => $this->option('username') ?? config('database.connections.mysql.username'),
            'database_password' => $this->option('password') ?? config('database.connections.mysql.password'),
        ];

        $this->info('Creating database for company: ' . $company->name);
        
        if ($databaseService->createDatabase($company, $credentials)) {
            $this->info('Database created successfully');
            return 0;
        }

        $this->error('Failed to create database');
        return 1;
    }

    /**
     * Handle database drop
     *
     * @param int $companyId
     * @param CompanyDatabaseService $databaseService
     * @return int
     */
    private function handleDrop($companyId, $databaseService)
    {
        if (!$companyId) {
            $this->error('Company ID is required for drop action');
            return 1;
        }

        $company = Company::find($companyId);
        if (!$company) {
            $this->error('Company not found');
            return 1;
        }

        $this->info('Dropping database for company: ' . $company->name);
        
        if ($databaseService->dropDatabase($company)) {
            $this->info('Database dropped successfully');
            return 0;
        }

        $this->error('Failed to drop database');
        return 1;
    }

    /**
     * Handle database connection test
     *
     * @param CompanyDatabaseService $databaseService
     * @return int
     */
    private function handleTest($databaseService)
    {
        $credentials = [
            'database_host' => $this->option('host') ?? config('database.connections.mysql.host'),
            'database_port' => $this->option('port') ?? config('database.connections.mysql.port'),
            'database_name' => $this->option('name') ?? 'test_db',
            'database_username' => $this->option('username') ?? config('database.connections.mysql.username'),
            'database_password' => $this->option('password') ?? config('database.connections.mysql.password'),
        ];

        $this->info('Testing database connection...');
        
        if ($databaseService->testConnection($credentials)) {
            $this->info('Connection successful');
            return 0;
        }

        $this->error('Connection failed');
        return 1;
    }

    /**
     * Handle database migration
     *
     * @param int $companyId
     * @param CompanyDatabaseService $databaseService
     * @return int
     */
    private function handleMigrate($companyId, $databaseService)
    {
        if (!$companyId) {
            $this->error('Company ID is required for migrate action');
            return 1;
        }

        $company = Company::find($companyId);
        if (!$company) {
            $this->error('Company not found');
            return 1;
        }

        $settings = $company->getDatabaseConnection();
        if (!$settings) {
            $this->error('No database settings found for company');
            return 1;
        }

        $this->info('Running migrations for company: ' . $company->name);
        
        try {
            $databaseService->configureConnection($settings);
            $databaseService->runMigrations();
            $this->info('Migrations completed successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Migration failed: ' . $e->getMessage());
            return 1;
        }
    }
} 