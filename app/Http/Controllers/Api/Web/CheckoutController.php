<?php

namespace App\Http\Controllers\Api\Web;

use App\Http\Controllers\Controller;
use App\Http\Resources\CheckoutResource;
use App\Models\Cart;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;

class CheckoutController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api_customer');

        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized  = config('services.midtrans.is_sanitized');
        Config::$is3ds        = config('services.midtrans.is_3ds');
    }

    public function store(Request $request)
    {
        DB::transaction(function () use ($request) {
            $length = 10;
            $random = '';
            for ($i = 0; $i < $length; $i++) {
                $random .= rand(0, 1) ? rand(0, 9) : chr(rand(ord('a'), ord('z')));
            }

            $no_invoice = 'INV-' . Str::upper($random);

            $invoice = Invoice::create([
                'invoice'         => $no_invoice,
                'customer_id'     => auth()->guard('api_customer')->user()->id,
                'courier'         => $request->courier,
                'courier_service' => $request->courier_service,
                'courier_cost'    => $request->courier_cost,
                'weight'          => $request->weight,
                'name'            => $request->name,
                'phone'           => $request->phone,
                'city_id'         => $request->city_id,
                'province_id'     => $request->province_id,
                'address'         => $request->address,
                'grand_total'     => $request->grand_total,
                'status'          => 'pending',
            ]);

            foreach (Cart::where('customer_id', auth()->guard('api_customer')->user()->id)->get()
                     as $cart) {
                $invoice->orders()->create([
                    'invoice_id' => $invoice->id,
                    'product_id' => $cart->product_id,
                    'qty'        => $cart->qty,
                    'price'      => $cart->product->price,
                ]);
            }

            // Delete cart
            Cart::with('product')
                ->where('customer_id', auth()->guard('api_customer')->user()->id)
                ->delete();

            $payload = [
                'transaction_details' => [
                    'order_id'     => $invoice->invoice,
                    'gross_amount' => $invoice->grand_total,
                ],
                'customer_details'    => [
                    'first_name'       => $invoice->name,
                    'email'            => auth()->guard('api_customer')->user()->email,
                    'phone'            => $invoice->phone,
                    'shipping_address' => $invoice->address,
                ]
            ];

            $snapToken = Snap::getSnapToken($payload);

            // update invoice with snap token
            $invoice->snap_token = $snapToken;
            $invoice->save();

            // make response 'snap_token'
            $this->response['snap_token'] = $snapToken;
        });

        return new CheckoutResource(true, 'Checkout berhasil', $this->response);
    }

}