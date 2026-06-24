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

    public function preview(
        string $email_message_id,
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

        return $this->client->query(method: 'POST', endpoint: 'v1/email-messages/' . $email_message_id . '/preview', options: [
            'json' => $payload
        ]);
    }
}
