<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class CampaignsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListCampaigns(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/campaigns')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->campaigns->list();

    $this->assertTrue($result['success']);
  }

  public function testCreateCampaign(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/campaigns',
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Spring announcement';
        })
      )
      ->willReturn(new Response(
        status: 201,
        body: json_encode([
          'success' => true,
          'campaignId' => 'camp_123',
          'name' => 'Spring announcement',
          'status' => 'Draft',
          'createdAt' => '2025-01-01T00:00:00.000Z',
          'updatedAt' => '2025-01-01T00:00:00.000Z',
          'emailMessageId' => 'msg_123',
          'emailMessageContentRevisionId' => 'rev_123'
        ])
      ));

    $result = $this->client->campaigns->create(name: 'Spring announcement');

    $this->assertEquals('camp_123', $result['campaignId']);
  }

  public function testFindCampaign(): void
  {
    $campaignId = 'camp_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/campaigns/' . $campaignId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'campaignId' => $campaignId,
          'name' => 'Spring announcement',
          'status' => 'Draft',
          'createdAt' => '2025-01-01T00:00:00.000Z',
          'updatedAt' => '2025-01-01T00:00:00.000Z',
          'emailMessageId' => 'msg_123'
        ])
      ));

    $result = $this->client->campaigns->get(campaign_id: $campaignId);

    $this->assertEquals($campaignId, $result['campaignId']);
  }

  public function testUpdateCampaign(): void
  {
    $campaignId = 'camp_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/campaigns/' . $campaignId,
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Updated name';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'campaignId' => $campaignId,
          'name' => 'Updated name',
          'status' => 'Draft',
          'createdAt' => '2025-01-01T00:00:00.000Z',
          'updatedAt' => '2025-01-02T00:00:00.000Z',
          'emailMessageId' => 'msg_123'
        ])
      ));

    $result = $this->client->campaigns->update(
      campaign_id: $campaignId,
      name: 'Updated name'
    );

    $this->assertEquals('Updated name', $result['name']);
  }
}
