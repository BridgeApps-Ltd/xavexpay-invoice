<?php

namespace Database\Seeders;

use Crater\Models\CustomField;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CustomFieldsSeeder extends Seeder
{
    protected $companyId;

    public function __construct($companyId = null)
    {
        $this->companyId = $companyId;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!$this->companyId) {
            throw new \Exception('Company ID is required for seeding custom fields');
        }

        $customFields = [
            [
                'name' => 'PaymentLink',
                'label' => 'Payment Link',
                'model_type' => 'Invoice',
                'type' => 'Url',
                'placeholder' => 'Payment Link',
                'is_required' => false,
                'order' => 1
            ],
            [
                'name' => 'PaymentGateway',
                'label' => 'Payment Gateway',
                'model_type' => 'Payment',
                'type' => 'Input',
                'placeholder' => 'Payment Gateway',
                'is_required' => false,
                'order' => 1
            ],
            [
                'name' => 'PaymentDetails',
                'label' => 'Payment Details',
                'model_type' => 'Payment',
                'type' => 'TextArea',
                'placeholder' => 'Payment Details',
                'is_required' => false,
                'order' => 2
            ],
            [
                'name' => 'PaymentTerms',
                'label' => 'Payment Terms',
                'model_type' => 'Invoice',
                'type' => 'TextArea',
                'placeholder' => 'Payment Terms',
                'string_answer' => "Payment Terms: Payment is due within 5 days of the invoice date.\nPlease complete payment via the secure link below.\n(or)\nuse the bank details below:\nBank Name: [Your Bank Name] | Account No: [1234567890] | IFSC: [ABC12345] | \nAccount Name: [Your Company Name].",
                'is_required' => false,
                'order' => 2
            ]
        ];

        foreach ($customFields as $field) {
            CustomField::create([
                'name' => $field['name'],
                'label' => $field['label'],
                'model_type' => $field['model_type'],
                'type' => $field['type'],
                'placeholder' => $field['placeholder'],
                'is_required' => $field['is_required'],
                'order' => $field['order'],
                'slug' => Str::upper('CUSTOM_'.$field['model_type'].'_'.Str::slug($field['name'], '_')),
                'company_id' => $this->companyId
            ]);
        }
    }
} 