<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class ZohoInventoryCheck extends Command
{
    protected $signature = 'zoho:check-connection';

    protected $description = 'Check connection and authentication with Zoho Inventory API';

    public function handle()
    {
        $this->info("Starting Zoho Inventory connection check...");

        $response = Http::asForm()->post('https://accounts.zoho.eu/oauth/v2/token', [
            'grant_type' => 'refresh_token',
            'client_id' => env('ZOHO_CLIENT_ID'),
            'client_secret' => env('ZOHO_CLIENT_SECRET'),
            'refresh_token' => env('ZOHO_REFRESH_TOKEN'),
        ]);

        $data = $response->json();

        if (!isset($data['access_token'])) {
            $this->error('Failed to get access token: ' . json_encode($data));
            return 1;
        }

        $token = $data['access_token'];

        $this->info('Access token received.');

        $orgResponse = Http::withHeaders([
            'Authorization' => 'Zoho-oauthtoken ' . $token,
        ])->get('https://www.zohoapis.eu/inventory/v1/organizations');

        $orgData = $orgResponse->json();

        if (isset($orgData['organizations'])) {
            $this->info('Successfully fetched organizations:');
            foreach ($orgData['organizations'] as $org) {
                $this->line("- {$org['name']} (ID: {$org['organization_id']})");
            }
            return 0;
        } else {
            $this->error('Failed to fetch organizations: ' . json_encode($orgData));
            return 1;
        }
    }
}
