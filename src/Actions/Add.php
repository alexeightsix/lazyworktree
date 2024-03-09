<?php

declare(strict_types=1);

namespace App\Actions;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\text;
use function Laravel\Prompts\search;
use App\GitService;
use App\Helpers;
use App\Worktree;

class Add
{
  const MENU_NAME = 'Add Worktree';

  public static function fromExisting(string $git_root): void
  {
    $branches = GitService::getBranches(path: $git_root);

    $branch = (string) search(
      'Select a branch to add as a worktree',
      fn (string $input) => array_filter($branches, fn ($branch) => str_contains($branch, $input))
    );

    $worktrees = GitService::getWorktrees(git_path: $git_root);
    $worktree = $worktrees->where('baseName', $branch);

    if ($worktree) {
      $switch = confirm(
        label: 'Branch already exists, do you want to switch to it?',
        default: true
      );

      if ($switch) {
        Link::run($worktree);
      }
    }

    GitService::addWorktree(git_root: $git_root, branch: $branch);
  }

  public static function fromNew(string $git_root): void
  {
    $worktrees = GitService::getWorktrees(git_path: $git_root);

    $branch = text(
      label: 'Enter the name of the new branch',
      validate: fn (string $value) => match (true) {
        $worktrees->where("baseName", $value) instanceof Worktree => 'The branch already exists.',
        empty($value) => 'The branch name cannot be empty.',
        default => null
      }
    );

    $branch = (string) Helpers::slugify(string: $branch);

    GitService::addWorktree(git_root: $git_root, branch: $branch, newBranch: true);

    $worktree = GitService::getWorktrees(git_path: $git_root);

    $worktree = $worktree->where('baseName', $branch);

    if (!$worktree) {
      throw new \Exception("Failed to add worktree.");
    }

    $switch = confirm(label: 'Do you want to switch to this Worktree?', default: true);

    if ($switch) {
      Link::run(worktree: $worktree);
    }
  }

  public static function run(): void
  {
    $is_new_worktree = confirm(
      label: 'Add New Worktree',
      yes: 'Create new branch',
      no: 'Create from existing branch'
    );

    $git_root = Helpers::findGitFolder();

    if (!$is_new_worktree) {
      self::fromExisting(git_root: $git_root);
    } else {
      self::fromNew(git_root: $git_root);
    }
  }
}
