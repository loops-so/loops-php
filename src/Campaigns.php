<?php

namespace Loops;

use Loops\LoopsClient;

class Campaigns
{
    private $client;

    public function __construct(LoopsClient $client)
    {
        $this->client = $client;
    }

    public function list(?int $per_page = null, ?string $cursor = null): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaigns', options: [
            'query' => Util::omitNull([
                'perPage' => $per_page,
                'cursor' => $cursor,
            ])
        ]);
    }

    public function create(
        string $name,
        ?string $campaign_group_id = null,
        ?string $mailing_list_id = null,
        ?string $audience_segment_id = null,
        ?array $audience_filter = null,
        ?array $scheduling = null
    ): mixed {
        $payload = ['name' => $name];
        if ($campaign_group_id !== null) {
            $payload['campaignGroupId'] = $campaign_group_id;
        }
        if ($mailing_list_id !== null) {
            $payload['mailingListId'] = $mailing_list_id;
        }
        if ($audience_segment_id !== null) {
            $payload['audienceSegmentId'] = $audience_segment_id;
        }
        if ($audience_filter !== null) {
            $payload['audienceFilter'] = $audience_filter;
        }
        if ($scheduling !== null) {
            $payload['scheduling'] = $scheduling;
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/campaigns', options: [
            'json' => $payload
        ]);
    }

    public function get(string $campaign_id): mixed
    {
        return $this->client->query(method: 'GET', endpoint: 'v1/campaigns/' . $campaign_id);
    }

    public function update(
        string $campaign_id,
        ?string $name = null,
        ?string $campaign_group_id = null,
        ?string $mailing_list_id = null,
        ?string $audience_segment_id = null,
        ?array $audience_filter = null,
        ?array $scheduling = null
    ): mixed {
        $payload = [];
        if ($name !== null) {
            $payload['name'] = $name;
        }
        if ($campaign_group_id !== null) {
            $payload['campaignGroupId'] = $campaign_group_id;
        }
        if ($mailing_list_id !== null) {
            $payload['mailingListId'] = $mailing_list_id;
        }
        if ($audience_segment_id !== null) {
            $payload['audienceSegmentId'] = $audience_segment_id;
        }
        if ($audience_filter !== null) {
            $payload['audienceFilter'] = $audience_filter;
        }
        if ($scheduling !== null) {
            $payload['scheduling'] = $scheduling;
        }
        if ($payload === []) {
            throw new \InvalidArgumentException(message: 'At least one field must be provided.');
        }

        return $this->client->query(method: 'POST', endpoint: 'v1/campaigns/' . $campaign_id, options: [
            'json' => $payload
        ]);
    }
}
