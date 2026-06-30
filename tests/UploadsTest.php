<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class UploadsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;
  private \GuzzleHttp\Client $mockUploadHttpClient;
  private string $imagePath;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->mockUploadHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
    $this->client->setUploadHttpClient($this->mockUploadHttpClient);

    $this->imagePath = sys_get_temp_dir() . '/loops_upload_test.png';
    file_put_contents(
      $this->imagePath,
      base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==')
    );
  }

  protected function tearDown(): void
  {
    if (file_exists($this->imagePath)) {
      unlink($this->imagePath);
    }
  }

  public function testUpload(): void
  {
    $presignedUrl = 'https://example.com/upload';
    $assetId = 'asset_123';
    $fileContents = file_get_contents($this->imagePath);
    $contentLength = strlen($fileContents);

    $this->mockHttpClient
      ->expects($this->exactly(2))
      ->method('post')
      ->willReturnCallback(function ($endpoint, $options = []) use ($presignedUrl, $assetId, $contentLength) {
        if ($endpoint === 'v1/uploads') {
          $this->assertEquals('image/png', $options['json']['contentType']);
          $this->assertEquals($contentLength, $options['json']['contentLength']);

          return new Response(
            status: 200,
            body: json_encode([
              'emailAssetId' => $assetId,
              'presignedUrl' => $presignedUrl,
            ])
          );
        }

        if ($endpoint === 'v1/uploads/' . $assetId . '/complete') {
          return new Response(
            status: 200,
            body: json_encode([
              'emailAssetId' => $assetId,
              'finalUrl' => 'https://cdn.example.com/image.png',
            ])
          );
        }

        $this->fail('Unexpected POST endpoint: ' . $endpoint);
      });

    $this->mockUploadHttpClient
      ->expects($this->once())
      ->method('put')
      ->with(
        $presignedUrl,
        $this->callback(function ($options) use ($fileContents, $contentLength) {
          return !isset($options['headers']['Authorization'])
            && $options['headers']['Content-Type'] === 'image/png'
            && $options['headers']['Content-Length'] === (string) $contentLength
            && $options['body'] === $fileContents;
        })
      )
      ->willReturn(new Response(status: 200));

    $result = $this->client->uploads->upload(path: $this->imagePath);

    $this->assertEquals($assetId, $result['emailAssetId']);
    $this->assertEquals('https://cdn.example.com/image.png', $result['finalUrl']);
  }

  public function testUploadRejectsMissingFile(): void
  {
    $this->expectException(\InvalidArgumentException::class);

    $this->client->uploads->upload(path: '/path/does/not/exist.png');
  }

  public function testUploadRejectsUnsupportedType(): void
  {
    $path = sys_get_temp_dir() . '/loops_upload_test.txt';
    file_put_contents($path, 'not an image');

    try {
      $this->expectException(\InvalidArgumentException::class);
      $this->client->uploads->upload(path: $path);
    } finally {
      unlink($path);
    }
  }
}
