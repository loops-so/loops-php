<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class TransactionalGroupsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListTransactionalGroups(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/transactional-groups')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->transactionalGroups->list();

    $this->assertEquals([], $result['data']);
  }

  public function testGetTransactionalGroup(): void
  {
    $groupId = 'tgrp_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/transactional-groups/' . $groupId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['id' => $groupId, 'name' => 'Onboarding'])
      ));

    $result = $this->client->transactionalGroups->get(transactional_group_id: $groupId);

    $this->assertEquals($groupId, $result['id']);
  }
}
