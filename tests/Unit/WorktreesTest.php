<?php

use App\Worktree;
use App\Worktrees;

test('new worktree', function () {
  $array = [
    "worktree /home/test/dev/foo/bar/worktrees/baz",
    "HEAD 3127cbfa815e13ae6d193ab4263dea6e1354eb59",
    "branch refs/heads/foo_bar_baz",
  ];

  $expected = [
    "baseName" => "baz",
    "branch" => "refs/heads/foo_bar_baz",
    "head" => "3127cbfa815e13ae6d193ab4263dea6e1354eb59",
    "path" => "/home/test/dev/foo/bar/worktrees/baz"
  ];

  $worktree = new Worktree($array);

  expect($worktree->isBare())->toBeFalse();
  expect($worktree->get())->toBe($expected);

  $worktrees = new Worktrees();

  expect($worktrees->isEmpty())->toBeTrue();

  $worktrees->addWorktree($worktree);

  expect($worktrees->isEmpty())->toBeFalse();
  expect($worktrees->exists("baz"))->toBeTrue();
  expect($worktrees->where("baseName", "baz") === $worktree)->toBeTrue();
  expect($worktrees->get()[0] === $worktree)->toBeTrue();
  expect(json_decode($worktrees->getJson(), true))->toBe([
    $expected
  ]);
});
