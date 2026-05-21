<?php

namespace Loops;

use Loops\LoopsClient;

class EmailMessages
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function get(string $email_message_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/email-messages/' . $email_message_id);
    }

    public function update(string $email_message_id, array $fields = []): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $email_message_id, options: [
            'json' => $fields
        ]);
    }
}
