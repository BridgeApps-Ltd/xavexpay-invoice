<?php

namespace Crater\Http\Controllers\V1\Admin\Invoice;

use Crater\Http\Controllers\Controller;
use Crater\Http\Requests;
use Crater\Http\Requests\DeleteInvoiceRequest;
use Crater\Http\Resources\InvoiceResource;
use Crater\Jobs\GenerateInvoicePdfJob;
use Crater\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Invoice::class);

        \Log::info('Invoice request received', ['request' => $request->all()]);

        $limit = $request->has('limit') ? $request->limit : 10;
        \Log::info('Using limit', ['limit' => $limit]);

        \Log::info('Building query');
        $query = Invoice::whereCompany()
            ->join('customers', 'customers.id', '=', 'invoices.customer_id')
            ->applyFilters($request->all());
        
        \Log::info('Adding select fields');
        $query->select([
            'invoices.*',
            'customers.name as customer_name'
        ]);
        
        \Log::info('Ordering and paginating');
        $invoices = $query->latest()
            ->paginateData($limit);
            
        \Log::info('Query completed', [
            'count' => $invoices->count(),
            'total' => $invoices->total(),
            'current_page' => $invoices->currentPage(),
            'per_page' => $invoices->perPage()
        ]);
        
        // Log the first invoice if any exist
        if ($invoices->count() > 0) {
            \Log::info('First invoice data', [
                'invoice' => $invoices->first()->toArray()
            ]);
        } else {
            \Log::info('No invoices found');
        }

        $totalCount = Invoice::whereCompany()->count();
        \Log::info('Total invoice count', ['count' => $totalCount]);

        $resource = InvoiceResource::collection($invoices);
        \Log::info('Resource created');

        $response = $resource->additional(['meta' => [
            'invoice_total_count' => $totalCount,
        ]]);
        
        \Log::info('Response prepared, returning data');
        
        return $response;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Requests\InvoicesRequest $request)
    {
        $this->authorize('create', Invoice::class);

        $invoice = Invoice::createInvoice($request);

        if ($request->has('invoiceSend')) {
            $invoice->send($request->subject, $request->body);
        }

        GenerateInvoicePdfJob::dispatch($invoice);

        return new InvoiceResource($invoice);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Crater\Models\Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Invoice $invoice)
    {
        $this->authorize('view', $invoice);

        return new InvoiceResource($invoice);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Requests\InvoicesRequest $request, Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice = $invoice->updateInvoice($request);

        if (is_string($invoice)) {
            return respondJson($invoice, $invoice);
        }

        GenerateInvoicePdfJob::dispatch($invoice, true);

        return new InvoiceResource($invoice);
    }

    /**
     * delete the specified resources in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteInvoiceRequest $request)
    {
        $this->authorize('delete multiple invoices');

        Invoice::deleteInvoices($request->ids);

        return response()->json([
            'success' => true,
        ]);
    }
}
