<?php

namespace Loops;

use Loops\LoopsClient;

class Components
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/components', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(string $name, string $lmx): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/components', options: [
            'json' => [
                'name' => $name,
                'lmx' => $lmx,
            ]
        ]);
    }

    public function get(string $component_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/components/' . $component_id);
    }

    public function update(string $component_id, ?string $name = null, ?string $lmx = null): mixed
    {
        $payload = Util::omitNull([
            'name' => $name,
            'lmx' => $lmx,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one of name or lmx must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/components/' . $component_id, options: [
            'json' => $payload
        ]);
    }
}
