<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class WorkflowsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListWorkflows(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/workflows')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => ['nextCursor' => null],
          'data' => []
        ])
      ));

    $result = $this->client->workflows->list();

    $this->assertEquals([], $result['data']);
  }

  public function testGetWorkflowNode(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_456';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/workflows/' . $workflowId . '/nodes/' . $nodeId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $nodeId,
          'type' => 'email'
        ])
      ));

    $result = $this->client->workflows->getNode(workflow_id: $workflowId, node_id: $nodeId);

    $this->assertEquals($nodeId, $result['id']);
  }
}
