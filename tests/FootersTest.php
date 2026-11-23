<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class FootersTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testListFooters(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with(
        'v1/footers',
        $this->callback(function ($options) {
          return $options['query']['perPage'] === 20
            && $options['query']['cursor'] === 'cursor123';
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'pagination' => ['nextCursor' => null],
          'data' => [
            [
              'id' => 'clf0o1t2e3r4i5d6a7b8c9d0',
              'name' => 'Main footer',
              'isDefault' => true,
              'placement' => 'inside',
            ]
          ]
        ])
      ));

    $result = $this->client->footers->list(per_page: 20, cursor: 'cursor123');

    $this->assertEquals('Main footer', $result['data'][0]['name']);
  }

  public function testGetFooter(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/footers/' . $footerId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $footerId,
          'name' => 'Main footer',
          'isDefault' => true,
          'placement' => 'inside',
          'variants' => []
        ])
      ));

    $result = $this->client->footers->get(footer_id: $footerId);

    $this->assertEquals($footerId, $result['id']);
  }

  public function testCreateFooter(): void
  {
    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/footers',
        $this->callback(function ($options) {
          return $options['json'] === ['name' => 'Main footer'];
        })
      )
      ->willReturn(new Response(
        status: 201,
        body: json_encode([
          'id' => 'clf0o1t2e3r4i5d6a7b8c9d0',
          'name' => 'Main footer',
          'isDefault' => false,
          'placement' => 'inside',
          'variants' => []
        ])
      ));

    $result = $this->client->footers->create(name: 'Main footer');

    $this->assertEquals('Main footer', $result['name']);
  }

  public function testUpdateFooter(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/footers/' . $footerId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'name' => 'Marketing footer',
            'placement' => 'below',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $footerId,
          'name' => 'Marketing footer',
          'placement' => 'below',
          'variants' => []
        ])
      ));

    $result = $this->client->footers->update(
      footer_id: $footerId,
      name: 'Marketing footer',
      placement: 'below'
    );

    $this->assertEquals('Marketing footer', $result['name']);
  }

  public function testUpdateFooterRequiresField(): void
  {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage('At least one of name or placement must be provided.');

    $this->client->footers->update(footer_id: 'clf0o1t2e3r4i5d6a7b8c9d0');
  }

  public function testDeleteFooter(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('delete')
      ->with('v1/footers/' . $footerId)
      ->willReturn(new Response(status: 204));

    $result = $this->client->footers->delete(footer_id: $footerId);

    $this->assertNull($result);
  }

  public function testSetDefaultFooter(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/footers/' . $footerId . '/set-default',
        $this->callback(function ($options) {
          return $options['json'] === ['existingEmails' => 'all'];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $footerId,
          'isDefault' => true,
          'updatedEmailCount' => 12,
          'variants' => []
        ])
      ));

    $result = $this->client->footers->setDefault(
      footer_id: $footerId,
      existing_emails: 'all'
    );

    $this->assertEquals(12, $result['updatedEmailCount']);
  }

  public function testGetFooterVariant(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';
    $variantId = 'clv1a2r3i4a5n6t7i8d9e0f1';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/footers/' . $footerId . '/variants/' . $variantId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $variantId,
          'footerId' => $footerId,
          'contentRevisionId' => 'clr2e3v4i5s6i7o8n9i0d1e2',
          'draftLmx' => '{system.company_name}',
        ])
      ));

    $result = $this->client->footers->getVariant(
      footer_id: $footerId,
      variant_id: $variantId
    );

    $this->assertEquals($variantId, $result['id']);
  }

  public function testUpdateFooterVariant(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';
    $variantId = 'clv1a2r3i4a5n6t7i8d9e0f1';
    $lmx = '{system.company_name} Unsubscribe';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/footers/' . $footerId . '/variants/' . $variantId,
        $this->callback(function ($options) use ($lmx) {
          return $options['json'] === [
            'expectedRevisionId' => 'clr2e3v4i5s6i7o8n9i0d1e2',
            'lmx' => $lmx,
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $variantId,
          'contentRevisionId' => 'clr3f4w5j6t7k8p9q0r1s2t3',
          'draftLmx' => $lmx,
        ])
      ));

    $result = $this->client->footers->updateVariant(
      footer_id: $footerId,
      variant_id: $variantId,
      expected_revision_id: 'clr2e3v4i5s6i7o8n9i0d1e2',
      lmx: $lmx
    );

    $this->assertEquals($lmx, $result['draftLmx']);
  }

  public function testPublishFooterVariant(): void
  {
    $footerId = 'clf0o1t2e3r4i5d6a7b8c9d0';
    $variantId = 'clv1a2r3i4a5n6t7i8d9e0f1';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/footers/' . $footerId . '/variants/' . $variantId . '/publish',
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'clr2e3v4i5s6i7o8n9i0d1e2',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $variantId,
          'publishedAt' => '2026-11-03T08:30:00.000Z',
          'publishedLmx' => '{system.company_name}',
        ])
      ));

    $result = $this->client->footers->publishVariant(
      footer_id: $footerId,
      variant_id: $variantId,
      expected_revision_id: 'clr2e3v4i5s6i7o8n9i0d1e2'
    );

    $this->assertEquals('2026-11-03T08:30:00.000Z', $result['publishedAt']);
  }
}
