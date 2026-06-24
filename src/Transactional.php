<?php

namespace Loops;

use Loops\LoopsClient;

class Transactional
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function send(
        string $transactional_id,
        string $email,
        ?bool $add_to_audience = false,
        ?array $data_variables = [],
        ?array $attachments = [], /** @var array<array{filename: string, contentType: string, data: string}> */
        ?array $headers = []
    ): mixed {
        $payload = [
            'transactionalId' => $transactional_id,
            'email' => $email,
            'addToAudience' => $add_to_audience,
            'dataVariables' => $data_variables,
            'attachments' => $attachments,
        ];

        return $this->client->query(method: 'POST', endpoint: 'v1/transactional', options: [
            'json' => $payload,
            'headers' => $headers
        ]);
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/transactional-emails', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(string $name, ?string $transactional_group_id = null): mixed
    {
        $payload = ['name' => $name];
        if ($transactional_group_id !== null) {
            $payload['transactionalGroupId'] = $transactional_group_id;
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails', options: [
            'json' => $payload
        ]);
    }

    public function get(string $transactional_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/transactional-emails/' . $transactional_id);
    }

    public function update(
        string $transactional_id,
        ?string $name = null,
        ?string $transactional_group_id = null
    ): mixed {
        $payload = [];
        if ($name !== null) {
            $payload['name'] = $name;
        }
        if ($transactional_group_id !== null) {
            $payload['transactionalGroupId'] = $transactional_group_id;
        }
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one field must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails/' . $transactional_id, options: [
            'json' => $payload
        ]);
    }

    public function ensureDraft(string $transactional_id): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails/' . $transactional_id . '/draft');
    }

    public function publish(string $transactional_id): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails/' . $transactional_id . '/publish');
    }
}
