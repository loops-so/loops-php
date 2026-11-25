<?php

namespace Loops;

use Loops\LoopsClient;

class Footers
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/footers', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(?string $name = null): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/footers', options: [
            // Cast so an omitted name encodes as JSON object `{}`, not array `[]`.
            'json' => (object) Util::omitNull([
                'name' => $name,
            ])
        ]);
    }

    public function get(string $footer_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/footers/' . $footer_id);
    }

    public function update(string $footer_id, ?string $name = null, ?string $placement = null): mixed
    {
        $payload = Util::omitNull([
            'name' => $name,
            'placement' => $placement,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one of name or placement must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/footers/' . $footer_id, options: [
            'json' => $payload
        ]);
    }

    public function delete(string $footer_id): mixed
    {
        return $this->client->query(method: 'DELETE', endpoint: 'v1/footers/' . $footer_id);
    }

    public function setDefault(string $footer_id, string $existing_emails): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/footers/' . $footer_id . '/set-default', options: [
            'json' => [
                'existingEmails' => $existing_emails,
            ]
        ]);
    }

    public function getVariant(string $footer_id, string $variant_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/footers/' . $footer_id . '/variants/' . $variant_id);
    }

    public function updateVariant(
        string $footer_id,
        string $variant_id,
        string $expected_revision_id,
        string $lmx
    ): mixed {
        return $this->client->query(method: 'POST', endpoint: 'v1/footers/' . $footer_id . '/variants/' . $variant_id, options: [
            'json' => [
                'expectedRevisionId' => $expected_revision_id,
                'lmx' => $lmx,
            ]
        ]);
    }

    public function publishVariant(
        string $footer_id,
        string $variant_id,
        string $expected_revision_id
    ): mixed {
        return $this->client->query(method: 'POST', endpoint: 'v1/footers/' . $footer_id . '/variants/' . $variant_id . '/publish', options: [
            'json' => [
                'expectedRevisionId' => $expected_revision_id,
            ]
        ]);
    }
}
