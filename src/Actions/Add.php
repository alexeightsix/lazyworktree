<?php

namespace App\Actions;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\search;
use App\GitService;
use App\Helpers;
use App\Config;

class Add
{
  const MENU_NAME = 'Add Worktree';

  public static function fromExisting(string $git_root) : void
  {
    $branches = GitService::getBranches($git_root);

    $branch = (string) search(
      'Select a branch to add as a worktree',
      fn (string $input) => array_filter($branches, fn ($branch) => str_contains($branch, $input))
    );

    if (GitService::isWorktree($git_root, (string) $branch)) {
      $switch = confirm('Branch already exists, do you want to switch to it?', true);

      if ($switch) {
        Link::run($branch);
      }
    }

    GitService::addWorktree($git_root, $branch);
  }

  public static function fromNew(string $git_root) : void
  {
    $branch = text(
      label: 'Enter the name of the new branch',
      validate: fn (string $value) => match (true) {
        empty($value) => 'The branch name cannot be empty.',
        default => null
      }
    );

    $branch = (string) Helpers::slugify($branch);

    $worktree_dir = GitService::addWorktree($git_root, $branch, true);

    $switch = confirm('Do you want to switch to this branch?', true);

    if ($switch) {
      Link::run($worktree_dir);
    }
  }

  public static function run(): void
  {
    $is_new_worktree = confirm(
      label: 'Add New Worktree',
      yes: 'Create new branch',
      no: 'Create from existing branch'
    );

    $git_root = Config::get('git_folder');

    if (!$is_new_worktree) {
      self::fromExisting($git_root);
    } else {
      self::fromNew($git_root);
    }
  }
}
