<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

use App\Actions\UnLink;
use App\Actions\ProcessHook;

use App\Worktree;

class Link
{
  public static function run(Worktree $worktree): void
  {
    $cwd = getcwd();

    if (!$cwd) {
      throw new \Exception('Could not get current working directory');
    }

    $current = $cwd . '/current';

    ProcessHook::run(ProcessHook::HOOK_BEFORE_CHANGE_LOCAL, $worktree->path);
    ProcessHook::run(ProcessHook::HOOK_BEFORE_CHANGE_GLOBAL, $worktree->path);

    if (is_link($current)) {
      warning('Removing old symlink: ' . readlink($current));
      UnLinkCurrent::run();
    }

    if (!symlink($worktree->path, $current)) {
      throw new \Exception('Could not create symlink');
    }

    info('Switched to worktree: ' . $worktree->path);

    ProcessHook::run(ProcessHook::HOOK_AFTER_CHANGE_LOCAL, $worktree->path);
    ProcessHook::run(ProcessHook::HOOK_AFTER_CHANGE_GLOBAL, $cwd);
  }
}
