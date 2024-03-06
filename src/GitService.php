<?php

declare(strict_types=1);

namespace App;

class GitService
{
  // FIX ME: make use of this when adding
  public static function exists(string $url): bool
  {
    $command = "git ls-remote {$url} 2> /dev/null";
    return shell_exec($command) !== null;
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
    }
  }

  private static function parseBranchName(string $name): string|bool
  {
    $re = '/^\[(.*)\]$/m';
    $matches = [];
    preg_match($re, $name, $matches);

    if (empty($matches) || count($matches) != 2) {
      return false;
    }

    return (string) $matches[1];
  }

  public static function addWorktree(string $git_root, string $branch, bool $newBanch = false): string
  {
    if ($newBanch) {
      $args = "-b {$branch}";
    } else {
      $args = $branch;
    }

    if (!is_dir("{$git_root}/../worktrees")) {
      mkdir("{$git_root}/../worktrees");
    }

    $slug = Helpers::slugify($branch);
    $worktree_dir = "../worktrees/$slug";
    $cmd = "git -C $git_root worktree add {$worktree_dir} $args";

    $output = shell_exec($cmd);

    if ($output === null) {
      throw new GitOperationException("Failed to add worktree.");
    }

    return getcwd() . "/worktrees/{$slug}";
  }

  public static function isWorktree(string $path, string $branch): bool
  {
    $worktrees = self::getWorktrees($path);

    foreach ($worktrees as $worktree) {
      if ($worktree["branch"] === $branch) {
        return true;
      }
    }

    return false;
  }

  public static function getBranches(string $path) : array
  {
    $output = shell_exec("git -C {$path} branch -a");

    if ($output === null) {
      throw new GitOperationException("Failed to list branches.");
    }

    $output = explode("\n", (string) $output);
    $output = array_filter($output);

    if (empty($output)) {
      throw new GitOperationException("Failed to list branches.");
    }

    $output = array_map(function ($branch): string {
      $branch = str_replace("*", "", $branch);
      $branch = trim($branch);
      return $branch;
    }, $output);


    return (array) $output;
  }

  public static function gitWorktreePrune(string $path): void
  {
    shell_exec("git -C {$path} worktree prune");
  }

  public static function getWorktrees(string $path): array
  {
    $ls = shell_exec("git -C {$path} worktree list");

    if ($ls === null) {
      throw new GitOperationException("Failed to list worktrees.");
    }

    $ls = explode("\n", (string) $ls);

    $list = [];

    foreach ($ls as $worktree) {
      $worktree = array_values(array_filter(explode(" ", $worktree)));

      if (empty($worktree[0])) {
        break;
      }

      if (is_dir($worktree[0]) === false) {
        throw new GitOperationException("Error Parsing Worktree List.");
      }

      $out = [
        "path" => $worktree[0],
        "branch" => null,
      ];

      foreach ($worktree as $key => $value) {
        if ($out["branch"] = self::parseBranchName($value)) {
          $list[] = $out;
          break;
        }
      }
    }
    return $list;
  }

  public static function bareClone(string $url, string $folder): void
  {
    $command = "git clone {$url} {$folder} --bare > /dev/null 2>&1";

    $out = shell_exec($command);

    if ($out === null) {
      throw new GitOperationException("Failed to clone repository.");
    }
  }
}

class GitOperationException extends \Exception
{
}
