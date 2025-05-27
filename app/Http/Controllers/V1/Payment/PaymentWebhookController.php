<?php

namespace Crater\Http\Controllers\V1\Payment;

use Crater\Http\Controllers\Controller;
use Crater\Models\Invoice;
use Crater\Models\Payment;
use Crater\Models\PaymentMethod;
use Crater\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Crater\Models\CompanySetting;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;

class PaymentWebhookController extends Controller
{
    /**
     * Handle the incoming payment webhook request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        // Log the entire request for debugging
        Log::info('Payment Webhook Received', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
            'ip' => $request->ip(),
            'timestamp' => now()->toIso8601String()
        ]);

        try {
            // Extract payment information
            $contextId = $request->input('contextId');
            $status = $request->input('status');
            $paymentId = $request->input('payId');
            $uuid = $request->input('uuid');
            $paymentMethod = $request->input('pm');
            $context = $request->input('context', 'Invoice'); // Default to 'Invoice' if not provided

            Log::info('Extracted payment information', [
                'contextId' => $contextId,
                'status' => $status,
                'paymentId' => $paymentId,
                'uuid' => $uuid,
                'paymentMethod' => $paymentMethod,
                'context' => $context
            ]);

            // Find the invoice
            $invoice = Invoice::where('invoice_number', $contextId)->first();
            
            if (!$invoice) {
                Log::error('Invoice not found', ['contextId' => $contextId]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice not found'
                ], 404);
            }

            Log::info('Found invoice', [
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'due_amount' => $invoice->due_amount
            ]);

            // Find or create Card payment method
            $paymentMethodModel = PaymentMethod::firstOrCreate(
                ['name' => 'Card'],
                ['company_id' => $invoice->company_id]
            );

            Log::info('Payment method', [
                'payment_method_id' => $paymentMethodModel->id,
                'payment_method_name' => $paymentMethodModel->name
            ]);

            // Create payment data
            $paymentData = [
                'payment_date' => now()->format(CompanySetting::getSetting('carbon_date_format', $invoice->company_id)),
                'customer_id' => $invoice->customer_id,
                'exchange_rate' => $invoice->exchange_rate,
                'amount' => $invoice->due_amount,
                'payment_number' => 'PAY-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->id,
                'payment_method_id' => $paymentMethodModel->id,
                'notes' => 'Payment received via ' . $paymentMethod,
                'company_id' => $invoice->company_id,
                'currency_id' => $invoice->currency_id,
                'creator_id' => $invoice->creator_id,
                'base_amount' => $invoice->due_amount * $invoice->exchange_rate,
                'status' => $status === 'Y' ? 'COMPLETED' : 'FAILED'
            ];

            Log::info('Payment data prepared', $paymentData);

            // Create payment
            $payment = Payment::create($paymentData);
            $payment->unique_hash = Hashids::connection(Payment::class)->encode($payment->id);
            $payment->save();

            Log::info('Payment created', [
                'payment_id' => $payment->id,
                'payment_number' => $payment->payment_number,
                'status' => $payment->status,
                'unique_hash' => $payment->unique_hash
            ]);

            // Store payment details in custom field
            $paymentDetails = "UUID: {$uuid}\nPayment ID: {$paymentId}\nStatus: {$status}\nPayment Method: {$paymentMethod}\nContext: {$context}\nInvoice Number: {$invoice->invoice_number}";

            Log::info('Payment details for custom field', ['details' => $paymentDetails]);

            // Store in custom field
            $customField = CustomField::where('slug', 'CUSTOM_PAYMENT_PAYMENTDETAILS')
                ->where('model_type', Payment::class)
                ->first();

            if ($customField) {
                Log::info('Found custom field', [
                    'custom_field_id' => $customField->id,
                    'slug' => $customField->slug,
                    'model_type' => $customField->model_type
                ]);

                $payment->customFields()->updateOrCreate(
                    ['custom_field_id' => $customField->id],
                    ['string_answer' => $paymentDetails]
                );

                // Verify the data was stored
                $storedField = $payment->customFields()
                    ->where('custom_field_id', $customField->id)
                    ->first();
                
                Log::info('Stored custom field data', [
                    'payment_id' => $payment->id,
                    'custom_field_id' => $customField->id,
                    'stored_value' => $storedField ? $storedField->string_answer : null
                ]);
            } else {
                Log::warning('Custom field not found', [
                    'field_name' => 'CUSTOM_PAYMENT_PAYMENTDETAILS',
                    'model_type' => Payment::class
                ]);
            }

            // Update invoice if payment was successful
            if ($status === 'Y') {
                $invoice->subtractInvoicePayment($payment->amount);

                Log::info('Invoice updated after successful payment', [
                    'invoice_id' => $invoice->id,
                    'payment_amount' => $payment->amount,
                    'new_due_amount' => $invoice->due_amount
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error processing payment webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Error processing payment'
            ], 500);
        }
    }
} 