<?php

declare(strict_types=1);

namespace App;

class Helpers
{

  public static function getRoot(): string
  {
    $root = file_exists(getcwd() . '/lazywt.json');

    if ($root) {
      return getcwd();
    }

    $child = file_exists('../../lazywt.json');

    if ($child) {
      return getcwd() . '/../../';
    }

    $is_in_worktrees = basename(getcwd());

    if ($is_in_worktrees === 'worktrees') {
      $root = getcwd() . '/../lazywt.json';
      if (file_exists($root)) {
        return getcwd() . '/..';
      }
    }

    throw new \Exception('Unable to locate lazywt.json');
  }

  public static function findGitFolder(): string
  {
    try {
      $cwd = self::getRoot();
      $config = $cwd . '/lazywt.json';

      $contents = file_get_contents($config);

      $config = json_decode(
        json: $contents,
        associative: true,
        flags: JSON_THROW_ON_ERROR
      );

      $valid = file_exists($cwd . '/' . $config['git_folder'] . '/config');

      if (!$valid) {
        throw new \Exception('Invalid git folder');
      }

      return $cwd . '/' . $config['git_folder'];
    } catch (\Exception) {
      throw new \Exception('Unable to locate git folder');
    }
  }

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
