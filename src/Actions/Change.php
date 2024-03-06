<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\select;
use App\GitService;
use App\Config;
use App\Worktree;

class Change
{
  public const MENU_NAME = 'Switch Worktree';

  public static function run(): void
  {
    $git_root = Config::get('git_folder');

    $worktrees = GitService::getWorktrees($git_root);

    if ($worktrees->isEmpty()) {
      throw new \Exception("No worktrees found.");
    }

    foreach ($worktrees->get() as $worktree) {
      $options[$worktree->path] = $worktree->baseName;
    }

    $path = (string) select('Select a worktree to switch to', $options);

    $worktree = $worktrees->where('path', $path);

    Link::run($worktree);
  }
}
