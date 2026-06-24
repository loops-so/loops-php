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

    public function get(string $id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/email-messages/' . $id);
    }

    public function update(string $id, array $fields = []): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $id, options: [
            'json' => $fields
        ]);
    }

    public function preview(
        string $id,
        array $emails,
        ?array $contact_properties = null,
        ?array $event_properties = null,
        ?array $data_variables = null
    ): mixed {
        $payload = ['emails' => $emails];
        if ($contact_properties !== null) {
            $payload['contactProperties'] = $contact_properties;
        }
        if ($event_properties !== null) {
            $payload['eventProperties'] = $event_properties;
        }
        if ($data_variables !== null) {
            $payload['dataVariables'] = $data_variables;
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $id . '/preview', options: [
            'json' => $payload
        ]);
    }
}
