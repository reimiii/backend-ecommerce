<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;

class DashboardController extends Controller
{

    public function index()
    {
        $pending = Invoice::where('status', 'pending')
            ->where('customer_id', auth()->guard('api_customer')->user()->id)->count();

        $success = Invoice::where('status', 'success')
            ->where('customer_id', auth()->guard('api_customer')->user()->id)->count();

        $expired = Invoice::where('status', 'expired')
            ->where('customer_id', auth()->guard('api_customer')->user()->id)->count();

        $failed = Invoice::where('status', 'failed')
            ->where('customer_id', auth()->guard('api_customer')->user()->id)->count();

        return response()->json([
            'success' => true,
            'message' => 'Statistics of your account',
            'data'    => [
                'count' => [
                    'pending' => $pending,
                    'success' => $success,
                    'expired' => $expired,
                    'failed'  => $failed,
                ],
            ]
        ], 200);
    }

}
