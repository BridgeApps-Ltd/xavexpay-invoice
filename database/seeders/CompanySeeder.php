<?php

namespace Database\Seeders;

use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Crater\Models\Currency;
use Crater\Models\PaymentMethod;
use Crater\Models\Setting;
use Crater\Models\Unit;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the company from the request or use the first company
        $company = Company::find(request()->company_id) ?? Company::first();
        
        if (!$company) {
            Log::error('No company found for seeding');
            return;
        }

        Log::info('Seeding company data', ['company_id' => $company->id]);

        // Create default currency if it doesn't exist
        $defaultCurrency = Currency::where('company_id', $company->id)
            ->where('code', 'USD')
            ->first();  

        if (!$defaultCurrency) {
            Log::info('Creating default currency for company', ['company_id' => $company->id]);
            Currency::create([
                'name' => 'US Dollar',
                'code' => 'USD',
                'symbol' => '$',
                'precision' => '2',
                'thousand_separator' => ',',
                'decimal_separator' => '.',
                'company_id' => $company->id
            ]);
        }

        // Set default currency in company settings
        CompanySetting::setSettings(['currency' => 'USD'], $company->id);

        // Create default payment methods if they don't exist
        $paymentMethods = [
            'Cash',
            'Check',
            'Credit Card',
            'Bank Transfer'
        ];

        foreach ($paymentMethods as $method) {
            if (!PaymentMethod::where('name', $method)->where('company_id', $company->id)->exists()) {
                PaymentMethod::create(['name' => $method, 'company_id' => $company->id]);
            }
        }

        // Create default units if they don't exist
        $units = [
            'box', 'cm', 'dz', 'ft', 'g', 'in', 'kg', 'km', 'lb', 'mg',
            'm', 'pcs', 'set', 'sq ft', 'sq m', 't', 'yd', 'ml', 'l'
        ];

        foreach ($units as $unit) {
            if (!Unit::where('name', $unit)->where('company_id', $company->id)->exists()) {
                Unit::create(['name' => $unit, 'company_id' => $company->id]);
            }
        }

        Log::info('Company seeding completed', ['company_id' => $company->id]);
    }
} 