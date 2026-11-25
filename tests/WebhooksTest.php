<?php

namespace Tests;

use Loops\Exceptions\WebhookVerificationError;
use Loops\Webhooks;
use PHPUnit\Framework\TestCase;

class WebhooksTest extends TestCase
{
  private string $secret;
  private string $secretWithoutPrefix;
  private string $secretBytes;

  protected function setUp(): void
  {
    $this->secretBytes = random_bytes(32);
    $this->secretWithoutPrefix = base64_encode($this->secretBytes);
    $this->secret = 'whsec_' . $this->secretWithoutPrefix;
  }

  private function sign(
    string $id,
    string|int $timestamp,
    string $rawBody,
    ?string $secret = null
  ): string {
    $secret ??= $this->secret;
    $encoded = str_starts_with($secret, 'whsec_')
      ? substr($secret, strlen('whsec_'))
      : $secret;
    $key = base64_decode($encoded, strict: true);
    $digest = base64_encode(
      hash_hmac('sha256', $id . '.' . $timestamp . '.' . $rawBody, $key, true)
    );

    return 'v1,' . $digest;
  }

  /**
   * @return array{id: string, timestamp: string, rawBody: string, headers: array<string, string>}
   */
  private function validPayload(
    ?string $id = null,
    ?int $timestamp = null,
    ?string $rawBody = null,
    ?string $secret = null,
    array $extraSignatures = []
  ): array {
    $id ??= 'msg_test_123';
    $timestamp ??= time();
    $rawBody ??= json_encode(['eventName' => 'email.sent'], JSON_THROW_ON_ERROR);
    $signature = $this->sign($id, $timestamp, $rawBody, $secret);
    $signatures = implode(' ', array_merge([$signature], $extraSignatures));

    return [
      'id' => $id,
      'timestamp' => (string) $timestamp,
      'rawBody' => $rawBody,
      'headers' => [
        'webhook-id' => $id,
        'webhook-timestamp' => (string) $timestamp,
        'webhook-signature' => $signatures,
      ],
    ];
  }

  public function testVerifiesValidRequestAndReturnsParsedPayload(): void
  {
    $payload = $this->validPayload();

    $event = Webhooks::verify(
      headers: $payload['headers'],
      rawBody: $payload['rawBody'],
      secret: $this->secret
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testAcceptsSecretsWithoutWhsecPrefix(): void
  {
    $payload = $this->validPayload(secret: $this->secretWithoutPrefix);

    $event = Webhooks::verify(
      headers: $payload['headers'],
      rawBody: $payload['rawBody'],
      secret: $this->secretWithoutPrefix
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testMatchesAnyV1SignatureWhenMultipleArePresent(): void
  {
    $payload = $this->validPayload(
      extraSignatures: ['v1,not-a-real-signature', 'v0,ignored']
    );

    $event = Webhooks::verify(
      headers: $payload['headers'],
      rawBody: $payload['rawBody'],
      secret: $this->secret
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testIsCaseInsensitiveForHeaderNames(): void
  {
    $payload = $this->validPayload();

    $event = Webhooks::verify(
      headers: [
        'Webhook-Id' => $payload['id'],
        'Webhook-Timestamp' => $payload['timestamp'],
        'Webhook-Signature' => $this->sign(
          $payload['id'],
          $payload['timestamp'],
          $payload['rawBody']
        ),
      ],
      rawBody: $payload['rawBody'],
      secret: $this->secret
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testJoinsArrayHeaderValues(): void
  {
    $payload = $this->validPayload();
    $good = $this->sign($payload['id'], $payload['timestamp'], $payload['rawBody']);

    $event = Webhooks::verify(
      headers: [
        'webhook-id' => [$payload['id']],
        'webhook-timestamp' => [$payload['timestamp']],
        'webhook-signature' => ['v1,bad-signature', $good],
      ],
      rawBody: $payload['rawBody'],
      secret: $this->secret
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testThrowsMissingSecretWhenSecretIsEmpty(): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: $payload['headers'],
        rawBody: $payload['rawBody'],
        secret: ''
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::MISSING_SECRET, $error->getErrorCode());
    }
  }

  public function testThrowsMissingSecretWhenSecretIsNotValidBase64(): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: $payload['headers'],
        rawBody: $payload['rawBody'],
        secret: 'whsec_!!!not-base64!!!'
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::MISSING_SECRET, $error->getErrorCode());
      $this->assertSame('Webhook signing secret is not valid base64', $error->getMessage());
    }
  }

  /**
   * @dataProvider emptyDecodedSecretProvider
   */
  public function testThrowsMissingSecretWhenDecodedSecretIsEmpty(string $secret): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: $payload['headers'],
        rawBody: $payload['rawBody'],
        secret: $secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::MISSING_SECRET, $error->getErrorCode());
      $this->assertSame('Missing webhook signing secret', $error->getMessage());
    }
  }

  /**
   * @return array<string, array{0: string}>
   */
  public function emptyDecodedSecretProvider(): array
  {
    return [
      'prefix only' => ['whsec_'],
      'whitespace' => ['   '],
    ];
  }

  public function testThrowsMissingHeadersWhenRequiredHeaderIsAbsent(): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: [
          'webhook-id' => 'msg_test_123',
          'webhook-timestamp' => (string) time(),
        ],
        rawBody: $payload['rawBody'],
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::MISSING_HEADERS, $error->getErrorCode());
    }
  }

  /**
   * @dataProvider invalidTimestampProvider
   */
  public function testThrowsInvalidTimestampForNonDigitTimestamp(string $timestamp): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: [
          'webhook-id' => $payload['id'],
          'webhook-timestamp' => $timestamp,
          'webhook-signature' => 'v1,abc',
        ],
        rawBody: $payload['rawBody'],
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::INVALID_TIMESTAMP, $error->getErrorCode());
    }
  }

  /**
   * @return array<string, array{0: string}>
   */
  public function invalidTimestampProvider(): array
  {
    return [
      'non-numeric' => ['not-a-number'],
      'float' => ['1730000000.5'],
      'exponent' => ['1e9'],
      'signed' => ['+1730000000'],
      'leading whitespace' => [' 1730000000'],
    ];
  }

  public function testThrowsInvalidTimestampWhenOutsideTolerance(): void
  {
    $stale = time() - 301;
    $payload = $this->validPayload(timestamp: $stale);

    try {
      Webhooks::verify(
        headers: $payload['headers'],
        rawBody: $payload['rawBody'],
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::INVALID_TIMESTAMP, $error->getErrorCode());
    }
  }

  public function testAllowsCustomToleranceSeconds(): void
  {
    $stale = time() - 301;
    $payload = $this->validPayload(timestamp: $stale);

    $event = Webhooks::verify(
      headers: $payload['headers'],
      rawBody: $payload['rawBody'],
      secret: $this->secret,
      toleranceSeconds: 600
    );

    $this->assertEquals(['eventName' => 'email.sent'], $event);
  }

  public function testThrowsInvalidSignatureForBadSignature(): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: [
          'webhook-id' => $payload['id'],
          'webhook-timestamp' => $payload['timestamp'],
          'webhook-signature' => 'v1,dGhpc2lzbm90YXJlYWxzaWduYXR1cmU=',
        ],
        rawBody: $payload['rawBody'],
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::INVALID_SIGNATURE, $error->getErrorCode());
    }
  }

  public function testThrowsInvalidSignatureWhenBodyHasBeenAltered(): void
  {
    $payload = $this->validPayload();

    try {
      Webhooks::verify(
        headers: $payload['headers'],
        rawBody: json_encode(['eventName' => 'tampered'], JSON_THROW_ON_ERROR),
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::INVALID_SIGNATURE, $error->getErrorCode());
    }
  }

  public function testThrowsInvalidBodyWhenVerifiedBodyIsNotJson(): void
  {
    $rawBody = 'not-json';
    $id = 'msg_test_123';
    $timestamp = (string) time();

    try {
      Webhooks::verify(
        headers: [
          'webhook-id' => $id,
          'webhook-timestamp' => $timestamp,
          'webhook-signature' => $this->sign($id, $timestamp, $rawBody),
        ],
        rawBody: $rawBody,
        secret: $this->secret
      );
      $this->fail('expected Webhooks::verify to throw');
    } catch (WebhookVerificationError $error) {
      $this->assertSame(WebhookVerificationError::INVALID_BODY, $error->getErrorCode());
    }
  }
}
