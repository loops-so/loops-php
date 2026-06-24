<?php

namespace Loops;

use Loops\LoopsClient;

class Themes
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

        return $this->client->query(method: 'GET', endpoint: 'v1/themes', options: [
            'query' => $query
        ]);
    }

    public function get(string $id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/themes/' . $id);
    }
}
