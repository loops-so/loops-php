<?php

namespace Tests;

use Loops\LoopsClient;
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Psr7\Response;

class EmailMessagesTest extends TestCase
{
  private LoopsClient $client;
  private \GuzzleHttp\Client $mockHttpClient;

  protected function setUp(): void
  {
    $this->mockHttpClient = $this->createMock(\GuzzleHttp\Client::class);
    $this->client = new LoopsClient('test_api_key');
    $this->client->setHttpClient($this->mockHttpClient);
  }

  public function testFindEmailMessage(): void
  {
    $emailMessageId = 'msg_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/email-messages/' . $emailMessageId)
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'emailMessageId' => $emailMessageId,
          'campaignId' => 'camp_123',
          'subject' => 'Hello',
          'previewText' => '',
          'fromName' => 'Loops',
          'fromEmail' => 'hello',
          'replyToEmail' => '',
          'lmx' => '<Email />',
          'contentRevisionId' => 'rev_123',
          'updatedAt' => '2025-01-01T00:00:00.000Z'
        ])
      ));

    $result = $this->client->emailMessages->get(email_message_id: $emailMessageId);

    $this->assertEquals($emailMessageId, $result['emailMessageId']);
  }

  public function testUpdateEmailMessage(): void
  {
    $emailMessageId = 'msg_123';
    $fields = [
      'expectedRevisionId' => 'rev_123',
      'subject' => 'Updated subject',
      'lmx' => '<Email><Text>Hello</Text></Email>'
    ];

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/email-messages/' . $emailMessageId,
        $this->callback(function ($options) use ($fields) {
          return $options['json'] === $fields;
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'emailMessageId' => $emailMessageId,
          'campaignId' => 'camp_123',
          'subject' => 'Updated subject',
          'previewText' => '',
          'fromName' => 'Loops',
          'fromEmail' => 'hello',
          'replyToEmail' => '',
          'lmx' => $fields['lmx'],
          'contentRevisionId' => 'rev_456',
          'updatedAt' => '2025-01-02T00:00:00.000Z'
        ])
      ));

    $result = $this->client->emailMessages->update(
      email_message_id: $emailMessageId,
      fields: $fields
    );

    $this->assertEquals('Updated subject', $result['subject']);
    $this->assertEquals('rev_456', $result['contentRevisionId']);
  }
}
