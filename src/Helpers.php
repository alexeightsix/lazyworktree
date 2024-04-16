<?php

declare(strict_types=1);

namespace App;

class Helpers
{
  public static function get_repo_from_clipboard_if_exists(): ?string
  {
    [$ok] = self::shell_exec(command: "which timeout && which xclip > /dev/null 2>&1");

    if (!$ok) {
      return null;
    }

    [$ok, $output] = self::shell_exec(command: "xclip -o");

    if (!$ok || empty($output)) {
      return null;
    }

    $output = trim($output[0]);
    $output = str_replace("\n", "", $output);

    preg_match("/[a-zA-Z0-9_]+@(.*).git/", $output, $matches);

    if (empty($matches)) {
      return null;
    }

    [$ok, $output] = self::shell_exec(command: "timeout 2 git ls-remote {$matches[0]}");

    return $ok ? $matches[0] : null;
  }

  public static function getRoot(): string
  {
    $root = file_exists(getcwd() . '/lazywt.json');

    if ($root) {
      return getcwd() . '/';
    }

    $child = file_exists('../../lazywt.json');

    if ($child) {
      return getcwd() . '/../../';
    }

    $is_in_worktrees = basename(getcwd());

    if ($is_in_worktrees === 'worktrees') {
      $root = getcwd() . '/../lazywt.json';
      if (file_exists($root)) {
        return getcwd() . '/../';
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
    exec("rm -r $dir");
    return true;
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
