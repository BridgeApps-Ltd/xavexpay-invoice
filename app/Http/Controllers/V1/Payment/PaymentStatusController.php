<?php

namespace Crater\Http\Controllers\V1\Payment;

use Crater\Http\Controllers\Controller;
use Crater\Models\Invoice;
use Crater\Models\Payment;
use Crater\Models\PaymentMethod;
use Crater\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Crater\Models\CompanySetting;
use Crater\Services\SerialNumberFormatter;
use Illuminate\Support\Facades\Hash;
use Vinkla\Hashids\Facades\Hashids;

class PaymentStatusController extends Controller
{
    /**
     * Create a payment with sequence numbers and custom fields
     *
     * @param array $paymentData
     * @param \Crater\Models\Invoice $invoice
     * @param \Illuminate\Http\Request $request
     * @return \Crater\Models\Payment
     */
    private function createPaymentWithSequence(array $paymentData, Invoice $invoice, Request $request)
    {
        // Create payment
        $payment = Payment::create($paymentData);
        $payment->unique_hash = Hashids::connection(Payment::class)->encode($payment->id);

        // Generate sequence numbers
        $serial = (new SerialNumberFormatter())
            ->setModel($payment)
            ->setCompany($payment->company_id)
            ->setCustomer($payment->customer_id)
            ->setNextNumbers();

        $payment->sequence_number = $serial->nextSequenceNumber;
        $payment->customer_sequence_number = $serial->nextCustomerSequenceNumber;
        $payment->save();

        // Subtract payment amount from invoice
        $invoice->subtractInvoicePayment($payment->amount);

        // Store payment details in custom field
        $this->storePaymentDetails($payment, $invoice, $request);

        return $payment;
    }

    /**
     * Store payment details in custom field
     *
     * @param \Crater\Models\Payment $payment
     * @param \Crater\Models\Invoice $invoice
     * @param \Illuminate\Http\Request $request
     * @throws \Exception
     */
    private function storePaymentDetails(Payment $payment, Invoice $invoice, Request $request)
    {
        // Find the PaymentDetails custom field
        $paymentDetailsField = CustomField::where('name', 'PaymentDetails')
            ->where('model_type', 'Payment')
            ->where('company_id', $invoice->company_id)
            ->first();

        Log::info('==>> PaymentDetails Field Search:', [
            'search_criteria' => [
                'name' => 'PaymentDetails',
                'model_type' => 'Payment',
                'company_id' => $invoice->company_id
            ],
            'found' => $paymentDetailsField ? true : false,
            'field_details' => $paymentDetailsField ? [
                'id' => $paymentDetailsField->id,
                'name' => $paymentDetailsField->name,
                'slug' => $paymentDetailsField->slug,
                'type' => $paymentDetailsField->type,
                'model_type' => $paymentDetailsField->model_type,
                'company_id' => $paymentDetailsField->company_id
            ] : null
        ]);

        if (!$paymentDetailsField) {
            throw new \Exception('PaymentDetails custom field not found');
        }

        // Format payment details
        $paymentDetails = "UUID: " . $request->input('uuid') . "\n" .
                         "Payment ID: " . $request->input('payId') . "\n" .
                         "Status: " . $request->input('status') . "\n" .
                         "Payment Method: " . $request->input('pm') . "\n" .
                         "Context: " . $request->input('context') . "\n" .
                         "Invoice Number: " . $request->input('contextId');

        // Store in custom field
        $payment->updateCustomFields([[
            'id' => $paymentDetailsField->id,
            'value' => $paymentDetails
        ]]);
    }

    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        // Log the entire request
        Log::info('==>> Payment Status Update:', [
            'headers' => $request->headers->all(),
            'query_params' => $request->query(),
            'request_data' => $request->all(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        // Get invoice number from contextId
        $invoiceNumber = $request->input('contextId');

        // Find the invoice
        $invoice = Invoice::where('invoice_number', $invoiceNumber)->first();

        if (!$invoice) {
            Log::error('Invoice not found:', ['invoice_number' => $invoiceNumber]);
            return response()->json([
                'success' => false,
                'message' => 'Invoice not found'
            ], 404);
        }

        // If payment is successful (status = Y)
        if ($request->input('status') === 'Y') {
            try {
                // Find or create Card payment method
                $paymentMethod = PaymentMethod::firstOrCreate(
                    ['name' => 'Card'],
                    ['company_id' => $invoice->company_id]
                );

                // Create payment data
                $paymentData = [
                    'payment_date' => now()->format(CompanySetting::getSetting('carbon_date_format', $invoice->company_id)),
                    'customer_id' => $invoice->customer_id,
                    'exchange_rate' => $invoice->exchange_rate,
                    'amount' => $invoice->due_amount,
                    'payment_number' => 'PAY-' . str_pad($invoice->id, 6, '0', STR_PAD_LEFT),
                    'invoice_id' => $invoice->id,
                    'payment_method_id' => $paymentMethod->id,
                    'notes' => 'Payment received via Card',
                    'company_id' => $invoice->company_id,
                    'currency_id' => $invoice->currency_id,
                    'creator_id' => $invoice->creator_id,
                    'base_amount' => $invoice->due_amount * $invoice->exchange_rate
                ];

                // Create payment with sequence numbers and custom fields
                $payment = $this->createPaymentWithSequence($paymentData, $invoice, $request);

                Log::info('Payment created successfully:', [
                    'payment_id' => $payment->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoiceNumber
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Payment processed successfully'
                ]);

            } catch (\Exception $e) {
                Log::error('Error processing payment:', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Error processing payment'
                ], 500);
            }
        }

        // Return success response for non-successful payments
        return response()->json([
            'success' => true,
            'message' => 'Payment status received'
        ]);
    }

    /**
     * Store a newly created payment in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Crater\Models\Payment
     * @throws \Exception
     */
    public function store(Request $request)
    {
        try {
            return Payment::createPayment($request);
        } catch (\Exception $e) {
            Log::error('Error in store method:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            throw $e; // Re-throw to be caught by the main try-catch block
        }
    }
} 