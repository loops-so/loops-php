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

  public function testCreateWorkflow(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows',
        $this->callback(function ($options) {
          return $options['json'] === [
            'name' => 'Welcome series',
            'description' => 'Onboarding emails',
            'mailingListId' => 'list_123',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => 'wf_123',
          'name' => 'Welcome series',
          'status' => 'Draft',
        ])
      ));

    $result = $this->client->workflows->create(
      name: 'Welcome series',
      description: 'Onboarding emails',
      mailing_list_id: 'list_123'
    );

    $this->assertEquals('wf_123', $result['id']);
  }

  public function testUpdateWorkflow(): void
  {
    $workflowId = 'wf_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'name' => 'Updated name',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $workflowId,
          'name' => 'Updated name',
          'workflowRevisionId' => 'rev_456',
        ])
      ));

    $result = $this->client->workflows->update(
      workflow_id: $workflowId,
      expected_revision_id: 'rev_123',
      name: 'Updated name'
    );

    $this->assertEquals('Updated name', $result['name']);
  }

  public function testChangeMailingList(): void
  {
    $workflowId = 'wf_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/mailing-list',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'mailingListId' => 'list_456',
            'dryRun' => true,
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'status' => 'dryRun',
          'mailingListId' => 'list_456',
          'queuedContactCount' => 0,
          'queuedContactLimitReached' => false,
        ])
      ));

    $result = $this->client->workflows->changeMailingList(
      workflow_id: $workflowId,
      expected_revision_id: 'rev_123',
      mailing_list_id: 'list_456',
      dry_run: true
    );

    $this->assertEquals('dryRun', $result['status']);
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
          'typeName' => 'SendEmailAction'
        ])
      ));

    $result = $this->client->workflows->getNode(workflow_id: $workflowId, node_id: $nodeId);

    $this->assertEquals($nodeId, $result['id']);
  }

  public function testCreateNodeBetween(): void
  {
    $workflowId = 'wf_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'insertMode' => 'between',
            'nodeTypeName' => 'TimerAction',
            'fromNodeId' => 'node_1',
            'toNodeId' => 'node_2',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'node' => ['id' => 'node_3', 'typeName' => 'TimerAction'],
          'workflow' => ['id' => $workflowId],
        ])
      ));

    $result = $this->client->workflows->createNode(
      workflow_id: $workflowId,
      expected_revision_id: 'rev_123',
      insert_mode: 'between',
      node_type_name: 'TimerAction',
      from_node_id: 'node_1',
      to_node_id: 'node_2'
    );

    $this->assertEquals('node_3', $result['node']['id']);
  }

  public function testCreateNodeBeforeWithToNodeId(): void
  {
    $workflowId = 'wf_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'insertMode' => 'before',
            'nodeTypeName' => 'TimerAction',
            'toNodeId' => 'node_2',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'node' => ['id' => 'node_3', 'typeName' => 'TimerAction'],
          'workflow' => ['id' => $workflowId],
        ])
      ));

    $result = $this->client->workflows->createNode(
      workflow_id: $workflowId,
      expected_revision_id: 'rev_123',
      insert_mode: 'before',
      node_type_name: 'TimerAction',
      to_node_id: 'node_2'
    );

    $this->assertEquals('node_3', $result['node']['id']);
  }

  public function testCreateNodeAfter(): void
  {
    $workflowId = 'wf_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'insertMode' => 'after',
            'nodeTypeName' => 'TimerAction',
            'fromNodeId' => 'node_1',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'node' => ['id' => 'node_3', 'typeName' => 'TimerAction'],
          'workflow' => ['id' => $workflowId],
        ])
      ));

    $result = $this->client->workflows->createNode(
      workflow_id: $workflowId,
      expected_revision_id: 'rev_123',
      insert_mode: 'after',
      node_type_name: 'TimerAction',
      from_node_id: 'node_1'
    );

    $this->assertEquals('node_3', $result['node']['id']);
  }

  public function testUpdateNode(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_456';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes/' . $nodeId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'payload' => [
              'amount' => 2,
              'unit' => 'd',
            ],
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $nodeId,
          'typeName' => 'TimerAction',
          'amount' => 2,
          'unit' => 'd',
          'workflowRevisionId' => 'rev_456',
        ])
      ));

    $result = $this->client->workflows->updateNode(
      workflow_id: $workflowId,
      node_id: $nodeId,
      expected_revision_id: 'rev_123',
      payload: ['amount' => 2, 'unit' => 'd']
    );

    $this->assertEquals(2, $result['amount']);
  }

  public function testDeleteNode(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_456';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('delete')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes/' . $nodeId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'queuedContactPolicy' => 'discard',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'status' => 'deleted',
          'nodeIds' => [$nodeId],
          'workflowRevisionId' => 'rev_456',
          'queuedContactCount' => 0,
          'queuedContactLimitReached' => false,
        ])
      ));

    $result = $this->client->workflows->deleteNode(
      workflow_id: $workflowId,
      node_id: $nodeId,
      expected_revision_id: 'rev_123',
      queued_contact_policy: 'discard'
    );

    $this->assertEquals('deleted', $result['status']);
  }

  public function testAddBranch(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_branch';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes/' . $nodeId . '/add-branch',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'node' => ['id' => 'node_child', 'typeName' => 'AudienceFilter'],
          'workflow' => ['id' => $workflowId],
        ])
      ));

    $result = $this->client->workflows->addBranch(
      workflow_id: $workflowId,
      node_id: $nodeId,
      expected_revision_id: 'rev_123'
    );

    $this->assertEquals('node_child', $result['node']['id']);
  }

  public function testRerouteNode(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_source';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes/' . $nodeId . '/reroute',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'newTargetNodeId' => 'node_target',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $nodeId,
          'nextNodeIds' => ['node_target'],
          'workflowRevisionId' => 'rev_456',
          'workflow' => ['id' => $workflowId],
        ])
      ));

    $result = $this->client->workflows->rerouteNode(
      workflow_id: $workflowId,
      node_id: $nodeId,
      expected_revision_id: 'rev_123',
      new_target_node_id: 'node_target'
    );

    $this->assertEquals(['node_target'], $result['nextNodeIds']);
  }

  public function testDeleteNodeRecursive(): void
  {
    $workflowId = 'wf_123';
    $nodeId = 'node_456';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('delete')
      ->with(
        'v1/workflows/' . $workflowId . '/nodes/' . $nodeId . '/recursive',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'dryRun' => true,
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'status' => 'dryRun',
          'nodeIds' => [$nodeId, 'node_789'],
          'queuedContactCount' => 0,
        ])
      ));

    $result = $this->client->workflows->deleteNodeRecursive(
      workflow_id: $workflowId,
      node_id: $nodeId,
      expected_revision_id: 'rev_123',
      dry_run: true
    );

    $this->assertEquals('dryRun', $result['status']);
  }
}
