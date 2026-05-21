<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class DedicatedSendingIpsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testGetDedicatedSendingIps(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/dedicated-sending-ips')
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['1.2.3.4', '5.6.7.8'])
      ));

    $result = $this->client->dedicatedSendingIps->list();

    $this->assertEquals(['1.2.3.4', '5.6.7.8'], $result);
  }
}
