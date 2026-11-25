<?php

namespace Loops\Exceptions;

class WebhookVerificationError extends \Exception
{
  public const MISSING_HEADERS = 'MISSING_HEADERS';
  public const MISSING_SECRET = 'MISSING_SECRET';
  public const INVALID_TIMESTAMP = 'INVALID_TIMESTAMP';
  public const INVALID_SIGNATURE = 'INVALID_SIGNATURE';
  public const INVALID_BODY = 'INVALID_BODY';

  private string $errorCode;

  public function __construct(string $message, string $errorCode)
  {
    parent::__construct(message: $message);
    $this->errorCode = $errorCode;
  }

  public function getErrorCode(): string
  {
    return $this->errorCode;
  }
}
