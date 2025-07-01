<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SyncController extends Controller
{
    public function contacts()
    {
        $token = (new SyncController())->getZohoToken();

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.eu/inventory/v1/contacts', [
            'organization_id' => config('ZOHO_ORG_ID'),
        ]);

        return response()->json($response->json()['contacts'] ?? []);
    }

    public function items(Request $request)
    {
        $token = (new SyncController())->getZohoToken();
        $organizationId = env('ZOHO_ORG_ID');

        $url = 'https://www.zohoapis.eu/inventory/v1/items?organization_id=' . $organizationId;


        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token
        ])->get('https://www.zohoapis.eu/inventory/v1/items', [
            'organization_id' => env('ZOHO_ORG_ID')
        ]);

        if ($response->successful()) {
            $data = $response->json();

            $items = $data['items'] ?? [];

            $formattedItems = array_map(function ($item) {
                return [
                    'item_id' => $item['item_id'] ?? null,
                    'name' => $item['name'] ?? '',
                    'rate' => $item['rate'] ?? 0,
                    'purchase_account_id' => $item['purchase_account_id'] ?? null,
                    'item_type' => $item['item_type'] ?? null,
                    'description' => $item['description'] ?? '',
                    'available_quantity' => $item['locations'][0]['location_actual_available_stock'] ?? null,
                    'vendor_id' => $item['vendor_id'] ?? null,
                    'vendor_name' => $item['vendor_name'] ?? '',
                    'tax_id' => $item['tax_id'] ?? null,
                    'tax_name' => $item['tax_name'] ?? '',
                    'tax_percentage' => $item['tax_percentage'] ?? 0,
                    'sku' => $item['sku'] ?? '',
                    'unit' => $item['unit'] ?? '',
                ];
            }, $items);

            return response()->json($formattedItems);
        } else {
            \Log::error('Zoho Inventory API error', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers(),
            ]);

            return response()->json([
                'error' => 'Failed to fetch items from Zoho Inventory',
                'status' => $response->status(),
                'response_body' => $response->body(),
            ], 500);
        }
    }

    public function taxes()
    {
        $token = (new SyncController())->getZohoToken();
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.eu/inventory/v1/taxes', [
            'organization_id' => env('ZOHO_ORG_ID'),
        ]);

        if ($response->successful()) {
            return response()->json($response->json()['taxes'] ?? []);
        }

        return response()->json([], 500);
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
