<?php

namespace App\Actions;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

use App\Actions\UnLink;
use App\Actions\ProcessHook;

class Link
{
  public static function run(string $worktree_path): void
  {
    if (!is_dir($worktree_path)) {
      throw new \Exception("The path {$worktree_path} is not a directory");
    }

    $cwd = getcwd();

    $current = $cwd . '/current';

    ProcessHook::run(ProcessHook::HOOK_BEFORE_CHANGE_LOCAL, $worktree_path);
    ProcessHook::run(ProcessHook::HOOK_BEFORE_CHANGE_GLOBAL, $cwd);

    if (is_link($current)) {
      warning('Removing old symlink: ' . readlink($current));
      UnLink::run();
    }

    if (!symlink($worktree_path, $current)) {
      throw new \Exception('Could not create symlink');
    }

    info('Switched to worktree: ' . $worktree_path);

    ProcessHook::run(ProcessHook::HOOK_AFTER_CHANGE_LOCAL, $worktree_path);
    ProcessHook::run(ProcessHook::HOOK_AFTER_CHANGE_GLOBAL, $cwd);
  }
}
