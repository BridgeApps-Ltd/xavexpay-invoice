<?php

use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ConvertDatabaseSettingsToRows extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // First, get all companies that have database settings
        $companies = Company::all();
        foreach ($companies as $company) {
            $settings = CompanySetting::where('company_id', $company->id)
                ->where('option', 'database_connection')
                ->first();

            if ($settings) {
                // Convert the column-based settings to row-based settings
                $newSettings = [
                    'database_connection_host' => $settings->database_host,
                    'database_connection_port' => $settings->database_port,
                    'database_connection_name' => $settings->database_name,
                    'database_connection_username' => $settings->database_username,
                    'database_connection_password' => $settings->database_password
                ];

                foreach ($newSettings as $option => $value) {
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

        // Now remove the dedicated columns
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