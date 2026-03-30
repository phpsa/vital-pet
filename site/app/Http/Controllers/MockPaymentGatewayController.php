<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MockPaymentGatewayController
{
    public function __invoke(Request $request): View|RedirectResponse
    {
        if ($request->input('action') === 'cancel') {
            $returnUrl = (string) $request->input('return_url', '');

            abort_if($returnUrl === '', 422, 'Missing return_url');

            $separator = str_contains($returnUrl, '?') ? '&' : '?';

            return redirect()->away($returnUrl.$separator.http_build_query([
                'gateway_status' => 'cancelled',
                'request_id' => $request->input('request_id'),
            ]));
        }

        return view('landing.mock-gateway', [
            'postedData' => $request->all(),
        ]);
    }
}