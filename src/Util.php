<?php

namespace Loops;

final class Util
{
  /**
   * Returns a copy of the array with null values removed.
   * Use for query parameters where null means "not provided".
   *
   * @param array<string, mixed> $params
   * @return array<string, mixed>
   */
  public static function omitNull(array $params): array
  {
    return array_filter($params, fn ($value) => $value !== null);
  }

  /**
   * Returns a copy of the array with unset sentinel values removed.
   * Use for patch parameters where null means "clear" and UNSET means "not provided".
   *
   * @param array<string, mixed> $params
   * @return array<string, mixed>
   */
  public static function omitUnset(array $params): array
  {
    return array_filter($params, fn ($value) => $value !== Core::UNSET);
  }
}
