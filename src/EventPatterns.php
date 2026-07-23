<?php

namespace Loops;

use Loops\LoopsClient;

class EventPatterns
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/event-patterns', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function get(string $event_pattern_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/event-patterns/' . $event_pattern_id);
    }

    public function getByName(string $event_name): mixed
    {
        return $this->client->query(
            method: 'GET',
            endpoint: 'v1/event-patterns/by-name/' . rawurlencode($event_name)
        );
    }
}
