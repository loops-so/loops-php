<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class CampaignGroupsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testCreateCampaignGroup(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/campaign-groups',
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Newsletters'
            && $options['json']['description'] === 'Monthly';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['id' => 'grp_123', 'name' => 'Newsletters'])
      ));

    $result = $this->client->campaignGroups->create(name: 'Newsletters', description: 'Monthly');

    $this->assertEquals('grp_123', $result['id']);
  }

  public function testUpdateCampaignGroup(): void
  {
    $groupId = 'grp_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/campaign-groups/' . $groupId,
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Updated name';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['id' => $groupId, 'name' => 'Updated name'])
      ));

    $result = $this->client->campaignGroups->update(
      id: $groupId,
      name: 'Updated name'
    );

    $this->assertEquals('Updated name', $result['name']);
  }
}
