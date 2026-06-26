<?php

namespace Loops;

use Loops\LoopsClient;
class Events
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function send(
        string $event_name,
        ?string $email = null,
        ?string $user_id = null,
        ?array $contact_properties = [],
        ?array $event_properties = [],
        ?array $mailing_lists = [],
        ?array $headers = []
    ): mixed {
        if (!$email && !$user_id) {
            throw new \InvalidArgumentException(message: 'You must provide an email or user_id value.');
        }

        $payload = array_merge(
            [
                'eventName' => $event_name,
                'eventProperties' => $event_properties,
                'mailingLists' => $mailing_lists,
            ],
            Util::omitNull([
                'email' => $email,
                'userId' => $user_id,
            ]),
            $contact_properties
        );

        return $this->client->query(method: 'POST', endpoint: 'v1/events/send', options: [
            'json' => $payload,
            'headers' => $headers
        ]);
    }
}
