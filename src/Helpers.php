<?php

declare(strict_types=1);

namespace App;

class Helpers
{
  public static function rmDirRecursive(string $dir): bool
  {
    $files = new \RecursiveIteratorIterator(
      new \RecursiveDirectoryIterator($dir, \RecursiveDirectoryIterator::SKIP_DOTS),
      \RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($files as $fileinfo) {
      $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
      $todo($fileinfo->getRealPath());
    }

    return rmdir($dir);
  }

  public static function slugify(string $string): string | null
  {
    $string = preg_replace('~[^\pL\d]+~u', '_', $string);
    $string = preg_replace('~[^-\w]+~', '', $string);
    $string = trim($string, '_');
    $string = preg_replace('~-+~', '_', $string);
    $string = strtolower($string);

    if (empty($string)) {
      return null;
    }

    return $string;
  }
}
