<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
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

    public function paymentTerms()
    {
        $token = (new SyncController())->getZohoToken();
        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.eu/inventory/v1/paymentterms', [
            'organization_id' => env('ZOHO_ORG_ID'),
        ]);

        if ($response->successful()) {
            return response()->json($response->json()['payment_terms'] ?? []);
        }

        return response()->json([], 500);
    }

    public function createContact(Request $request)
    {
        $token = (new SyncController())->getZohoToken();

        $data = $request->validate([
            'contact_name' => 'required|string|max:200',
            'company_name' => 'nullable|string|max:200',
            'email' => 'nullable|email',
            'phone' => 'nullable|string',
            'contact_type' => 'in:customer,vendor',
            'billing_address' => 'nullable|array',
            'shipping_address' => 'nullable|array',
            'contact_persons' => 'nullable|array',
        ]);

        $payload = [
            'contact_name' => $data['contact_name'],
            'company_name' => $data['company_name'] ?? null,
            'contact_type' => $data['contact_type'] ?? 'customer',
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
        ];

        if (!empty($data['billing_address'])) {
            $payload['billing_address'] = $data['billing_address'];
        }

        if (!empty($data['shipping_address'])) {
            $payload['shipping_address'] = $data['shipping_address'];
        }

        if (!empty($data['contact_persons'])) {
            $payload['contact_persons'] = $data['contact_persons'];
        }

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://www.zohoapis.eu/inventory/v1/contacts?organization_id=' . env('ZOHO_ORG_ID'), $payload);

        if ($response->successful()) {
            return response()->json($response->json()['contact']);
        }

        return response()->json([
            'message' => 'Failed to create contact',
            'errors' => $response->json(),
            'status' => $response->status(),
        ], $response->status());
    }

    public function createSalesOrder(Request $request)
    {
        $token = (new SyncController())->getZohoToken();

        $data = $request->all();

        if (!isset($data['customer_id'])) {
            return response()->json(['error' => 'Missing customer_id'], 400);
        }

        if (!isset($data['line_items']) || empty($data['line_items'])) {
            return response()->json(['error' => 'At least one line item is required'], 400);
        }

        $payload = [
            'customer_id' => $data['customer_id'],
            'date' => $data['date'] ?? date('Y-m-d'),
            'reference_number' => $data['reference_number'] ?? '',
            'shipment_date' => $data['shipment_date'] ?? null,
            'status' => $data['status'] ?? 'confirmed',
            'notes' => $data['notes'] ?? '',
            'terms' => $data['terms'] ?? '',
            'discount' => $data['discount'] ?? '0%',
            'shipping_charge' => $data['shipping_charge'] ?? 0,
            'adjustment' => $data['adjustment'] ?? 0,
            'is_discount_before_tax' => $data['is_discount_before_tax'] ?? true,
            'discount_type' => $data['discount_type'] ?? 'entity_level',
            'line_items' => array_map(function ($item) {
                return [
                    'item_id' => $item['item_id'],
                    'name' => $item['name'] ?? '',
                    'description' => $item['description'] ?? '',
                    'rate' => $item['rate'],
                    'quantity' => $item['quantity'],
                    'tax_id' => $item['tax_id'] ?? null,
                    'unit' => $item['unit'] ?? 'qty',
                    'item_total' => $item['item_total'] ?? ($item['rate'] * $item['quantity'])
                ];
            }, $data['line_items'])
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://www.zohoapis.eu/inventory/v1/salesorders?organization_id=' . env('ZOHO_ORG_ID'), $payload);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to create sales order',
                'details' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'sales_order' => $response->json()
        ]);
    }
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

}
