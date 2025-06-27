<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ZohoAuthController extends Controller
{
    public function callback(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return response('Authorization code is missing', 400);
        }

        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'code' => $code,
            'client_id' => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'redirect_uri' => route('zoho.callback'),
            'grant_type' => 'authorization_code',
        ]);

        $data = $response->json();

        Log::info('Zoho token response', $data);

        if (!isset($data['refresh_token'])) {
            return response('Error getting refresh_token: ' . json_encode($data), 400);
        }

        Storage::disk('local')->put('zoho_token.json', json_encode([
            'refresh_token' => $data['refresh_token'],
            'created_at' => now()->toDateTimeString(),
        ]));

        return response('Refresh token saved successfully: ' . $data['refresh_token']);
    }
}
