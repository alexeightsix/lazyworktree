<?php

declare(strict_types=1);

namespace App;

use Exception;

class Config
{
  public static function decodeConfig(): array
  {
    $root = Helpers::getRoot();
    $contents = file_get_contents($root . '/lazywt.json');

    if ($contents === false) {
      throw new Exception("Unable to read lazywt.json.");
    }

    return (array) json_decode(
      json: $contents,
      associative: true,
      flags: JSON_THROW_ON_ERROR
    );
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
