<?php

namespace Loops;

use Loops\LoopsClient;

class CampaignGroups
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaign-groups', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(string $name, ?string $description = null): mixed
    {

        return $this->client->query(method: 'POST', endpoint: 'v1/campaign-groups', options: [
            'json' => Util::omitNull([
                'name' => $name,
                'description' => $description,
            ])
        ]);
    }

    public function get(string $campaign_group_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaign-groups/' . $campaign_group_id);
    }

    public function update(string $campaign_group_id, ?string $name = null, ?string $description = null): mixed
    {
        $payload = Util::omitNull([
            'name' => $name,
            'description' => $description,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one field must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/campaign-groups/' . $campaign_group_id, options: [
            'json' => $payload
        ]);
    }
}
