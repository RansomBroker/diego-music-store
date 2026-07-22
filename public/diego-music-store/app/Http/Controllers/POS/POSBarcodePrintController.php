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

        return view('pos.barcode-print-sheet', [
            'queue'          => $payload['queue'] ?? [],
            'layout'         => $payload['layout'] ?? '3col',
            'label_width'    => $payload['label_width'] ?? 33,
            'label_height'   => $payload['label_height'] ?? 18,
            'columns'        => $payload['columns'] ?? 3,
            'gap_x'          => $payload['gap_x'] ?? 3,
            'gap_y'          => $payload['gap_y'] ?? 3,
            'font_size'      => $payload['font_size'] ?? 10,
            'barcode_height' => $payload['barcode_height'] ?? 35,
            'show_store'     => $payload['show_store'] ?? true,
            'show_name'      => $payload['show_name'] ?? true,
            'show_price'     => $payload['show_price'] ?? true,
            'show_code'      => $payload['show_code'] ?? true,
        ]);
    }
}
