<?php

namespace Crater\Traits;

use Crater\Models\CompanySetting;
use Crater\Exceptions\DatabaseNotConfiguredException;

trait ValidatesDatabaseSettings
{
    protected static function bootValidatesDatabaseSettings()
    {
        static::creating(function ($model) {
            self::validateDatabaseSettings($model->company_id);
        });

        static::updating(function ($model) {
            self::validateDatabaseSettings($model->company_id);
        });
    }

    protected static function validateDatabaseSettings($companyId)
    {
        $hasSettings = CompanySetting::where('company_id', $companyId)
            ->whereIn('option', [
                'database_connection_host',
                'database_connection_port',
                'database_connection_name',
                'database_connection_username',
                'database_connection_password'
            ])
            ->count() === 5;

        if (!$hasSettings) {
            throw new DatabaseNotConfiguredException('Database is not configured for this company');
        }
    }
} 