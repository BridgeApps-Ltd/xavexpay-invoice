<?php

namespace Crater\Http\Controllers\V1\Admin\Settings;

use Crater\Http\Controllers\Controller;
use Crater\Models\CompanyPaymentSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PaymentSettingsController extends Controller
{
    public function getSettings(Request $request)
    {
        $settings = CompanyPaymentSetting::getSettings($request->header('company'));

        return response()->json([
            'settings' => $settings
        ]);
    }

    public function saveSettings(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'payment_manager' => 'required|string',
            'payment_domain_url' => 'required|url',
            'payment_tenant_id' => 'required|string',
            'payment_context' => 'required|string',
            'payment_status' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $settings = CompanyPaymentSetting::updateOrCreate(
            ['company_id' => $request->header('company')],
            $request->all()
        );

        return response()->json([
            'settings' => $settings,
            'success' => true
        ]);
    }
} 