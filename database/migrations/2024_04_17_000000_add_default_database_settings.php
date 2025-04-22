<?php

use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Database\Migrations\Migration;

class AddDefaultDatabaseSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $companies = Company::all();
        foreach ($companies as $company) {
            $settings = [
                'database_connection_host' => env('DB_HOST', '127.0.0.1'),
                'database_connection_port' => env('DB_PORT', '3306'),
                'database_connection_name' => env('DB_DATABASE', 'forge'),
                'database_connection_username' => env('DB_USERNAME', 'forge'),
                'database_connection_password' => env('DB_PASSWORD', '')
            ];

            foreach ($settings as $option => $value) {
                CompanySetting::updateOrCreate(
                    [
                        'company_id' => $company->id,
                        'option' => $option
                    ],
                    [
                        'value' => $value
                    ]
                );
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Remove the database settings
        CompanySetting::whereIn('option', [
            'database_connection_host',
            'database_connection_port',
            'database_connection_name',
            'database_connection_username',
            'database_connection_password'
        ])->delete();
    }
} 