<?php

namespace Loops;

use Loops\LoopsClient;

class Uploads
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function create(string $content_type, int $content_length): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/uploads', options: [
            'json' => [
                'contentType' => $content_type,
                'contentLength' => $content_length,
            ]
        ]);
    }

    public function complete(string $id): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/uploads/' . $id . '/complete');
    }
}
