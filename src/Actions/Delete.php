<?php

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
    $list = GitService::getWorktrees($git_root);

    foreach ($list as $value) {
      $options[$value["path"]] = $value["branch"];
    }

    if (empty($options)) {
      throw new \Exception("No worktrees found.");
    }

    $path = (string) select('Select a worktree to delete', $options);

    GitService::deleteWorktree($git_root, $path);

    $list = GitService::getWorktrees($git_root);

    if (empty($list)) {
      UnLink::run();
    }

    info("Worktree $options[$path] [{$path}] deleted");
  }
}
