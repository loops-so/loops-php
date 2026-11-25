<?php

namespace Loops;

use Loops\Exceptions\WebhookVerificationError;

final class Webhooks
{
  private const SECRET_PREFIX = 'whsec_';
  private const DEFAULT_TOLERANCE_SECONDS = 300;

  /**
   * Verifies that a webhook request was signed by Loops.
   *
   * Uses the signing secret from Settings → Webhooks. Pass the raw request
   * body string, not a parsed JSON object.
   *
   * @param array<string, string|string[]|null> $headers Request headers. Keys
   *   are matched case-insensitively. Array values are joined with spaces.
   * @param string $rawBody Exact raw request body as a string. Do not pass a
   *   parsed JSON object — parsing or reformatting breaks signature verification.
   * @param string $secret Webhook signing secret (may include the `whsec_` prefix).
   * @param int $toleranceSeconds Maximum allowed age of the webhook timestamp,
   *   in seconds. Defaults to 300 (5 minutes).
   * @return mixed The decoded JSON payload when verification succeeds.
   * @throws WebhookVerificationError When headers, timestamp, signature,
   *   secret, or body are invalid.
   */
  public static function verify(
    array $headers,
    string $rawBody,
    string $secret,
    int $toleranceSeconds = self::DEFAULT_TOLERANCE_SECONDS
  ): mixed {
    if ($secret === '') {
      throw new WebhookVerificationError(
        message: 'Missing webhook signing secret',
        errorCode: WebhookVerificationError::MISSING_SECRET
      );
    }

    $id = self::getHeader($headers, 'webhook-id');
    $timestamp = self::getHeader($headers, 'webhook-timestamp');
    $signatureHeader = self::getHeader($headers, 'webhook-signature');

    if ($id === null || $timestamp === null || $signatureHeader === null) {
      throw new WebhookVerificationError(
        message: 'Missing webhook-id, webhook-timestamp, or webhook-signature header',
        errorCode: WebhookVerificationError::MISSING_HEADERS
      );
    }

    if (!ctype_digit($timestamp)) {
      throw new WebhookVerificationError(
        message: 'webhook-timestamp is not a valid number',
        errorCode: WebhookVerificationError::INVALID_TIMESTAMP
      );
    }

    $ts = (int) $timestamp;
    $now = time();
    if (abs($now - $ts) > $toleranceSeconds) {
      throw new WebhookVerificationError(
        message: 'webhook-timestamp is outside the allowed tolerance',
        errorCode: WebhookVerificationError::INVALID_TIMESTAMP
      );
    }

    $encoded = str_starts_with($secret, self::SECRET_PREFIX)
      ? substr($secret, strlen(self::SECRET_PREFIX))
      : $secret;
    $key = base64_decode($encoded, strict: true);
    if ($key === false) {
      throw new WebhookVerificationError(
        message: 'Webhook signing secret is not valid base64',
        errorCode: WebhookVerificationError::MISSING_SECRET
      );
    }
    if ($key === '') {
      throw new WebhookVerificationError(
        message: 'Missing webhook signing secret',
        errorCode: WebhookVerificationError::MISSING_SECRET
      );
    }

    $expected = base64_encode(
      hash_hmac('sha256', $id . '.' . $timestamp . '.' . $rawBody, $key, true)
    );

    $matched = false;
    foreach (preg_split('/\s+/', trim($signatureHeader)) ?: [] as $entry) {
      if ($entry === '') {
        continue;
      }
      $comma = strpos($entry, ',');
      if ($comma === false) {
        continue;
      }
      $version = substr($entry, 0, $comma);
      $value = substr($entry, $comma + 1);
      if ($version === 'v1' && hash_equals($expected, $value)) {
        $matched = true;
        break;
      }
    }

    if (!$matched) {
      throw new WebhookVerificationError(
        message: 'Invalid signature',
        errorCode: WebhookVerificationError::INVALID_SIGNATURE
      );
    }

    try {
      $payload = json_decode(json: $rawBody, associative: true, flags: JSON_THROW_ON_ERROR);
    } catch (\JsonException $e) {
      throw new WebhookVerificationError(
        message: 'Webhook body is not valid JSON',
        errorCode: WebhookVerificationError::INVALID_BODY
      );
    }

    return $payload;
  }

  /**
   * @param array<string, string|string[]|null> $headers
   */
  private static function getHeader(array $headers, string $name): ?string
  {
    $target = strtolower($name);

    foreach ($headers as $key => $value) {
      if (strtolower((string) $key) !== $target) {
        continue;
      }

      if (is_array($value)) {
        return count($value) > 0 ? implode(' ', $value) : null;
      }

      return $value;
    }

    return null;
  }
}
