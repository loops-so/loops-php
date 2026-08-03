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
    $lmx = '<Email><Text>Hello</Text></Email>';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/email-messages/' . $emailMessageId,
        $this->callback(function ($options) use ($lmx) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'subject' => 'Updated subject',
            'lmx' => $lmx,
          ];
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
          'lmx' => $lmx,
          'contentRevisionId' => 'rev_456',
          'updatedAt' => '2025-01-02T00:00:00.000Z'
        ])
      ));

    $result = $this->client->emailMessages->update(
      email_message_id: $emailMessageId,
      expected_revision_id: 'rev_123',
      subject: 'Updated subject',
      lmx: $lmx
    );

    $this->assertEquals('Updated subject', $result['subject']);
    $this->assertEquals('rev_456', $result['contentRevisionId']);
  }

  public function testPreviewEmailMessage(): void
  {
    $emailMessageId = 'msg_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/email-messages/' . $emailMessageId . '/preview',
        $this->callback(function ($options) {
          return $options['json']['emails'] === ['test@example.com']
            && $options['json']['contactProperties'] === ['firstName' => 'Ada'];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'success' => true,
          'emailMessageId' => $emailMessageId
        ])
      ));

    $result = $this->client->emailMessages->preview(
      email_message_id: $emailMessageId,
      emails: ['test@example.com'],
      contact_properties: ['firstName' => 'Ada']
    );

    $this->assertTrue($result['success']);
  }

  public function testGuardianEmailMessage(): void
  {
    $emailMessageId = 'msg_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('get')
      ->with('v1/email-messages/' . $emailMessageId . '/guardian')
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'errors' => [],
          'warnings' => []
        ])
      ));

    $result = $this->client->emailMessages->guardian(email_message_id: $emailMessageId);

    $this->assertEquals([], $result['errors']);
    $this->assertEquals([], $result['warnings']);
  }

  public function testUpdateEmailMessageWithExtraFields(): void
  {
    $emailMessageId = 'msg_123';

    $this->mockHttpClient
      ->expects($this->once())
      ->method('post')
      ->with(
        'v1/email-messages/' . $emailMessageId,
        $this->callback(function ($options) {
          return $options['json'] === [
            'expectedRevisionId' => 'rev_123',
            'ccEmail' => 'cc@example.com',
            'bccEmail' => 'bcc@example.com',
            'languageCode' => 'en',
            'emailFormat' => 'plain',
          ];
        })
      )
      ->willReturn(new Response(
        status: 200,
        body: json_encode([
          'id' => $emailMessageId,
          'emailFormat' => 'plain',
          'contentRevisionId' => 'rev_456',
        ])
      ));

    $result = $this->client->emailMessages->update(
      email_message_id: $emailMessageId,
      expected_revision_id: 'rev_123',
      cc_email: 'cc@example.com',
      bcc_email: 'bcc@example.com',
      language_code: 'en',
      email_format: 'plain'
    );

    $this->assertEquals('plain', $result['emailFormat']);
  }
}
