<?php

namespace Loops;

use Loops\LoopsClient;

class Themes
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/themes', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(string $name, ?array $styles = null): mixed
    {
        return $this->client->query(method: 'POST', endpoint: 'v1/themes', options: [
            'json' => Util::omitNull([
                'name' => $name,
                'styles' => $styles,
            ])
        ]);
    }

    public function get(string $theme_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/themes/' . $theme_id);
    }

    public function update(string $theme_id, ?string $name = null, ?array $styles = null): mixed
    {
        $payload = Util::omitNull([
            'name' => $name,
            'styles' => $styles,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one of name or styles must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/themes/' . $theme_id, options: [
            'json' => $payload
        ]);
    }
}
