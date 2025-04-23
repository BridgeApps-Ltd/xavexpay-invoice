<?php

use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDatabaseConnectionFieldsToCompanySettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Remove the dedicated columns if they exist
        Schema::table('company_settings', function (Blueprint $table) {
            if (Schema::hasColumn('company_settings', 'database_host')) {
                $table->dropColumn('database_host');
            }
            if (Schema::hasColumn('company_settings', 'database_port')) {
                $table->dropColumn('database_port');
            }
            if (Schema::hasColumn('company_settings', 'database_name')) {
                $table->dropColumn('database_name');
            }
            if (Schema::hasColumn('company_settings', 'database_username')) {
                $table->dropColumn('database_username');
            }
            if (Schema::hasColumn('company_settings', 'database_password')) {
                $table->dropColumn('database_password');
            }
        });

        // Add database connection settings as separate rows for each company
        $companies = Company::all();
        foreach ($companies as $company) {
            $settings = [
                'database_connection_host' => env('DB_HOST', '127.0.0.1'),
                'database_connection_port' => env('DB_PORT', '3306'),
                'database_connection_name' => env('DB_DATABASE', 'DB_NAME'),
                'database_connection_username' => env('DB_USERNAME', 'DB_NAME'),
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
        // Add back the dedicated columns
        Schema::table('company_settings', function (Blueprint $table) {
            $table->string('database_host')->nullable()->after('value');
            $table->integer('database_port')->nullable()->after('database_host');
            $table->string('database_name')->nullable()->after('database_port');
            $table->string('database_username')->nullable()->after('database_name');
            $table->string('database_password')->nullable()->after('database_username');
        });

        // Move the settings back to dedicated columns
        $companies = Company::all();
        foreach ($companies as $company) {
            $settings = CompanySetting::where('company_id', $company->id)
                ->whereIn('option', [
                    'database_connection_host',
                    'database_connection_port',
                    'database_connection_name',
                    'database_connection_username',
                    'database_connection_password'
                ])
                ->get();

            if ($settings->isNotEmpty()) {
                $companySetting = CompanySetting::where('company_id', $company->id)
                    ->where('option', 'database_connection')
                    ->first();

                if (!$companySetting) {
                    $companySetting = new CompanySetting();
                    $companySetting->company_id = $company->id;
                    $companySetting->option = 'database_connection';
                    $companySetting->value = 'enabled';
                }

                foreach ($settings as $setting) {
                    $column = str_replace('database_connection_', 'database_', $setting->option);
                    $companySetting->$column = $setting->value;
                }

                $companySetting->save();
            }
        }

        // Remove the separate setting rows
        CompanySetting::whereIn('option', [
            'database_connection_host',
            'database_connection_port',
            'database_connection_name',
            'database_connection_username',
            'database_connection_password'
        ])->delete();
    }
} 