<?php

namespace Loops;

use Loops\LoopsClient;

class Workflows
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflows', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(
        string $name,
        ?string $description = null,
        ?string $mailing_list_id = null
    ): mixed {
        return $this->client->query(method: 'POST', endpoint: 'v1/workflows', options: [
            'json' => array_merge(['name' => $name], Util::omitNull([
                'description' => $description,
                'mailingListId' => $mailing_list_id,
            ]))
        ]);
    }

    public function get(string $workflow_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflows/' . $workflow_id);
    }

    public function update(
        string $workflow_id,
        ?string $expected_revision_id,
        ?string $name = null,
        ?string $description = null
    ): mixed {
        $payload = Util::omitNull([
            'name' => $name,
            'description' => $description,
        ]);
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one of name or description must be provided.');
        }
        $payload = array_merge(['expectedRevisionId' => $expected_revision_id], $payload);

        return $this->client->query(method: 'POST', endpoint: 'v1/workflows/' . $workflow_id, options: [
            'json' => $payload
        ]);
    }

    public function changeMailingList(
        string $workflow_id,
        ?string $expected_revision_id,
        mixed $mailing_list_id,
        ?bool $dry_run = null,
        ?string $queued_contact_policy = null
    ): mixed {
        $payload = array_merge(
            [
                'expectedRevisionId' => $expected_revision_id,
                'mailingListId' => $mailing_list_id,
            ],
            Util::omitNull([
                'dryRun' => $dry_run,
                'queuedContactPolicy' => $queued_contact_policy,
            ])
        );

        return $this->client->query(method: 'POST', endpoint: 'v1/workflows/' . $workflow_id . '/mailing-list', options: [
            'json' => $payload
        ]);
    }

    public function getNode(string $workflow_id, string $node_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id);
    }

    public function createNode(
        string $workflow_id,
        ?string $expected_revision_id,
        string $insert_mode,
        string $node_type_name,
        ?string $from_node_id = null,
        ?string $to_node_id = null,
        ?string $before_node_id = null
    ): mixed {
        $payload = [
            'expectedRevisionId' => $expected_revision_id,
            'insertMode' => $insert_mode,
            'nodeTypeName' => $node_type_name,
        ];

        if ($insert_mode === 'between') {
            if ($from_node_id === null || $to_node_id === null) {
                throw new \InvalidArgumentException(message: 'from_node_id and to_node_id are required when insert_mode is "between".');
            }
            if ($before_node_id !== null) {
                throw new \InvalidArgumentException(message: 'before_node_id is not permitted when insert_mode is "between".');
            }
            $payload['fromNodeId'] = $from_node_id;
            $payload['toNodeId'] = $to_node_id;
        } elseif ($insert_mode === 'before') {
            if ($from_node_id !== null) {
                throw new \InvalidArgumentException(message: 'from_node_id is not permitted when insert_mode is "before".');
            }
            if ($to_node_id !== null && $before_node_id !== null) {
                throw new \InvalidArgumentException(message: 'Provide either to_node_id or before_node_id when insert_mode is "before", not both.');
            }
            if ($to_node_id === null && $before_node_id === null) {
                throw new \InvalidArgumentException(message: 'to_node_id or before_node_id is required when insert_mode is "before".');
            }
            if ($to_node_id !== null) {
                $payload['toNodeId'] = $to_node_id;
            } else {
                $payload['beforeNodeId'] = $before_node_id;
            }
        } elseif ($insert_mode === 'after') {
            if ($from_node_id === null) {
                throw new \InvalidArgumentException(message: 'from_node_id is required when insert_mode is "after".');
            }
            if ($to_node_id !== null) {
                throw new \InvalidArgumentException(message: 'to_node_id is not permitted when insert_mode is "after".');
            }
            if ($before_node_id !== null) {
                throw new \InvalidArgumentException(message: 'before_node_id is not permitted when insert_mode is "after".');
            }
            $payload['fromNodeId'] = $from_node_id;
        } else {
            throw new \InvalidArgumentException(message: 'insert_mode must be "between", "before", or "after".');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/workflows/' . $workflow_id . '/nodes', options: [
            'json' => $payload
        ]);
    }

    public function updateNode(
        string $workflow_id,
        string $node_id,
        ?string $expected_revision_id,
        array $payload
    ): mixed {
        return $this->client->query(
            method: 'POST',
            endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id,
            options: [
                'json' => [
                    'expectedRevisionId' => $expected_revision_id,
                    'payload' => $payload,
                ]
            ]
        );
    }

    public function deleteNode(
        string $workflow_id,
        string $node_id,
        ?string $expected_revision_id,
        ?bool $dry_run = null,
        ?string $queued_contact_policy = null
    ): mixed {
        $payload = array_merge(
            ['expectedRevisionId' => $expected_revision_id],
            Util::omitNull([
                'dryRun' => $dry_run,
                'queuedContactPolicy' => $queued_contact_policy,
            ])
        );

        return $this->client->query(
            method: 'DELETE',
            endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id,
            options: [
                'json' => $payload
            ]
        );
    }

    public function addBranch(
        string $workflow_id,
        string $node_id,
        ?string $expected_revision_id
    ): mixed {
        return $this->client->query(
            method: 'POST',
            endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id . '/add-branch',
            options: [
                'json' => [
                    'expectedRevisionId' => $expected_revision_id,
                ]
            ]
        );
    }

    public function rerouteNode(
        string $workflow_id,
        string $node_id,
        ?string $expected_revision_id,
        string $new_target_node_id
    ): mixed {
        return $this->client->query(
            method: 'POST',
            endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id . '/reroute',
            options: [
                'json' => [
                    'expectedRevisionId' => $expected_revision_id,
                    'newTargetNodeId' => $new_target_node_id,
                ]
            ]
        );
    }

    public function deleteNodeRecursive(
        string $workflow_id,
        string $node_id,
        ?string $expected_revision_id,
        ?bool $dry_run = null,
        ?string $queued_contact_policy = null
    ): mixed {
        $payload = array_merge(
            ['expectedRevisionId' => $expected_revision_id],
            Util::omitNull([
                'dryRun' => $dry_run,
                'queuedContactPolicy' => $queued_contact_policy,
            ])
        );

        return $this->client->query(
            method: 'DELETE',
            endpoint: 'v1/workflows/' . $workflow_id . '/nodes/' . $node_id . '/recursive',
            options: [
                'json' => $payload
            ]
        );
    }
}
