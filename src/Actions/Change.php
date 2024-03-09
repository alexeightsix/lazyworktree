<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\select;
use App\GitService;
use App\Config;
use App\Helpers;

class Change
{
  public const MENU_NAME = 'Switch Worktree';

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
      label: 'Select a worktree to switch to',
      options: $options
    );

    $worktree = $worktrees->where('path', $path);

    if (!$worktree) {
      throw new \Exception("No worktrees found.");
    }

    Link::run(worktree: $worktree);
  }
}
