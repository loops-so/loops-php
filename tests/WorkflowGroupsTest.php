<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class WorkflowGroupsTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testCreateWorkflowGroup(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflow-groups',
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Onboarding'
            && $options['json']['description'] === 'Top of funnel';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['id' => 'grp_123', 'name' => 'Onboarding'])
      ));

    $result = $this->client->workflowGroups->create(name: 'Onboarding', description: 'Top of funnel');

    $this->assertEquals('grp_123', $result['id']);
  }

  public function testUpdateWorkflowGroup(): void
  {
    $groupId = 'grp_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflow-groups/' . $groupId,
        $this->callback(function ($options) {
          return $options['json']['name'] === 'Updated name';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode(['id' => $groupId, 'name' => 'Updated name'])
      ));

    $result = $this->client->workflowGroups->update(
      workflow_group_id: $groupId,
      name: 'Updated name'
    );

    $this->assertEquals('Updated name', $result['name']);
  }
}
