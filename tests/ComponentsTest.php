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

  public function testCreateComponent(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/components',
        $this->callback(function ($options) {
          return $options['json'] === [
            'name' => 'Header',
            'lmx' => '<Paragraph>Welcome</Paragraph>',
          ];
        })
      )
      ->willReturn(new Response(
        status: 201,
        body: json_encode([
          'id' => 'component_abc123',
          'name' => 'Header',
          'lmx' => '<Paragraph>Welcome</Paragraph>',
        ])
      ));

    $result = $this->client->components->create(
      name: 'Header',
      lmx: '<Paragraph>Welcome</Paragraph>'
    );

    $this->assertEquals('Header', $result['name']);
  }

  public function testUpdateComponent(): void
  {
    $componentId = 'component_abc123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/components/' . $componentId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'lmx' => '<Paragraph>Updated</Paragraph>',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $componentId,
          'name' => 'Header',
          'lmx' => '<Paragraph>Updated</Paragraph>',
          'affectedEmailCount' => 3,
        ])
      ));

    $result = $this->client->components->update(
      component_id: $componentId,
      lmx: '<Paragraph>Updated</Paragraph>'
    );

    $this->assertEquals(3, $result['affectedEmailCount']);
  }
}
