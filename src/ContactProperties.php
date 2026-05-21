<?php

namespace Loops;

use Loops\LoopsClient;

class ContactProperties
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function create(string $name, string $type = 'string' | 'number' | 'boolean' | 'date'): mixed
    {
        $payload = [
            'name' => $name,
            'type' => $type
        ];

        return $this->client->query(method: 'POST', endpoint: 'v1/contacts/properties', options: [
            'json' => $payload
        ]);
    }
    public function list(?string $list = null): mixed
    {
        $query = [];
        if ($list) {
            $query['list'] = $list;
        }
        return $this->client->query(method: 'GET', endpoint: 'v1/contacts/properties', options: [
            'query' => $query
        ]);
    }
}