<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class POSBarcodePrintController extends Controller
{
    public function show(Request $request)
    {
        $payload = json_decode(base64_decode($request->query('data')), true);
        if (!$payload) {
            abort(400, 'Data barcode tidak valid.');
        }

        $params = \App\Helpers\ProductHelper::resolveLayoutParams($payload);

        return view('pos.barcode-print-sheet', array_merge($params, [
            'queue' => $payload['queue'] ?? [],
        ]));
    }
}
