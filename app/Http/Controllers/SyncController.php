<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Http;

class SyncController extends Controller
{
    public function getZohoToken()
    {
        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'refresh_token' => env('ZOHO_REFRESH_TOKEN')
        ]);

        $data = $response->json();

        if (!is_array($data) || !array_key_exists('access_token', $data)) {
            throw new \Exception('Failed to get access token: ' . json_encode($data));
        }

        return $data['access_token'];
    }

}
