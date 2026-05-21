<?php

namespace Loops;

use Loops\LoopsClient;

class DedicatedSendingIps
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/dedicated-sending-ips');
    }
}
