<?php

namespace Crater\Console\Commands;

use Crater\Models\Company;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SeedCustomFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'company:seed-custom-fields {company_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed custom fields for a specific company database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $companyId = $this->argument('company_id');
        
        try {
            $company = Company::find($companyId);
            if (!$company) {
                $this->error('Company not found');
                return 1;
            }

            $credentials = $company->getDatabaseConnection();
            if (!$credentials) {
                $this->error('Database settings not found for company');
                return 1;
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

            $this->info('Running custom fields seeder for company: ' . $company->name);
            
            // Run the seeder
            $seeder = new \Database\Seeders\CustomFieldsSeeder($companyId);
            $seeder->run();

            $this->info('Custom fields seeded successfully');
            return 0;
        } catch (\Exception $e) {
            $this->error('Failed to seed custom fields: ' . $e->getMessage());
            Log::error('Failed to seed custom fields', [
                'company_id' => $companyId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
} 