<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class SyncController extends Controller
{
    public function getPurchaseAccounts()
    {
        $token = (new SyncController())->getZohoToken();
        $organizationId = env('ZOHO_ORG_ID');

        $url = "https://www.zohoapis.eu/inventory/v1/chartofaccounts?organization_id={$organizationId}";

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token
        ])->get($url);

        if ($response->successful()) {
            $accounts = collect($response->json()['chartofaccounts'] ?? [])
                ->filter(function ($acc) {
                    return $acc['is_active'] && strtolower($acc['account_type']) === 'expense';
                })->values();

            return response()->json($accounts);
        }

        return response()->json([
            'error' => 'Failed to fetch accounts',
            'body' => $response->body(),
        ], 500);
    }

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
