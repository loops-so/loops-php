<?php

namespace Tests;

use Loops\Core;
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

  public function testOmitUnsetRemovesUnsetValues(): void
  {
    $result = Util::omitUnset([
      'name' => 'Updated',
      'mailingListId' => Core::UNSET,
      'audienceFilter' => Core::UNSET,
    ]);

    $this->assertEquals(['name' => 'Updated'], $result);
  }

  public function testOmitUnsetKeepsExplicitNull(): void
  {
    $result = Util::omitUnset([
      'mailingListId' => null,
      'audienceFilter' => Core::UNSET,
    ]);

    $this->assertEquals(['mailingListId' => null], $result);
  }
}
