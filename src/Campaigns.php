<?php

namespace Loops;

use Loops\LoopsClient;

class Campaigns
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

        return $this->client->query(method: 'GET', endpoint: 'v1/campaigns', options: [
            'query' => $query
        ]);
    }

    public function create(string $name): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/campaigns', options: [
            'json' => ['name' => $name]
        ]);
    }

    public function get(string $campaign_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaigns/' . $campaign_id);
    }

    public function update(string $campaign_id, string $name): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/campaigns/' . $campaign_id, options: [
            'json' => ['name' => $name]
        ]);
    }
}
