<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class POSBarcodePrintController extends Controller
{
    public function show(Request $request)
    {
        $payload = null;

        if ($request->filled('token')) {
            $payload = cache()->get($request->query('token'));
        } elseif ($request->filled('data')) {
            $decoded = base64_decode($request->query('data'), true);
            $payload = $decoded ? json_decode($decoded, true) : null;
        }

        if (!$payload || !is_array($payload)) {
            abort(404, 'Data barcode tidak ditemukan atau sesi pencetakan telah kedaluwarsa. Silakan cetak ulang.');
        }

        $params = \App\Helpers\ProductHelper::resolveLayoutParams($payload);

        return view('pos.barcode-print-sheet', array_merge($params, [
            'queue' => $payload['queue'] ?? [],
        ]));
    }
}
