<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\select;
use function Laravel\Prompts\info;

use App\Actions\UnLink;
use App\Config;
use App\GitService;

class Delete
{
  const MENU_NAME = 'Delete Worktree';

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

    $path = (string) select('Select a worktree to delete', $options);

    // FIX ME: handle better (symlink, last one you're deleting etc)
    GitService::deleteWorktree($git_root, $path);

    $worktrees = GitService::getWorktrees($git_root);

    if (empty($worktrees)) {
      UnLinkCurrent::run();
    }

    info("Worktree $options[$path] [{$path}] deleted");
  }
}
