<?php

declare(strict_types=1);

namespace App;

class Helpers
{

  public static function shell_exec(string $command): array
  {
    $exit_code = -1;
    $output = null;
    exec($command, $output, $exit_code);

    return [
      $exit_code === 0,
      $output
    ];
  }
  public static function rmDirRecursive(string $dir): bool
  {
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
      $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink'); // @phpstan-ignore-line
      $todo($fileinfo->getRealPath()); // @phpstan-ignore-line
    }

    return rmdir($dir);
  }

  public static function slugify(string $string): string | null
  {
    $string = preg_replace('~[^\pL\d]+~u', '_', $string);
    $string = preg_replace('~[^-\w]+~', '', $string); // @phpstan-ignore-line
    $string = trim($string, '_'); // @phpstan-ignore-line
    $string = preg_replace('~-+~', '_', $string);
    $string = strtolower($string); // @phpstan-ignore-line

    if (empty($string)) {
      return null;
    }

    return $string;
  }
}
