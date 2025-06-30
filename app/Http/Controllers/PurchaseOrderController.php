<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class PurchaseOrderController extends Controller
{
    public function getVendors()
    {
        $token = (new SyncController())->getZohoToken();

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
            'Content-Type' => 'application/json',
        ])->get('https://www.zohoapis.eu/inventory/v1/vendors?organization_id=' . env('ZOHO_ORG_ID'));

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to fetch vendors',
                'details' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'vendors' => $response->json()['contacts'] ?? [],
        ]);
    }

    public function createPurchaseOrder(Request $request)
    {
        $token = (new SyncController())->getZohoToken();

        $data = $request->all();

        if (!isset($data['vendor_id'])) {
            return response()->json(['error' => 'Missing vendor_id'], 400);
        }

        if (!isset($data['line_items']) || empty($data['line_items'])) {
            return response()->json(['error' => 'At least one line item is required'], 400);
        }

        $payload = [
            'vendor_id' => $data['vendor_id'],
            'date' => $data['date'] ?? date('Y-m-d'),
            'reference_number' => $data['reference_number'] ?? '',
            'shipment_date' => $data['shipment_date'] ?? null,
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
            }, $data['line_items']),
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
            'Content-Type' => 'application/json',
        ])->post('https://www.zohoapis.eu/inventory/v1/purchaseorders?organization_id=' . env('ZOHO_ORG_ID'), $payload);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to create purchase order',
                'details' => $response->json(),
            ], $response->status());
        }

        return response()->json([
            'success' => true,
            'purchase_order' => $response->json()
        ]);
    }
}
