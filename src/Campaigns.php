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
        $payload = array_merge(
            ['name' => $name],
            Util::omitNull([
                'campaignGroupId' => $campaign_group_id,
                'mailingListId' => $mailing_list_id,
                'audienceSegmentId' => $audience_segment_id,
                'audienceFilter' => $audience_filter,
                'scheduling' => $scheduling,
            ])
        );

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
        mixed $mailing_list_id = Core::UNSET,
        mixed $audience_segment_id = Core::UNSET,
        mixed $audience_filter = Core::UNSET,
        ?array $scheduling = null
    ): mixed {
        $payload = array_merge(
            Util::omitNull([
                'name' => $name,
                'campaignGroupId' => $campaign_group_id,
                'scheduling' => $scheduling,
            ]),
            Util::omitUnset([
                'mailingListId' => $mailing_list_id,
                'audienceSegmentId' => $audience_segment_id,
                'audienceFilter' => $audience_filter,
            ]),
        );

        return $this->client->query(method: 'POST', endpoint: 'v1/campaigns/' . $campaign_id, options: [
            'json' => $payload
        ]);
    }
}
