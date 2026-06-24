<?php

namespace Loops;

use Loops\LoopsClient;

class Workflows
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

        return $this->client->query(method: 'GET', endpoint: 'v1/workflows', options: [
            'query' => $query
        ]);
    }

    public function get(string $workflow_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflows/' . $workflow_id);
    }

    public function getNode(string $workflow_id, string $node_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id);
    }
}
