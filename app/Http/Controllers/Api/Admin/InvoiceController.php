<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{

    public function index()
    {
        $invoices = Invoice::with('customer')->when(request()->q, function ($invoices) {
            $invoices = $invoices->where('invoice', 'like', '%' . request()->q . '%');
        })->latest()->paginate(5);

        return new InvoiceResource(true, 'List of invoices', $invoices);
    }

    public function show($id)
    {
        $invoice = Invoice::with([
            'orders.product',
            'customer',
            'city',
            'province'
        ])->whereId($id)->first();

        if ( !$invoice ) {
            return new InvoiceResource(false, 'Invoice not found', null);
        }

        return new InvoiceResource(true, 'Invoice found', $invoice);
    }

}