<?php

declare(strict_types=1);

namespace App;

use App\Worktrees;

class GitService
{
  public static function repoExists(string $url): bool
  {
    $command = "git ls-remote {$url} > /dev/null 2>&1";
    [$ok] = Helpers::shell_exec($command);
    return $ok;
  }

  public static function deleteWorktree(string $git_path, string $worktree_path): void
  {
    try {
      Helpers::rmDirRecursive($worktree_path);
      GitService::gitWorktreePrune($git_path);
    } catch (\Exception) {
      $error = true;
    }

    if (isset($error) || is_dir($worktree_path)) {
      throw new GitOperationException("Failed to delete worktree.");
    } elseif (is_link($worktree_path) && unlink($worktree_path)) {
      throw new \Exception("Failed to delete worktree symlink.");
    }
  }

  public static function addWorktree(string $git_root, string $branch, bool $newBranch = false): void
  {
    if ($newBranch) {
      $args = "-b {$branch}";
    } else {
      $args = $branch;
    }

    if (!is_dir("{$git_root}/../worktrees") && !mkdir("{$git_root}/../worktrees")) {
      throw new GitOperationException("Failed to create worktrees directory.");
    }

    $slug = Helpers::slugify($branch);
    $worktree_dir = "../worktrees/$slug";
    $cmd = "git -C $git_root worktree add {$worktree_dir} $args";

    [$ok] = Helpers::shell_exec($cmd);

    if (!$ok) {
      throw new GitOperationException("Failed to add worktree.");
    }
  }

  public static function getBranches(string $path): array
  {
    [$ok, $output] = Helpers::shell_exec("git -C {$path} branch -a");

    if (!$ok || !isset($output)) {
      throw new GitOperationException("Failed to list branches.");
    }

    $output = array_filter($output);

    if (empty($output)) {
      throw new GitOperationException("Failed to list branches.");
    }

    $branches = [];

    foreach ($output as $branch) {
      $branch = str_replace("*", "", $branch);
      $branch = str_replace("+", "", $branch);
      $branches[] = $branch;
    }

    sort($branches);

    $branches = array_map("trim", $branches); // @phpstan-ignore-line

    return (array) $branches;
  }

  public static function gitWorktreePrune(string $path): void
  {
    [$ok] = Helpers::shell_exec("git -C {$path} worktree prune");

    if (!$ok) {
      throw new GitOperationException("Failed to prune worktrees.");
    }
  }

  public static function getWorktrees(string $git_path): Worktrees
  {
    [$ok, $worktrees] = Helpers::shell_exec("git -C {$git_path} worktree list --porcelain");

    if (!$ok || !$worktrees) {
      throw new GitOperationException("Failed to list worktrees.");
    }

    $current = 0;
    $trees = [];

    foreach ($worktrees as $worktree) {
      if (empty($worktree)) {
        $current = $current + 1;
        continue;
      }
      $trees[$current][] = $worktree;
    }

    $list = new Worktrees();

    foreach ($trees as $tree) {
      $list->addWorktree(new Worktree($tree));
    }

    return $list;
  }

  public static function bareClone(string $url, string $folder): void
  {
    [$ok] = Helpers::shell_exec("git clone {$url} {$folder} --bare > /dev/null 2>&1");

    if (!$ok) {
      throw new GitOperationException("Failed to clone repository.");
    }
  }
}

class GitOperationException extends \Exception
{
}
