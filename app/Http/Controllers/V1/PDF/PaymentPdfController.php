<?php

namespace Crater\Http\Controllers\V1\PDF;

use Crater\Http\Controllers\Controller;
use Crater\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentPdfController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request, Payment $payment)
    {
        Log::info('Payment PDF requested', [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'is_preview' => $request->has('preview'),
            'request_path' => $request->path(),
            'request_method' => $request->method()
        ]);

        if ($request->has('preview')) {
            return view('app.pdf.payment.payment');
        }

        $response = $payment->getGeneratedPDFOrStream('payment');
        
        Log::info('Payment PDF response', [
            'payment_id' => $payment->id,
            'payment_number' => $payment->payment_number,
            'response_status' => $response->status(),
            'response_headers' => $response->headers->all()
        ]);

        return $response;
    }
}
