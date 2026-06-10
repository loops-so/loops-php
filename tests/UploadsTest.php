<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class UploadsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testCreateUpload(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/uploads',
        $this->callback(function ($options) {
          return $options['json']['contentType'] === 'image/png'
            && $options['json']['contentLength'] === 102400;
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'emailAssetId' => 'asset_123',
          'presignedUrl' => 'https://example.com/upload'
        ])
      ));

    $result = $this->client->uploads->create(
      content_type: 'image/png',
      content_length: 102400
    );

    $this->assertEquals('asset_123', $result['emailAssetId']);
    $this->assertEquals('https://example.com/upload', $result['presignedUrl']);
  }

  public function testCompleteUpload(): void
  {
    $assetId = 'asset_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with('v1/uploads/' . $assetId . '/complete')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'emailAssetId' => $assetId,
          'finalUrl' => 'https://cdn.example.com/image.png'
        ])
      ));

    $result = $this->client->uploads->complete(id: $assetId);

    $this->assertEquals('https://cdn.example.com/image.png', $result['finalUrl']);
  }
}
