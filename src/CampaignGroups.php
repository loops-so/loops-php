<?php

namespace Loops;

use Loops\LoopsClient;

class CampaignGroups
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        $query = [];
        if ($per_page !== null) {
            $query['perPage'] = $per_page;
        }
        if ($cursor) {
            $query['cursor'] = $cursor;
        }

        return $this->client->query(method: 'GET', endpoint: 'v1/campaign-groups', options: [
            'query' => $query
        ]);
    }

    public function create(string $name, ?string $description = null): mixed
    {
        $payload = ['name' => $name];
        if ($description !== null) {
            $payload['description'] = $description;
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/campaign-groups', options: [
            'json' => $payload
        ]);
    }

    public function get(string $id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaign-groups/' . $id);
    }

    public function update(string $id, ?string $name = null, ?string $description = null): mixed
    {
        $payload = [];
        if ($name !== null) {
            $payload['name'] = $name;
        }
        if ($description !== null) {
            $payload['description'] = $description;
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/campaign-groups/' . $id, options: [
            'json' => $payload
        ]);
    }
}
