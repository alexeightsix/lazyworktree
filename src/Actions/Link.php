<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

use App\Actions\ProcessHook;
use App\Helpers;
use App\Worktree;

class Link
{
  public static function run(Worktree $worktree): void
  {
    $cwd = Helpers::getRoot();

    if (!$cwd) {
      throw new \Exception('Could not get current working directory');
    }

    $current = $cwd . 'current';

    if (!isset($worktree->path)) {
      throw new \Exception('No worktree found');
    }

    ProcessHook::run(hook: ProcessHook::HOOK_BEFORE_CHANGE_LOCAL, cwd: $worktree->path);
    ProcessHook::run(hook: ProcessHook::HOOK_BEFORE_CHANGE_GLOBAL, cwd: $cwd);

    if (is_link(filename: $current)) {
      UnLinkCurrent::run(link: $cwd);
    }

    if (!symlink(target: $worktree->path, link: $current)) {
      throw new \Exception('Could not create symlink');
    }

    info(message: 'Switched To Worktree: ' . basename($worktree->path));

    ProcessHook::run(hook: ProcessHook::HOOK_AFTER_CHANGE_LOCAL, cwd: $worktree->path);
    ProcessHook::run(hook: ProcessHook::HOOK_AFTER_CHANGE_GLOBAL, cwd: $cwd);
  }
}
