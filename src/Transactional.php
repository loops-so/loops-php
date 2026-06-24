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
        ?array $attachments = [], /** @var array<array{filename: string, content_type: string, data: string}> */
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
        $query = [];
        if ($per_page !== null) {
            $query['perPage'] = $per_page;
        }
        if ($cursor) {
            $query['cursor'] = $cursor;
        }

        return $this->client->query(method: 'GET', endpoint: 'v1/transactional-emails', options: [
            'query' => $query
        ]);
    }

    public function create(string $name): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails', options: [
            'json' => ['name' => $name]
        ]);
    }

    public function get(string $transactional_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/transactional-emails/' . $transactional_id);
    }

    public function update(string $transactional_id, string $name): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/transactional-emails/' . $transactional_id, options: [
            'json' => ['name' => $name]
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