<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class ThemesTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListThemes(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with(
        'v1/themes',
        $this->callback(function ($options) {
          return $options['query']['perPage'] === 20
            && $options['query']['cursor'] === 'cursor123';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->themes->list(per_page: 20, cursor: 'cursor123');

    $this->assertTrue($result['success']);
  }

  public function testFindTheme(): void
  {
    $themeId = 'theme_abc123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/themes/' . $themeId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'themeId' => $themeId,
          'name' => 'Default',
          'styles' => [],
          'isDefault' => true,
          'createdAt' => '2025-01-01T00:00:00.000Z',
          'updatedAt' => '2025-01-01T00:00:00.000Z'
        ])
      ));

    $result = $this->client->themes->get(theme_id: $themeId);

    $this->assertEquals($themeId, $result['themeId']);
  }
}
