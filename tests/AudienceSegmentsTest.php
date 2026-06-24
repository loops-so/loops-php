<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class AudienceSegmentsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListAudienceSegments(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with(
        'v1/audience-segments',
        $this->callback(function ($options) {
          return $options['query']['perPage'] === 20
            && $options['query']['cursor'] === 'cursor123';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->audienceSegments->list(per_page: 20, cursor: 'cursor123');

    $this->assertEquals([], $result['data']);
  }

  public function testGetAudienceSegment(): void
  {
    $segmentId = 'seg_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/audience-segments/' . $segmentId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $segmentId,
          'name' => 'Active subscribers'
        ])
      ));

    $result = $this->client->audienceSegments->get(id: $segmentId);

    $this->assertEquals($segmentId, $result['id']);
  }
}
