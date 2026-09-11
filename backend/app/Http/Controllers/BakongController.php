<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use KHQR\BakongKHQR;
use KHQR\Helpers\KHQRData;
use KHQR\Models\IndividualInfo;

class BakongController extends Controller
{
    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = (float) $validated['amount'];

        $expiration = (int) floor(microtime(true) * 1000) + (120 * 1000);

        $merchant = new IndividualInfo(
            bakongAccountID: env('BAKONG_ACCOUNT_ID', 'ny_menghong@bkrt'),
            merchantName: env('BAKONG_MERCHANT_NAME', 'MENGHONG NY'),
            merchantCity: env('BAKONG_MERCHANT_CITY', 'Phnom Penh'),
            currency: KHQRData::CURRENCY_USD,
            amount: $amount,
            expirationTimestamp: (string) $expiration,
        );

        $qrResponse = BakongKHQR::generateIndividual($merchant);

        return response()->json([
            'success' => true,
            'qr' => $qrResponse->data['qr'] ?? null,
            'md5' => $qrResponse->data['md5'] ?? null,
            'amount' => $amount,
            'expires_in' => 120,
        ]);
    }

    public function verify(Request $request)
    {
        $validated = $request->validate([
            'md5' => 'required|string',
        ]);

        try {
            $token = env('BAKONG_TOKEN');
            $bakong = new BakongKHQR($token);
            $result = $bakong->checkTransactionByMD5($validated['md5']);

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}