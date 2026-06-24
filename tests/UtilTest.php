<?php

namespace Tests;

use Loops\Util;
use PHPUnit\Framework\TestCase;

class UtilTest extends TestCase
{
  public function testOmitNullRemovesNullValues(): void
  {
    $result = Util::omitNull([
      'name' => 'Test',
      'campaignGroupId' => null,
      'mailingListId' => 'list_123',
      'audienceSegmentId' => null,
    ]);

    $this->assertEquals([
      'name' => 'Test',
      'mailingListId' => 'list_123',
    ], $result);
  }

  public function testOmitNullKeepsFalsyNonNullValues(): void
  {
    $result = Util::omitNull([
      'addToAudience' => false,
      'dataVariables' => [],
      'count' => 0,
    ]);

    $this->assertEquals([
      'addToAudience' => false,
      'dataVariables' => [],
      'count' => 0,
    ], $result);
  }
}
