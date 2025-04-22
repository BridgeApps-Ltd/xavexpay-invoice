<?php

namespace Crater\Services;

use Crater\Models\Company;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Spatie\DbDumper\Databases\MySql;

class CompanyDatabaseBackupService
{
    /**
     * Create a backup of a company's database
     *
     * @param Company $company
     * @return bool
     */
    public function createBackup(Company $company)
    {
        try {
            $settings = $company->getDatabaseConnection();
            if (!$settings) {
                Log::error('No database settings found for company: ' . $company->id);
                return false;
            }

            $backupPath = 'backups/company_' . $company->id . '_' . date('Y-m-d_H-i-s') . '.sql';
            
            MySql::create()
                ->setHost($settings['database_host'])
                ->setPort($settings['database_port'])
                ->setDbName($settings['database_name'])
                ->setUserName($settings['database_username'])
                ->setPassword($settings['database_password'])
                ->dumpToFile(storage_path('app/' . $backupPath));

            Log::info('Backup created successfully for company: ' . $company->id, [
                'path' => $backupPath
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to create backup for company: ' . $company->id, [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Restore a company's database from backup
     *
     * @param Company $company
     * @param string $backupPath
     * @return bool
     */
    public function restoreBackup(Company $company, string $backupPath)
    {
        try {
            if (!Storage::exists($backupPath)) {
                Log::error('Backup file not found: ' . $backupPath);
                return false;
            }

            $settings = $company->getDatabaseConnection();
            if (!$settings) {
                Log::error('No database settings found for company: ' . $company->id);
                return false;
            }

            $command = sprintf(
                'mysql -h %s -P %s -u %s -p%s %s < %s',
                $settings['database_host'],
                $settings['database_port'],
                $settings['database_username'],
                $settings['database_password'],
                $settings['database_name'],
                storage_path('app/' . $backupPath)
            );

            exec($command, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Failed to restore backup for company: ' . $company->id, [
                    'output' => $output,
                    'return_var' => $returnVar
                ]);
                return false;
            }

            Log::info('Backup restored successfully for company: ' . $company->id, [
                'path' => $backupPath
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to restore backup for company: ' . $company->id, [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * List all backups for a company
     *
     * @param Company $company
     * @return array
     */
    public function listBackups(Company $company)
    {
        try {
            $backups = Storage::files('backups');
            $companyBackups = array_filter($backups, function ($backup) use ($company) {
                return strpos($backup, 'company_' . $company->id . '_') === 0;
            });

            return array_map(function ($backup) {
                return [
                    'path' => $backup,
                    'size' => Storage::size($backup),
                    'last_modified' => Storage::lastModified($backup)
                ];
            }, $companyBackups);
        } catch (\Exception $e) {
            Log::error('Failed to list backups for company: ' . $company->id, [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }

    /**
     * Delete a backup
     *
     * @param string $backupPath
     * @return bool
     */
    public function deleteBackup(string $backupPath)
    {
        try {
            if (!Storage::exists($backupPath)) {
                Log::error('Backup file not found: ' . $backupPath);
                return false;
            }

            Storage::delete($backupPath);
            Log::info('Backup deleted successfully: ' . $backupPath);
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete backup: ' . $backupPath, [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }
} 