<?php

namespace Lwekuiper\StatamicHubspot\Connectors;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HubSpotConnector
{
    protected $token;

    public function __construct()
    {
        $this->token = config('statamic.hubspot.access_token');
    }

    public function createOrUpdateContact($email, $data): ?array
    {
        $properties = array_merge(['email' => $email], $data);

        $response = $this->client()->post('crm/v3/objects/contacts/batch/upsert', [
            'inputs' => [
                [
                    'id' => $email,
                    'idProperty' => 'email',
                    'properties' => $properties,
                ],
            ],
        ]);

        $result = $this->handleResponse($response, 'Failed to create or update contact', $data);

        return $result && isset($result['results'][0]) ? $result['results'][0] : null;
    }

    public function getContactProperties(): ?array
    {
        $response = $this->client()->get('crm/v3/properties/contacts');

        return $this->handleResponse($response, 'Failed to get contact properties');
    }

    public function getContactProperty($name): ?array
    {
        $response = $this->client()->get("crm/v3/properties/contacts/{$name}");

        return $this->handleResponse($response, 'Failed to get contact property', ['name' => $name]);
    }

    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => "Bearer {$this->token}",
            'Content-Type' => 'application/json',
        ])
            ->acceptJson()
            ->baseUrl('https://api.hubapi.com/');
    }

    private function handleResponse(Response $response, string $errorMessage, array $context = []): ?array
    {
        if (! $response->successful()) {
            Log::error($errorMessage, array_merge([$response->json()], $context));

            return null;
        }

        return $response->json();
    }
}
