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

    public function get(string $component_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/components/' . $component_id);
    }
}
