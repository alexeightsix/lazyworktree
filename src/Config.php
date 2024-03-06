<?php

declare(strict_types=1);

namespace App;

use Exception;

class Config
{
  public static function decodeConfig(): array
  {
    $contents = file_get_contents(getcwd() . '/lazywt.json');

    if ($contents === false) {
      throw new Exception("Unable to read lazywt.json.");
    }

    if (!json_validate($contents)) {
      throw new Exception("Invalid JSON in lazywt.json.");
    }

    return json_decode($contents, true);
  }

  public static function has(string $key): bool
  {
    return array_key_exists($key, self::decodeConfig());
  }

  public static function get(string $key): string
  {
    return self::decodeConfig()[$key];
  }
}
