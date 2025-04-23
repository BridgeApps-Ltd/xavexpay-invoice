<?php

namespace Database\Seeders;

use Crater\Models\Company;
use Crater\Models\CompanySetting;
use Crater\Models\Currency;
use Crater\Models\PaymentMethod;
use Crater\Models\Setting;
use Crater\Models\Unit;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Get the company from the request
        $company = Company::find(request()->company_id);
        
        if (!$company) {
            return;
        }

        // Create default currency
        Currency::create([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'precision' => '2',
            'thousand_separator' => ',',
            'decimal_separator' => '.',
            'company_id' => $company->id
        ]);

        // Create default payment methods
        PaymentMethod::create(['name' => 'Cash', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Check', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Credit Card', 'company_id' => $company->id]);
        PaymentMethod::create(['name' => 'Bank Transfer', 'company_id' => $company->id]);

        // Create default units
        Unit::create(['name' => 'box', 'company_id' => $company->id]);
        Unit::create(['name' => 'cm', 'company_id' => $company->id]);
        Unit::create(['name' => 'dz', 'company_id' => $company->id]);
        Unit::create(['name' => 'ft', 'company_id' => $company->id]);
        Unit::create(['name' => 'g', 'company_id' => $company->id]);
        Unit::create(['name' => 'in', 'company_id' => $company->id]);
        Unit::create(['name' => 'kg', 'company_id' => $company->id]);
        Unit::create(['name' => 'km', 'company_id' => $company->id]);
        Unit::create(['name' => 'lb', 'company_id' => $company->id]);
        Unit::create(['name' => 'mg', 'company_id' => $company->id]);
        Unit::create(['name' => 'pc', 'company_id' => $company->id]);

        // Create default company settings
        $settings = [
            'invoice_set_due_date_automatically' => 'YES',
            'invoice_due_date_days' => 7,
            'estimate_set_expiry_date_automatically' => 'YES',
            'estimate_expiry_date_days' => 7,
            'estimate_convert_action' => 'no_action',
            'bulk_exchange_rate_configured' => "NO",
            'invoice_number_format' => "{{SERIES:INV}}{{DELIMITER:-}}{{SEQUENCE:6}}",
            'estimate_number_format' => "{{SERIES:EST}}{{DELIMITER:-}}{{SEQUENCE:6}}",
            'payment_number_format' => "{{SERIES:PAY}}{{DELIMITER:-}}{{SEQUENCE:6}}",
            'language' => 'en',
            'timezone' => 'UTC',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'fiscal_year' => '1-12',
            'carbon_date_format' => 'Y-m-d',
            'moment_date_format' => 'YYYY-MM-DD',
            'notification_email' => 'enabled',
            'notify_invoice_viewed' => 'NO',
            'notify_estimate_viewed' => 'NO',
            'tax_per_item' => 'NO',
            'discount_per_item' => 'NO',
            'invoice_mail_body' => 'Thank you for your business. Please find the invoice attached.',
            'estimate_mail_body' => 'Thank you for your business. Please find the estimate attached.',
            'payment_mail_body' => 'Thank you for your payment. Please find the payment receipt attached.',
            'invoice_company_address_format' => '{{company_name}}<br>{{company_address}}<br>{{company_city}} {{company_state}} {{company_zip}}<br>{{company_country}}<br>{{company_phone}}',
            'estimate_company_address_format' => '{{company_name}}<br>{{company_address}}<br>{{company_city}} {{company_state}} {{company_zip}}<br>{{company_country}}<br>{{company_phone}}',
            'payment_company_address_format' => '{{company_name}}<br>{{company_address}}<br>{{company_city}} {{company_state}} {{company_zip}}<br>{{company_country}}<br>{{company_phone}}',
            'invoice_shipping_address_format' => '{{shipping_address}}<br>{{shipping_city}} {{shipping_state}} {{shipping_zip}}<br>{{shipping_country}}',
            'estimate_shipping_address_format' => '{{shipping_address}}<br>{{shipping_city}} {{shipping_state}} {{shipping_zip}}<br>{{shipping_country}}',
            'payment_shipping_address_format' => '{{shipping_address}}<br>{{shipping_city}} {{shipping_state}} {{shipping_zip}}<br>{{shipping_country}}',
            'invoice_billing_address_format' => '{{billing_address}}<br>{{billing_city}} {{billing_state}} {{billing_zip}}<br>{{billing_country}}',
            'estimate_billing_address_format' => '{{billing_address}}<br>{{billing_city}} {{billing_state}} {{billing_zip}}<br>{{billing_country}}',
            'payment_billing_address_format' => '{{billing_address}}<br>{{billing_city}} {{billing_state}} {{billing_zip}}<br>{{billing_country}}',
        ];

        foreach ($settings as $key => $value) {
            CompanySetting::create([
                'company_id' => $company->id,
                'option' => $key,
                'value' => $value
            ]);
        }
    }
} 