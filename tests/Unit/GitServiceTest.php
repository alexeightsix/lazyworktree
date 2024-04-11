<?php

use App\GitOperationException;
use App\GitService;
use App\Helpers;

test('repo exists', function () {
  $true = GitService::repoExists('git@github.com:laravel/laravel.git');
  expect($true)->toBeTrue();
  $false = GitService::repoExists('badUrl');
  expect($false)->toBeFalse();
});

test('bare clone works', function () {
  [$source] = createTmpDir(git: true);
  [$dest] = createTmpDir();
  expect(GitService::bareClone($source, $dest))->toBeNull();
});

test('bare clone fails', function () {
  [$dest] = createTmpDir();
  expect(fn () => GitService::bareClone("asdasd", $dest))->toThrow(GitOperationException::class);
});

test('add, get and delete worktrees', function () {
  [$source] = createTmpDir(git: true);
  [$dest] = createTmpDir();

  GitService::bareClone($source, $dest . "/git");

  expect(GitService::addWorktree($dest . "/git", "master", false))->toBeNull();
  expect(GitService::addWorktree($dest . "/git", "dev", true))->toBeNull();

  $worktrees = GitService::getWorktrees($dest . "/git")->get();

  expect($worktrees[0]->get()["branch"] === "refs/heads/dev")->toBeTrue();
  expect($worktrees[1]->get()["branch"] === "refs/heads/master")->toBeTrue();
  expect(GitService::deleteWorktree($dest . "/git", $dest . "/worktrees/master"))->toBeNull();
  expect(GitService::deleteWorktree($dest . "/git", $dest . "/worktrees/dev"))->toBeNull();
});

test('getBranches', function () {
  [$source] = createTmpDir(git: true);
  [$dest] = createTmpDir();
  GitService::bareClone($source, $dest . "/git");
  $branches = GitService::getBranches($dest . "/git");
  expect($branches[0])->toBe('master');
});

test('pruneWorktrees', function () {
  [$source] = createTmpDir(git: true);
  [$dest] = createTmpDir();
  GitService::bareClone($source, $dest . "/git");
  expect(GitService::addWorktree($dest . "/git", "master", false))->toBeNull();
  Helpers::rmDirRecursive($dest . "/worktrees/master");
  expect(GitService::gitWorktreePrune($dest . "/git"))->toBeNull();
});
