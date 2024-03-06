<?php

namespace App\Actions;

use function Laravel\Prompts\text;
use function Laravel\Prompts\spin;
use function Laravel\Prompts\select;
use App\GitService;
use App\Config;

class Change
{
  public const MENU_NAME = 'Switch Worktree';

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

    $path = (string) select('Select a worktree to switch to', $options);
    
    Link::run($path);

  }
}
