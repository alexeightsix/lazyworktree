<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

use App\Config;
use App\GitService;
use App\Helpers;

class Delete
{
  const MENU_NAME = 'Delete Worktree';

  public static function run(): void
  {
    $git_root = Helpers::findGitFolder();

    $worktrees = GitService::getWorktrees(git_path: $git_root);

    if ($worktrees->isEmpty()) {
      throw new \Exception("No worktrees found.");
    }

    $options = [];

    foreach ($worktrees->get() as $worktree) {
      $options[$worktree->path] = $worktree->baseName;
    }

    $path = (string) select(
      label: 'Select a worktree to delete',
      options: $options
    );

    ProcessHook::run(ProcessHook::HOOK_BEFORE_DELETE_LOCAL, $path);
    ProcessHook::run(ProcessHook::HOOK_BEFORE_DELETE_GLOBAL, $path);

    GitService::deleteWorktree($git_root, $path);

    $worktrees = GitService::getWorktrees($git_root);

    ProcessHook::run(ProcessHook::HOOK_AFTER_DELETE_LOCAL, $path);
    ProcessHook::run(ProcessHook::HOOK_AFTER_DELETE_GLOBAL, $path);

    info("Worktree $options[$path] [{$path}] deleted");
  }
}
