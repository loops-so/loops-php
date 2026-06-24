<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class ComponentsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListComponents(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/components', $this->callback(function ($options) {
        return $options['query'] === [];
      }))
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->components->list();

    $this->assertTrue($result['success']);
  }

  public function testFindComponent(): void
  {
    $componentId = 'component_abc123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/components/' . $componentId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'componentId' => $componentId,
          'name' => 'Header',
          'lmx' => '<Section />'
        ])
      ));

    $result = $this->client->components->get(component_id: $componentId);

    $this->assertEquals($componentId, $result['componentId']);
  }
}
