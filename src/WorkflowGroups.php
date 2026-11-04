<?php

namespace Loops;

use Loops\LoopsClient;

class WorkflowGroups
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflow-groups', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(string $name, ?string $description = null): mixed
    {

        return $this->client->query(method: 'POST', endpoint: 'v1/workflow-groups', options: [
            'json' => Util::omitNull([
                'name' => $name,
                'description' => $description,
            ])
        ]);
    }

    public function get(string $workflow_group_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflow-groups/' . $workflow_group_id);
    }

    public function update(string $workflow_group_id, ?string $name = null, ?string $description = null): mixed
    {
        $payload = Util::omitNull([
            'name' => $name,
            'description' => $description,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one field must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/workflow-groups/' . $workflow_group_id, options: [
            'json' => $payload
        ]);
    }
}
