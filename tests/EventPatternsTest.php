<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class EventPatternsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListEventPatterns(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/event-patterns')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->eventPatterns->list();

    $this->assertEquals([], $result['data']);
  }

  public function testGetEventPattern(): void
  {
    $eventPatternId = 'ep_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/event-patterns/' . $eventPatternId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $eventPatternId,
          'eventName' => 'signup',
          'eventProperties' => [],
          'incomingWebhookPlatform' => null,
        ])
      ));

    $result = $this->client->eventPatterns->get(event_pattern_id: $eventPatternId);

    $this->assertEquals('signup', $result['eventName']);
  }

  public function testGetEventPatternByName(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/event-patterns/by-name/' . rawurlencode('Payment Received'))
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => 'ep_456',
          'eventName' => 'Payment Received',
          'eventProperties' => [],
          'incomingWebhookPlatform' => null,
        ])
      ));

    $result = $this->client->eventPatterns->getByName(event_name: 'Payment Received');

    $this->assertEquals('ep_456', $result['id']);
  }
}
